<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('query');
            $table->string('intent')->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->json('results_meta')->nullable();
            $table->string('source')->default('landing_ai_search');
            $table->timestamps();
        });

        Schema::create('ai_trend_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_trend_keywords');
        Schema::dropIfExists('ai_search_logs');
    }
};
