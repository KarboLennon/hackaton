<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'full_name', 'role', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    //  Relasi
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function funnelEvents()
    {
        return $this->hasMany(FunnelEvent::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function points()
    {
        return $this->hasMany(PointLedger::class);
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function invitesSent()
    {
        return $this->hasMany(Invite::class, 'inviter_id');
    }
}
