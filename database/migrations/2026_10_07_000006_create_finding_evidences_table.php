<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(){Schema::create('finding_evidences',function(Blueprint $t){$t->id();$t->foreignId('finding_id')->constrained('audit_findings')->cascadeOnDelete();$t->string('file_name');$t->string('file_path');$t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});} public function down(){Schema::dropIfExists('finding_evidences');} };
