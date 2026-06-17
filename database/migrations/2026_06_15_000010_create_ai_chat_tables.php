<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ai_chat_sessions')) {
            Schema::create('ai_chat_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title')->nullable();
                $table->string('source')->default('landing_ai_chat');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
                $table->enum('role', ['user', 'assistant'])->default('user');
                $table->longText('message');
                $table->json('recommendations')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
