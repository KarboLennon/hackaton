<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submission extends Model
{   
    use HasFactory;
    protected $table = 'detail_submissions';
    protected $fillable = [
        'user_id','challenge_id','platform',
        'content_url','caption','status','metrics','approved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function metricsSnapshots()
    {
        return $this->hasMany(ContentMetric::class);
    }
}
