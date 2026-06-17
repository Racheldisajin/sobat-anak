<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSearchLog extends Model
{
    protected $guarded = [];
    protected $casts = [
        'results_meta' => 'array',
    ];
}
