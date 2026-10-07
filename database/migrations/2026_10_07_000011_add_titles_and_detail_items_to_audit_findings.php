<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->string('judul_temuan')->nullable()->after('no_lha');
        });

        Schema::create('audit_finding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('audit_findings')->cascadeOnDelete();
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['finding_id', 'sort_order']);
        });

        DB::table('audit_findings')->select('id', 'temuan', 'created_at', 'updated_at')->orderBy('id')->chunkById(500, function ($findings) {
            $now = now();
            $items = $findings->map(fn ($finding) => [
                'finding_id' => $finding->id,
                'description' => $finding->temuan,
                'sort_order' => 0,
                'created_at' => $finding->created_at ?? $now,
                'updated_at' => $finding->updated_at ?? $now,
            ])->all();

            if ($items) {
                DB::table('audit_finding_items')->insert($items);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_finding_items');
        Schema::table('audit_findings', function (Blueprint $table) {
            $table->dropColumn('judul_temuan');
        });
    }
};
