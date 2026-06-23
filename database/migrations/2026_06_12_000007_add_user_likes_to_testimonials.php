<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('testimonials')) {
            Schema::table('testimonials', function (Blueprint $table) {
                if (!Schema::hasColumn('testimonials', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('testimonials', 'rating')) {
                    $table->unsignedTinyInteger('rating')->default(5)->after('message');
                }
                if (!Schema::hasColumn('testimonials', 'likes_count')) {
                    $table->unsignedInteger('likes_count')->default(0)->after('rating');
                }
            });
        }

        if (!Schema::hasTable('testimonial_likes')) {
            Schema::create('testimonial_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['testimonial_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_likes');
        if (Schema::hasTable('testimonials')) {
            Schema::table('testimonials', function (Blueprint $table) {
                if (Schema::hasColumn('testimonials', 'user_id')) $table->dropConstrainedForeignId('user_id');
                if (Schema::hasColumn('testimonials', 'rating')) $table->dropColumn('rating');
                if (Schema::hasColumn('testimonials', 'likes_count')) $table->dropColumn('likes_count');
            });
        }
    }
};
