<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelEvent extends Model
{
    protected $table = 'detail_funnel_events';
    protected $fillable = ['user_id','campaign_id','stage','occurred_at','meta'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
