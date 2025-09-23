<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    // Izinkan semua kolom yang dipakai controller
    protected $fillable = [
        'user_id',
        'reward_id',
        'status',
        'points_spent',
        'voucher_code',
        'expires_at',
    ];

    // Biar serialize tanggalnya rapi
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
