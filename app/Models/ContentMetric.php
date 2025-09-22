<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentMetric extends Model
{
    protected $table = 'detail_content_metrics';
    protected $fillable = ['submission_id','likes','comments','shares','views','collected_at'];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
