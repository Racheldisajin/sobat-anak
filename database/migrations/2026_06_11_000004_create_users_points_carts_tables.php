<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void {
    Schema::create('users', function(Blueprint $table){
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('role')->default('user');
        $table->timestamps();
    });
    Schema::create('user_points', function(Blueprint $table){
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->integer('points')->default(1250);
        $table->timestamps();
    });
    Schema::create('cart_items', function(Blueprint $table){
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
        $table->integer('quantity')->default(1);
        $table->timestamps();
        $table->unique(['user_id','product_id']);
    });
    Schema::create('reward_claims', function(Blueprint $table){
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('reward_name');
        $table->integer('points_used');
        $table->timestamps();
    });
} public function down(): void { Schema::dropIfExists('reward_claims'); Schema::dropIfExists('cart_items'); Schema::dropIfExists('user_points'); Schema::dropIfExists('users'); } };
