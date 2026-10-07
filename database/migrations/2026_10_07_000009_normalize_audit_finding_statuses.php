<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('audit_findings')->whereRaw('LOWER(status) IN (?, ?, ?)', ['open', 'extended', 'due_this_month'])->update(['status' => 'open']);
        DB::table('audit_findings')->whereRaw('LOWER(status) = ?', ['closed'])->update(['status' => 'closed']);
        DB::table('audit_findings')->whereRaw('LOWER(status) = ?', ['overdue'])->update(['status' => 'overdue']);
        DB::table('audit_findings')->whereRaw('LOWER(status) != ?', ['closed'])->whereDate('target_date', '<', now()->toDateString())->update(['status' => 'overdue']);

        DB::statement("ALTER TABLE audit_findings MODIFY status ENUM('open', 'closed', 'overdue') NOT NULL DEFAULT 'open'");

        DB::table('finding_updates')->whereRaw('LOWER(status) IN (?, ?, ?)', ['open', 'extended', 'due_this_month'])->update(['status' => 'open']);
        DB::table('finding_updates')->whereRaw('LOWER(status) = ?', ['closed'])->update(['status' => 'closed']);
        DB::table('finding_updates')->whereRaw('LOWER(status) = ?', ['overdue'])->update(['status' => 'overdue']);
    }

    public function down(): void
    {
        DB::table('audit_findings')->whereIn('status', ['open', 'closed', 'overdue'])->update(['status' => DB::raw('UPPER(status)')]);
        DB::statement("ALTER TABLE audit_findings MODIFY status VARCHAR(255) NOT NULL DEFAULT 'OPEN'");
        DB::table('finding_updates')->whereIn('status', ['open', 'closed', 'overdue'])->update(['status' => DB::raw('UPPER(status)')]);
    }
};
