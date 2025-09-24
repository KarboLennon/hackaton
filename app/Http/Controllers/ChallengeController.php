<?php

// app/Http/Controllers/ChallengeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{
    public function index(Request $r)
    {
        $q = Challenge::query()
            ->select('id','campaign_id','name','description','type','start_at','end_at','base_points','rules','status','image_path')
            ->with('campaign:id,name,status');

        if ($r->filled('campaign_id')) $q->where('campaign_id',(int)$r->campaign_id);
        if ($r->filled('status'))      $q->where('status',$r->status);
        if ($r->filled('type'))        $q->where('type',$r->type);

        $q->orderByDesc('start_at');

        $perPage = (string)$r->get('per_page','20');
        $data = ($perPage==='0'||strtolower($perPage)==='all') ? $q->get() : $q->paginate((int)$perPage);

        $data->through(fn($ch) => $this->appendImageUrl($ch));
        return $data;
    }

    public function show($id)
    {
        $c = Challenge::with('campaign:id,name,status')->findOrFail($id);
        return $this->appendImageUrl($c);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'campaign_id' => ['required','integer','exists:m_campaigns,id'],
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'type'        => ['required','in:weekly,monthly,one_off'],
            'start_at'    => ['required','date'],
            'end_at'      => ['required','date','after:start_at'],
            'base_points' => ['required','integer','min:0'],
            'rules'       => ['nullable','string'],
            'status'      => ['required','in:active,draft,closed'],
            'image'       => ['nullable','image','max:2048'],
        ]);

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('challenges','public');
        }

        $challenge = Challenge::create($data);
        return response()->json($this->appendImageUrl($challenge), 201);
    }

    public function update($id, Request $r)
    {
        $challenge = Challenge::findOrFail($id);

        $data = $r->validate([
            'campaign_id' => ['sometimes','integer','exists:m_campaigns,id'],
            'name'        => ['sometimes','string','max:255'],
            'description' => ['sometimes','nullable','string'],
            'type'        => ['sometimes','in:weekly,monthly,one_off'],
            'start_at'    => ['sometimes','date'],
            'end_at'      => ['sometimes','date','after:start_at'],
            'base_points' => ['sometimes','integer','min:0'],
            'rules'       => ['sometimes','nullable','string'],
            'status'      => ['sometimes','in:active,draft,closed'],
            'image'       => ['sometimes','nullable','image','max:2048'],
            'remove_image'=> ['sometimes','boolean'],
        ]);

        if (($data['remove_image'] ?? false) && $challenge->image_path) {
            Storage::disk('public')->delete($challenge->image_path);
            $data['image_path'] = null;
        }
        if ($r->hasFile('image')) {
            if ($challenge->image_path) Storage::disk('public')->delete($challenge->image_path);
            $data['image_path'] = $r->file('image')->store('challenges','public');
        }

        $challenge->update($data);
        return response()->json($this->appendImageUrl($challenge->refresh()));
    }

    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);
        if ($challenge->image_path) Storage::disk('public')->delete($challenge->image_path);
        $challenge->delete();
        return response()->json(['ok'=>true]);
    }

    public function setStatus($id, Request $r)
    {
        $r->validate(['status' => ['required','in:active,draft,closed']]);
        $c = Challenge::findOrFail($id);
        $c->update(['status'=>$r->status]);
        return response()->json($this->appendImageUrl($c));
    }

    private function appendImageUrl($model) {
        $model->image_url = $model->image_path ? \Storage::url($model->image_path) : null;
        return $model;
    }
}
