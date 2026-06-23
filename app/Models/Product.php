<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getDisplayRatingAttribute(): float
    {
        // Rating ditampilkan 0 sampai ada ulasan/rating dari user.
        if (isset($this->reviews_avg_rating) && (int) $this->reviews_count > 0) {
            return round((float) $this->reviews_avg_rating, 1);
        }

        return 0.0;
    }

    public function getDisplayReviewCountAttribute(): int
    {
        return (int) ($this->reviews_count ?? 0);
    }
}
