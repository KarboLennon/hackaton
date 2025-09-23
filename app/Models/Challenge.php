<?php
// app/Models/Challenge.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    protected $table = 'm_challenges';

    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'type',       
        'start_at',
        'end_at',
        'base_points',
        'rules',       
        'status',      
    ];

    protected $casts = [
        'start_at'    => 'datetime',
        'end_at'      => 'datetime',
        'base_points' => 'integer',
        // kalau kolom rules berisi JSON, ini enak:
        // 'rules'       => 'array',
    ];

    /** Relasi: challenge milik sebuah campaign */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'challenge_id', 'id');
    }
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
