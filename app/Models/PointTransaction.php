<?php

class PointTransaction extends Model
{
    protected $fillable = ['user_id','amount','type','related_user_id','related_id','meta'];
    protected $casts = ['meta' => 'array'];
    public function user(){ return $this->belongsTo(User::class); }
}