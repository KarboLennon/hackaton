<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $table = 'detail_invites';
    protected $fillable = ['inviter_id','invite_code','invitee_user_id','campaign_id','status'];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_user_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
