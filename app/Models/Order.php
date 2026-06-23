<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled_payments' => 'array',
        'payment_detail' => 'array',
        'midtrans_response' => 'array',
        'callback_payload' => 'array',
        'shipping_snapshot' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Sudah dibayar',
            'pending' => 'Menunggu pembayaran',
            'challenge' => 'Menunggu review Midtrans',
            'expired' => 'Pembayaran kedaluwarsa',
            'cancelled' => 'Pembayaran dibatalkan',
            'failed' => 'Pembayaran gagal',
            'refund' => 'Dana dikembalikan',
            default => 'Status belum diketahui',
        };
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'paid',
            'pending', 'challenge' => 'pending',
            'expired', 'cancelled', 'failed' => 'failed',
            'refund' => 'refund',
            default => 'pending',
        };
    }
}
