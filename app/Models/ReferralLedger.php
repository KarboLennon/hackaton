<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralLedger extends Model
{
    protected $fillable = [
        'type',
        'referrer_id',
        'referred_id',
        'points',
        'meta',
    ];

    protected $casts = [
        'points' => 'integer',
        'meta'   => 'array',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
