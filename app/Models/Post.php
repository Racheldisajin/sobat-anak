<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
        'meta_data' => 'array',
    ];

    public function category(){ return $this->belongsTo(PostCategory::class, 'category_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags((string) $this->content), 150);
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Artikel';
    }
}
