<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatMessage extends Model
{
    protected $guarded = [];
    protected $casts = [
        'recommendations' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }
}
