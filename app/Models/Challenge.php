<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $table = 'm_challenges';
    protected $fillable = [
        'campaign_id','name','description','type',
        'start_at','end_at','base_points','rules','status'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
