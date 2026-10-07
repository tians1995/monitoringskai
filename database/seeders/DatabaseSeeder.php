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
        User::create(['name' => 'Administrator', 'email' => 'admin@audit.local', 'password' => Hash::make('password'), 'role' => 'ADMIN']);
        User::create(['name' => 'Auditor Demo', 'email' => 'auditor@audit.local', 'password' => Hash::make('password'), 'role' => 'AUDITOR']);
        $unit = Unit::create(['name' => 'IT Division']);
        $audit = Audit::create(['name' => 'IT General Control Audit', 'year' => 2026]);

        $openFinding = AuditFinding::create([
            'audit_id' => $audit->id, 'unit_id' => $unit->id, 'no_lha' => '1',
            'judul_temuan' => 'Pengelolaan akses belum memadai', 'inisial' => 'TE', 'rating' => 'HIGH',
            'audit_plan' => 'Audit TI 2026', 'temuan' => "Akun pengguna yang tidak aktif masih ditemukan.\nPeninjauan hak akses belum dilakukan berkala.",
            'rekomendasi' => 'Unit kerja agar melakukan perbaikan dan menyediakan evidence.',
            'action_plan' => 'Melakukan remediation sesuai rekomendasi.', 'pic_name' => 'Auditor Demo',
            'target_date' => now()->addDays(10), 'status' => 'open', 'progress' => 30,
        ]);
        $openFinding->items()->createMany([
            ['description' => 'Akun pengguna yang tidak aktif masih ditemukan.', 'sort_order' => 0],
            ['description' => 'Peninjauan hak akses belum dilakukan berkala.', 'sort_order' => 1],
        ]);

        $overdueFinding = AuditFinding::create([
            'audit_id' => $audit->id, 'unit_id' => $unit->id, 'no_lha' => '2',
            'judul_temuan' => 'Konfigurasi keamanan perlu diperkuat', 'inisial' => 'AB', 'rating' => 'MEDIUM',
            'audit_plan' => 'Audit TI 2026', 'temuan' => 'Contoh temuan yang sudah melewati target.',
            'rekomendasi' => 'Melakukan perbaikan konfigurasi.', 'action_plan' => 'Remediation konfigurasi.',
            'pic_name' => 'Auditor Demo', 'target_date' => now()->subDays(5), 'status' => 'overdue', 'progress' => 70,
        ]);
        $overdueFinding->items()->create(['description' => 'Contoh temuan yang sudah melewati target.', 'sort_order' => 0]);
    }
}
