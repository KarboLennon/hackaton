<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    protected $table = 'detail_redemptions';
    protected $fillable = ['user_id','reward_id','points_spent','status','shipping_info','approved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}
