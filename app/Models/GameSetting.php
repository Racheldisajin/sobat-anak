<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?: [];
        return $settings[$key] ?? $default;
    }
}
