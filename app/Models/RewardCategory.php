<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardCategory extends Model
{
    protected $fillable = ['name'];

    public function rewards(): HasMany {
        return $this->hasMany(Reward::class, 'category_id');
    }
}
