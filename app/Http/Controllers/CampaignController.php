<?php

namespace App\Http\Controllers;

use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        return Campaign::with(['challenges' => function($q){
            $q->select('id','campaign_id','name','type','start_at','end_at','base_points','status');
        }])->get();
    }

    public function show($id)
    {
        return Campaign::with('challenges')->findOrFail($id);
    }
}
