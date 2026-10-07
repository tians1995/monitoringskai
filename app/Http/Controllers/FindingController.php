<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\FindingEvidence;
use App\Models\FindingUpdate;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FindingController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditFinding::with(['audit', 'unit', 'pic']);

        if ($request->filled('month')) $query->whereMonth('target_date', $request->integer('month'));
        if ($request->filled('year')) $query->whereYear('target_date', $request->integer('year'));

        if ($request->filled('status')) {
            $status = strtolower($request->input('status'));
            if ($status === 'overdue') {
                $query->whereDate('target_date', '<', now()->toDateString())->whereRaw('LOWER(status) != ?', ['closed']);
            } elseif ($status === 'closed') {
                $query->whereRaw('LOWER(status) = ?', ['closed']);
            } elseif ($status === 'open') {
                $query->whereRaw('LOWER(status) != ?', ['closed'])->where(fn ($q) => $q->whereNull('target_date')->orWhereDate('target_date', '>=', now()->toDateString()));
            }
        }

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(fn ($q) => $q->where('no_lha', 'like', "%$term%")
                ->orWhere('judul_temuan', 'like', "%$term%")
                ->orWhere('temuan', 'like', "%$term%"));
        }

        return Inertia::render('Findings/Index', [
            'findings' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['month', 'year', 'status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Findings/Form', [
            'finding' => null,
            'audits' => Audit::orderByDesc('year')->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'audit_id' => 'required|exists:audits,id',
            'unit_id' => 'nullable|exists:units,id',
            'no_lha' => 'required|regex:/^[0-9]+$/|max:20',
            'judul_temuan' => 'required|string|max:255',
            'inisial' => 'nullable|max:20',
            'rating' => 'required|in:HIGH,MEDIUM,LOW',
            'audit_plan' => 'nullable|string|max:255',
            'temuan_items' => 'required|array|min:1',
            'temuan_items.*' => 'required|string',
            'rekomendasi' => 'nullable',
            'action_plan' => 'nullable',
            'pic_name' => 'nullable|string|max:255',
            'target_date' => 'required|date',
            'status' => 'nullable|in:open,closed,overdue',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $items = $data['temuan_items'];
        unset($data['temuan_items']);
        $data['temuan'] = implode("\n", $items);
        $data['status'] = $this->resolveStatus($data['status'] ?? 'open', $data['target_date']);
        $data['closed_date'] = $data['status'] === 'closed' ? now()->toDateString() : null;
        $data['pic_id'] = null;
        $finding = DB::transaction(function () use ($data, $items) {
            $finding = AuditFinding::create($data);
            $finding->items()->createMany(array_map(fn ($description, $index) => ['description' => $description, 'sort_order' => $index], $items, array_keys($items)));

            FindingUpdate::create([
                'finding_id' => $finding->id,
                'user_id' => auth()->id(),
                'status' => $finding->computed_status,
                'progress' => $finding->progress,
                'notes' => 'Temuan dibuat',
                'new_target_date' => $finding->target_date,
            ]);

            return $finding;
        });

        return redirect()->route('findings.show', $finding)->with('success', 'Temuan berhasil dibuat.');
    }

    public function show(AuditFinding $finding)
    {
        $finding->load(['audit', 'unit', 'pic', 'items', 'updates.user', 'evidences.uploader']);

        return Inertia::render('Findings/Show', ['finding' => $finding]);
    }

    public function edit(AuditFinding $finding)
    {
        $finding->load('pic', 'items');

        return Inertia::render('Findings/Form', [
            'finding' => $finding,
            'audits' => Audit::orderByDesc('year')->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, AuditFinding $finding)
    {
        $data = $request->validate([
            'audit_id' => 'required|exists:audits,id',
            'unit_id' => 'nullable|exists:units,id',
            'no_lha' => 'required|regex:/^[0-9]+$/|max:20',
            'judul_temuan' => 'required|string|max:255',
            'inisial' => 'nullable|max:20',
            'rating' => 'required|in:HIGH,MEDIUM,LOW',
            'audit_plan' => 'nullable|string|max:255',
            'temuan_items' => 'required|array|min:1',
            'temuan_items.*' => 'required|string',
            'rekomendasi' => 'nullable',
            'action_plan' => 'nullable',
            'pic_name' => 'nullable|string|max:255',
            'target_date' => 'required|date',
            'status' => 'required|in:open,closed,overdue',
            'version' => 'required|integer|min:1',
            'closed_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $items = $data['temuan_items'];
        unset($data['temuan_items']);
        $data['temuan'] = implode("\n", $items);

        $finding = DB::transaction(function () use ($data, $finding, $items) {
            $locked = AuditFinding::whereKey($finding->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $data['version'] === (int) $locked->version, 409, 'Temuan sudah diperbarui oleh pengguna lain. Muat ulang halaman sebelum menyimpan.');

            $oldTargetDate = $locked->target_date;
            $data['status'] = $this->resolveStatus($data['status'], $data['target_date']);
            $data['closed_date'] = $data['status'] === 'closed' ? ($data['closed_date'] ?? now()->toDateString()) : null;
            $data['pic_id'] = null;
            unset($data['version']);
            $locked->fill($data);
            $locked->version++;
            $locked->save();
            $locked->items()->delete();
            $locked->items()->createMany(array_map(fn ($description, $index) => ['description' => $description, 'sort_order' => $index], $items, array_keys($items)));

            FindingUpdate::create([
                'finding_id' => $locked->id,
                'user_id' => auth()->id(),
                'status' => $locked->computed_status,
                'progress' => $locked->progress,
                'notes' => 'Data temuan diperbarui',
                'old_target_date' => $oldTargetDate,
                'new_target_date' => $locked->target_date,
            ]);

            return $locked;
        });

        return redirect()->route('findings.show', $finding)->with('success', 'Temuan diperbarui.');
    }

    public function updateProgress(Request $request, AuditFinding $finding)
    {
        $data = $request->validate([
            'status' => 'required|in:open,closed,overdue',
            'progress' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'new_target_date' => 'nullable|date',
            'version' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data, $finding) {
            $locked = AuditFinding::whereKey($finding->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $data['version'] === (int) $locked->version, 409, 'Temuan sudah diperbarui oleh pengguna lain. Muat ulang halaman sebelum menyimpan.');

            $oldTargetDate = $locked->target_date;
            $targetDate = $data['new_target_date'] ?? $locked->target_date;
            $status = $this->resolveStatus($data['status'], $targetDate);
            $locked->update([
                'status' => $status,
                'progress' => $data['progress'],
                'target_date' => $targetDate,
                'closed_date' => $status === 'closed' ? now()->toDateString() : null,
                'version' => $locked->version + 1,
            ]);

            FindingUpdate::create([
                'finding_id' => $locked->id,
                'user_id' => auth()->id(),
                'status' => $locked->computed_status,
                'progress' => $locked->progress,
                'notes' => $data['notes'] ?? null,
                'old_target_date' => $oldTargetDate,
                'new_target_date' => $locked->target_date,
            ]);
        });

        return back()->with('success', 'Monitoring diperbarui.');
    }

    public function evidence(Request $request, AuditFinding $finding)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $file = $request->file('file');
        $path = $file->store('evidence', 'public');

        FindingEvidence::create([
            'finding_id' => $finding->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Evidence diunggah.');
    }

    public function export(Request $request)
    {
        $rows = AuditFinding::with(['audit', 'unit', 'pic'])
            ->forReportFilters($request->only(['year', 'months', 'status', 'rating']))
            ->get();
        $filename = 'audit-findings-'.now()->format('Ymd_His').'.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="'.$filename.'"'];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No LHA', 'Audit', 'Tahun', 'Inisial', 'Rating', 'Judul Temuan', 'Temuan', 'Rekomendasi', 'Action Plan Unit Kerja', 'PIC', 'Target', 'Status']);
            foreach ($rows as $finding) {
                fputcsv($out, [
                    $finding->no_lha,
                    $finding->audit?->name,
                    $finding->audit?->year,
                    $finding->inisial,
                    $finding->rating,
                    $finding->judul_temuan,
                    $finding->temuan,
                    $finding->rekomendasi,
                    $finding->action_plan,
                    $finding->pic_name ?: $finding->pic?->name,
                    $finding->target_date?->format('Y-m-d'),
                    $finding->computed_status,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    private function resolveStatus(string $status, $targetDate): string
    {
        if ($status === 'closed') {
            return 'closed';
        }

        return $targetDate && Carbon::parse($targetDate)->lt(Carbon::today()) ? 'overdue' : 'open';
    }
}
