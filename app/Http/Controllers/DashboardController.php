<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $recipients = User::whereIn('role', ['ADMIN', 'AUDITOR'])->get();
        $findings = AuditFinding::with(['audit', 'unit', 'pic'])->get();

        foreach ($findings as $finding) {
            $daysToTarget = $finding->target_date ? $today->diffInDays($finding->target_date, false) : null;
            $notificationType = $finding->computed_status === 'overdue'
                ? 'OVERDUE'
                : (($finding->computed_status === 'open' && $daysToTarget !== null && $daysToTarget >= 0 && $daysToTarget <= 7) ? 'DUE_SOON' : null);

            if ($notificationType) {
                foreach ($recipients as $user) {
                    $exists = Notification::where('user_id', $user->id)
                        ->where('finding_id', $finding->id)
                        ->where('type', $notificationType)
                        ->whereDate('created_at', $today)
                        ->exists();

                    if (!$exists) {
                        Notification::create([
                            'user_id' => $user->id,
                            'finding_id' => $finding->id,
                            'type' => $notificationType,
                            'message' => $finding->no_lha.' - '.$notificationType,
                            'is_read' => false,
                        ]);
                    }
                }
            }
        }

        $counts = [
            'total' => $findings->count(),
            'closed' => $findings->filter(fn ($finding) => $finding->computed_status === 'closed')->count(),
            'due_month' => $findings->filter(fn ($finding) => $finding->computed_status === 'open' && $finding->target_date && $finding->target_date->month === $today->month && $finding->target_date->year === $today->year)->count(),
            'overdue' => $findings->filter(fn ($finding) => $finding->computed_status === 'overdue')->count(),
            'open' => $findings->filter(fn ($finding) => $finding->computed_status === 'open')->count(),
        ];

        $due = $findings
            ->filter(fn ($finding) => $finding->computed_status === 'overdue' || ($finding->computed_status === 'open' && $finding->target_date && $finding->target_date->month === $today->month && $finding->target_date->year === $today->year))
            ->sortBy('target_date')->take(10)->values();

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            $months[] = [
                'label' => $month->format('M Y'),
                'due' => $findings->filter(fn ($finding) => $finding->target_date && $finding->target_date->month === $month->month && $finding->target_date->year === $month->year)->count(),
                'overdue' => $findings->filter(fn ($finding) => $finding->target_date && $finding->target_date->lt($month->copy()->startOfMonth()) && $finding->computed_status === 'overdue')->count(),
            ];
        }

        $monthFindings = $findings->filter(fn ($finding) =>
            $finding->target_date
            && $finding->target_date->month === $today->month
            && $finding->target_date->year === $today->year
        )->values();

        $monthlyProjects = $monthFindings
            ->groupBy(fn ($finding) => $finding->audit_id)
            ->map(function ($projectFindings) {
                $audit = $projectFindings->first()->audit;

                return [
                    'id' => $audit?->id,
                    'project' => $audit?->name ?? 'Audit tanpa nama',
                    'year' => $audit?->year,
                    'total' => $projectFindings->count(),
                    'open' => $projectFindings->filter(fn ($finding) => $finding->computed_status === 'open')->count(),
                    'overdue' => $projectFindings->filter(fn ($finding) => $finding->computed_status === 'overdue')->count(),
                    'closed' => $projectFindings->filter(fn ($finding) => $finding->computed_status === 'closed')->count(),
                ];
            })
            ->filter(fn ($project) => $project['open'] > 0 || $project['overdue'] > 0)
            ->sortByDesc('overdue')
            ->values();

        $dueSoon = $findings->filter(fn ($finding) =>
            $finding->computed_status === 'open'
            && $finding->target_date
            && $finding->target_date->greaterThanOrEqualTo($today)
            && $finding->target_date->lessThanOrEqualTo($today->copy()->addDays(7))
        )->sortBy('target_date')->values();

        $monthlyCounts = [
            'total' => $monthFindings->count(),
            'open' => $monthFindings->filter(fn ($finding) => $finding->computed_status === 'open')->count(),
            'overdue' => $monthFindings->filter(fn ($finding) => $finding->computed_status === 'overdue')->count(),
            'closed' => $monthFindings->filter(fn ($finding) => $finding->computed_status === 'closed')->count(),
            'due_soon' => $dueSoon->count(),
        ];

        $insights = [];
        if ($monthlyCounts['overdue'] > 0) {
            $topOverdue = $monthlyProjects->firstWhere('overdue', $monthlyProjects->max('overdue'));
            $insights[] = [
                'type' => 'warning',
                'title' => $monthlyCounts['overdue'].' temuan bulan ini sudah overdue',
                'detail' => $topOverdue ? 'Prioritaskan tindak lanjut pada '.$topOverdue['project'].' ('.$topOverdue['overdue'].' temuan overdue).' : 'Tinjau target dan PIC untuk temuan yang terlambat.',
            ];
        }
        if ($monthlyCounts['due_soon'] > 0) {
            $insights[] = [
                'type' => 'info',
                'title' => $monthlyCounts['due_soon'].' temuan open akan jatuh tempo dalam 7 hari',
                'detail' => 'Hubungi PIC dan pastikan action plan serta bukti tindak lanjut sudah siap.',
            ];
        }
        if ($monthlyCounts['total'] > 0) {
            $closureRate = (int) round(($monthlyCounts['closed'] / $monthlyCounts['total']) * 100);
            $insights[] = [
                'type' => 'success',
                'title' => $closureRate.'% temuan bertarget bulan ini sudah closed',
                'detail' => $monthlyCounts['open'] > 0 ? 'Pantau '.$monthlyCounts['open'].' temuan yang masih open sampai target bulan ini.' : 'Semua temuan bertarget bulan ini sudah closed.',
            ];
        }
        if (!$insights) {
            $insights[] = ['type' => 'success', 'title' => 'Belum ada temuan bertarget bulan ini', 'detail' => 'Tambahkan target temuan atau pilih periode laporan untuk melihat analitik.'];
        }

        return Inertia::render('Dashboard', compact('counts', 'due', 'months', 'monthlyCounts', 'monthlyProjects', 'insights'));
    }
}
