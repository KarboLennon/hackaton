<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index(Request $r)
    {
        $q = Campaign::query()
            ->select('id', 'name', 'description', 'start_date', 'end_date', 'status', 'image_path')
            ->withCount('challenges')
            ->orderByDesc('start_date')->orderByDesc('id');

        if ($r->boolean('with_challenges')) {
            $q->with([
                'challenges' => function ($cq) {
                    $cq->select('id', 'campaign_id', 'name', 'type', 'start_at', 'end_at', 'base_points', 'status');
                }
            ]);
        }


        if ($r->filled('status')) {
            $q->where('status', $r->string('status'));
        }
        if ($kw = $r->get('keyword')) {
            $q->where(fn($qq) => $qq->where('name', 'like', "%{$kw}%")
                ->orWhere('description', 'like', "%{$kw}%"));
        }

        $perPage = (string) $r->get('per_page', '20');
        $data = ($perPage === '0' || strcasecmp($perPage, 'all') === 0) ? $q->get() : $q->paginate((int) $perPage);

        // Lampirkan url gambar
        $data->through(fn($c) => $this->appendImageUrl($c));
        return $data;
    }

    public function show($id)
    {
        $c = Campaign::with(['challenges:id,campaign_id,name,description,type,start_at,end_at,base_points,rules,status,image_path'])
            ->findOrFail($id);
        $this->appendImageUrl($c);
        $c->challenges->transform(fn($ch) => $this->appendImageUrl($ch));
        return $c;
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'image' => ['nullable', 'image', 'max:2048'], // <= 2MB
        ]);

        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('campaigns', 'public');
        }

        $c = Campaign::create($data);
        return response()->json($this->appendImageUrl($c), 201);
    }

    public function update($id, Request $r)
    {
        $c = Campaign::findOrFail($id);
        $data = $r->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'closed'])],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        if (($data['remove_image'] ?? false) && $c->image_path) {
            Storage::disk('public')->delete($c->image_path);
            $data['image_path'] = null;
        }

        if ($r->hasFile('image')) {
            if ($c->image_path)
                Storage::disk('public')->delete($c->image_path);
            $data['image_path'] = $r->file('image')->store('campaigns', 'public');
        }

        $c->update($data);
        return response()->json($this->appendImageUrl($c->refresh()));
    }

    public function destroy($id)
    {
        $c = Campaign::findOrFail($id);
        if ($c->image_path)
            Storage::disk('public')->delete($c->image_path);
        $c->delete();
        return response()->json(['ok' => true]);
    }

    public function setStatus($id, Request $r)
    {
        $data = $r->validate(['status' => ['required', Rule::in(['draft', 'active', 'closed'])]]);
        $c = Campaign::findOrFail($id);
        $c->update(['status' => $data['status']]);
        return response()->json(['ok' => true, 'status' => $c->status]);
    }

    private function appendImageUrl($model)
    {
        $model->image_url = $model->image_path ? Storage::url($model->image_path) : null;
        return $model;
    }
}
