<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'detail_user_profiles';
    protected $fillable = [
        'user_id',
        'phone',
        'ig_handle',
        'tiktok_handle',
        'city',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
