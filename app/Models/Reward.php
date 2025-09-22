<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'm_rewards';
    protected $fillable = ['name','description','points_cost','stock','is_active'];

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}
