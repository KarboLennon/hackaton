<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function index(Request $r)
    {
        $q = Campaign::query()
            ->select('id', 'name', 'description', 'start_date', 'end_date', 'status')
            ->with([
                'challenges' => function ($cq) {
                    $cq->select('id', 'campaign_id', 'name', 'type', 'start_at', 'end_at', 'base_points', 'status');
                }
            ])
            ->withCount('challenges');

        if ($r->filled('status')) {
            $q->where('status', $r->string('status'));
        }

        if ($kw = $r->get('keyword')) {
            $q->where(function ($qq) use ($kw) {
                $qq->where('name', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%");
            });
        }
        $q->orderByDesc('start_date')->orderByDesc('id');
        $perPage = (string) $r->get('per_page', '20');
        if ($perPage === '0' || strcasecmp($perPage, 'all') === 0) {
            return $q->get();
        }

        return $q->paginate((int) $perPage);
    }


    /**
     * PUBLIC: Campaign detail
     */
    public function show($id)
    {
        return Campaign::with([
            'challenges' => function ($cq) {
                $cq->select('id', 'campaign_id', 'name', 'description', 'type', 'start_at', 'end_at', 'base_points', 'rules', 'status');
            }
        ])->findOrFail($id);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
        ]);

        $c = Campaign::create($data);
        return response()->json($c, 201);
    }

    /**
     * ADMIN: Update campaign
     * Body JSON (semua optional):
     * - name, description, start_date, end_date, status
     */
    public function update($id, Request $r)
    {
        $c = Campaign::findOrFail($id);

        $data = $r->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'closed'])],
        ]);

        $c->update($data);
        return response()->json($c);
    }

    public function destroy($id)
    {
        $c = Campaign::findOrFail($id);
        $c->delete();
        return response()->json(['ok' => true]);
    }

    public function setStatus($id, Request $r)
    {
        $data = $r->validate([
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
        ]);

        $c = Campaign::findOrFail($id);
        $c->update(['status' => $data['status']]);

        return response()->json(['ok' => true, 'status' => $c->status]);
    }
}
