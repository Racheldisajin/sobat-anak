<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('products', function(Blueprint $table){ $table->id(); $table->string('name'); $table->string('category'); $table->unsignedInteger('price'); $table->string('badge')->nullable(); $table->decimal('rating',2,1)->default(0); $table->unsignedInteger('sold')->default(0); $table->text('image'); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('products'); } };
