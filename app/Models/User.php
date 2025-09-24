<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'address',
        'province',
        'city',
        'postal_code',
        'phone_number',
        'role',
        'status',
        'referral_code',
        'referred_by',
        'points', // <-- kolom fisik di tabel users
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'points' => 'integer', // <-- aman karena kolom ada
    ];

    // --- Relasi referral ---
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }
    public function getTotalPointsAttribute(): int
    {
        return (int) \DB::table('detail_points_ledger')
            ->where('user_id', $this->id)
            ->sum('points');
    }


    // Ledger referral (kalau tabel/model-nya memang ada)
    public function referralLedgers()
    {
        return $this->hasMany(ReferralLedger::class, 'referrer_id');
    }

    // --- Relasi lain (pastikan model & tabelnya memang ada bila dipakai) ---
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

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function invitesSent()
    {
        return $this->hasMany(Invite::class, 'inviter_id');
    }

}
