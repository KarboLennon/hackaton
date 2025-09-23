<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $table = 'm_campaigns';
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];
    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'campaign_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
