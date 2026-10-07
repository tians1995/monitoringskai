<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\AuditFinding;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $isLocal = app()->environment('local');
        $seedDemoUsers = $isLocal || filter_var(env('SEED_DEMO_USERS', 'true'), FILTER_VALIDATE_BOOLEAN);
        $auditor = null;

        if ($seedDemoUsers) {
            User::firstOrCreate(
                ['email' => 'admin@audit.local'],
                ['name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'ADMIN']
            );
            $auditor = User::firstOrCreate(
                ['email' => 'auditor@audit.local'],
                ['name' => 'Auditor Demo', 'password' => Hash::make('password'), 'role' => 'AUDITOR']
            );
        }

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        if ($adminEmail && $adminPassword && $adminEmail !== 'admin@audit.local') {
            User::firstOrCreate(
                ['email' => $adminEmail],
                ['name' => 'Administrator', 'password' => Hash::make($adminPassword), 'role' => 'ADMIN']
            );
        }

        if (!$isLocal) {
            return;
        }

        $unit = Unit::firstOrCreate(['name' => 'IT Division']);
        $audit = Audit::firstOrCreate(['name' => 'IT General Control Audit', 'year' => 2026]);

        $openFinding = AuditFinding::firstOrCreate(
            ['audit_id' => $audit->id, 'no_lha' => '1'],
            [
                'unit_id' => $unit->id,
                'judul_temuan' => 'Pengelolaan akses belum memadai',
                'inisial' => 'TE',
                'rating' => 'HIGH',
                'audit_plan' => 'Audit TI 2026',
                'temuan' => "Akun pengguna yang tidak aktif masih ditemukan.\nPeninjauan hak akses belum dilakukan berkala.",
                'rekomendasi' => 'Unit kerja agar melakukan perbaikan dan menyediakan evidence.',
                'action_plan' => 'Melakukan remediation sesuai rekomendasi.',
                'pic_name' => $auditor->name,
                'target_date' => now()->addDays(10),
                'status' => 'open',
                'progress' => 30,
            ]
        );
        if (!$openFinding->items()->exists()) {
            $openFinding->items()->createMany([
                ['description' => 'Akun pengguna yang tidak aktif masih ditemukan.', 'sort_order' => 0],
                ['description' => 'Peninjauan hak akses belum dilakukan berkala.', 'sort_order' => 1],
            ]);
        }

        $overdueFinding = AuditFinding::firstOrCreate(
            ['audit_id' => $audit->id, 'no_lha' => '2'],
            [
                'unit_id' => $unit->id,
                'judul_temuan' => 'Konfigurasi keamanan perlu diperkuat',
                'inisial' => 'AB',
                'rating' => 'MEDIUM',
                'audit_plan' => 'Audit TI 2026',
                'temuan' => 'Contoh temuan yang sudah melewati target.',
                'rekomendasi' => 'Melakukan perbaikan konfigurasi.',
                'action_plan' => 'Remediation konfigurasi.',
                'pic_name' => $auditor->name,
                'target_date' => now()->subDays(5),
                'status' => 'overdue',
                'progress' => 70,
            ]
        );
        if (!$overdueFinding->items()->exists()) {
            $overdueFinding->items()->create(['description' => 'Contoh temuan yang sudah melewati target.', 'sort_order' => 0]);
        }
    }
}
