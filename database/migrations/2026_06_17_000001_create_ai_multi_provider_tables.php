<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_provider_logs')) {
            Schema::create('ai_provider_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('provider', 60)->index();
                $table->string('model')->nullable();
                $table->string('status', 80)->index();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->text('prompt_preview')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_answer_cache')) {
            Schema::create('ai_answer_cache', function (Blueprint $table) {
                $table->id();
                $table->string('question_hash', 64)->unique();
                $table->text('question');
                $table->string('intent')->nullable()->index();
                $table->longText('answer');
                $table->string('provider', 60)->nullable();
                $table->json('recommendations_json')->nullable();
                $table->unsignedInteger('hit_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_user_limits')) {
            Schema::create('ai_user_limits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('limit_date')->index();
                $table->unsignedInteger('used_count')->default(0);
                $table->timestamps();
                $table->unique(['user_id', 'limit_date'], 'ai_user_limits_user_date_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_user_limits');
        Schema::dropIfExists('ai_answer_cache');
        Schema::dropIfExists('ai_provider_logs');
    }
};
