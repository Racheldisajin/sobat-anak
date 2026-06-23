<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatSession extends Model
{
    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(AiChatMessage::class, 'session_id');
    }
}
