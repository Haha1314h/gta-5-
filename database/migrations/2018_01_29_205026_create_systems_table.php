<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateSystemsTable extends Migration { public function up() { Schema::create('systems', function (Blueprint $sp185401) { $sp185401->increments('id'); $sp185401->string('name', 100)->unique(); $sp185401->longText('value')->nullable(); $sp185401->timestamps(); }); } public function down() { Schema::dropIfExists('systems'); } }