<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Reward extends Model
{
    protected $fillable = [
        'title',
        'description',
        'points_cost',
        'stock',
        'image_path',
        'category_id',
    ];

    protected $appends = ['image_url', 'category_name'];

     public function category()
    {
        return $this->belongsTo(RewardCategory::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }

}
