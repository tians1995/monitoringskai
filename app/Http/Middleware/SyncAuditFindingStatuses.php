<?php

namespace App\Http\Middleware;

use App\Models\AuditFinding;
use Closure;
use Illuminate\Http\Request;

class SyncAuditFindingStatuses
{
    public function handle(Request $request, Closure $next)
    {
        $today = now()->toDateString();

        AuditFinding::query()
            ->whereRaw('LOWER(status) != ?', ['closed'])
            ->whereNotNull('target_date')
            ->whereDate('target_date', '<', $today)
            ->whereRaw('LOWER(status) != ?', ['overdue'])
            ->update(['status' => 'overdue']);

        AuditFinding::query()
            ->whereRaw('LOWER(status) = ?', ['overdue'])
            ->where(fn ($query) => $query->whereNull('target_date')->orWhereDate('target_date', '>=', $today))
            ->update(['status' => 'open']);

        return $next($request);
    }
}
