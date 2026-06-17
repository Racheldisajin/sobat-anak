<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->index();
            $table->string('purpose', 40)->index();
            $table->string('code_hash');
            $table->longText('payload')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'purpose', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_otp_codes');
    }
};
