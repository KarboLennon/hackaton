<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'm_campaigns';
    protected $fillable = ['name','description','start_date','end_date','status'];

    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }

    public function funnelEvents()
    {
        return $this->hasMany(FunnelEvent::class);
    }
}