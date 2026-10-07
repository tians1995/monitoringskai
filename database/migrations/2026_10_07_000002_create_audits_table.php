<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(){Schema::create('audits',function(Blueprint $t){$t->id();$t->string('name');$t->unsignedSmallInteger('year');$t->timestamps();$t->unique(['name','year']);});} public function down(){Schema::dropIfExists('audits');} };
