<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditFinding;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['year', 'months', 'status', 'rating']);
        $rows = AuditFinding::with(['audit', 'unit', 'pic'])
            ->forReportFilters($filters)
            ->orderBy('target_date')
            ->get();

        $summary = [
            'total' => $rows->count(),
            'closed' => $rows->filter(fn ($finding) => $finding->computed_status === 'closed')->count(),
            'overdue' => $rows->filter(fn ($finding) => $finding->computed_status === 'overdue')->count(),
            'open' => $rows->filter(fn ($finding) => $finding->computed_status === 'open')->count(),
        ];

        $years = collect(range(2020, 2035))
            ->merge(Audit::query()->select('year')->distinct()->pluck('year'))
            ->unique()
            ->sortDesc()
            ->values();

        return Inertia::render('Reports/Index', compact('rows', 'summary', 'filters', 'years'));
    }
}
