<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    protected $table = 'detail_points_ledger';
    protected $fillable = ['user_id', 'source_type', 'source_id', 'points', 'description'];
    public $timestamps = true;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
