<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestimonialLike extends Model
{
    protected $guarded = [];

    public function testimonial()
    {
        return $this->belongsTo(Testimonial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
