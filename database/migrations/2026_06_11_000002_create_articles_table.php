<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('post_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('image')->nullable();
            $table->longText('content')->nullable();
            $table->integer('counter')->default(0);
            $table->string('status');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->json('meta_data')->nullable();
            $table->string('source')->default('web');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('post_categories');
    }
};
