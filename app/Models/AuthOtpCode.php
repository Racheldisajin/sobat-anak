<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthOtpCode extends Model
{
    protected $table = 'auth_otp_codes';
    protected $guarded = [];
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
