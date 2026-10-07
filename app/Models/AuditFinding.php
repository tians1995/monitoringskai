<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuditFinding extends Model
{
    protected $fillable = [
        'audit_id', 'unit_id', 'no_lha', 'judul_temuan', 'inisial', 'rating', 'audit_plan', 'temuan',
        'rekomendasi', 'action_plan', 'pic_name', 'pic_id', 'target_date',
        'status', 'version', 'closed_date', 'progress',
    ];

    protected $casts = ['target_date' => 'date', 'closed_date' => 'date'];
    protected $appends = ['computed_status', 'days_overdue', 'days_remaining'];

    public function audit() { return $this->belongsTo(Audit::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function pic() { return $this->belongsTo(User::class, 'pic_id'); }
    public function updates() { return $this->hasMany(FindingUpdate::class, 'finding_id')->latest('id'); }
    public function evidences() { return $this->hasMany(FindingEvidence::class); }

    public function scopeForReportFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['year'])) {
            $query->whereHas('audit', fn (Builder $audit) => $audit->where('year', $filters['year']));
        }

        if (!empty($filters['months'])) {
            $months = array_map('intval', (array) $filters['months']);
            $query->whereIn(DB::raw('MONTH(target_date)'), $months);
        }

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            if ($status === 'closed') {
                $query->where(fn (Builder $q) => $q->whereRaw('LOWER(status) = ?', ['closed'])->orWhereNotNull('closed_date'));
            } elseif ($status === 'overdue') {
                $query->whereRaw('LOWER(status) != ?', ['closed'])->whereNull('closed_date')->whereDate('target_date', '<', now()->toDateString());
            } elseif ($status === 'open') {
                $query->whereRaw('LOWER(status) != ?', ['closed'])->whereNull('closed_date')
                    ->where(fn (Builder $q) => $q->whereNull('target_date')->orWhereDate('target_date', '>=', now()->toDateString()));
            }
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query;
    }

    public function getComputedStatusAttribute()
    {
        $status = strtolower($this->getRawOriginal('status') ?? 'open');

        if ($status === 'closed' || $this->closed_date) {
            return 'closed';
        }

        if ($this->target_date && $this->target_date->lt(Carbon::today())) {
            return 'overdue';
        }

        return 'open';
    }

    public function getDaysOverdueAttribute()
    {
        return $this->computed_status === 'overdue' ? Carbon::today()->diffInDays($this->target_date) : 0;
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->target_date || in_array($this->computed_status, ['overdue', 'closed'])) {
            return 0;
        }

        return Carbon::today()->diffInDays($this->target_date, false) * -1;
    }
}
