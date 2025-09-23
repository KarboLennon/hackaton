<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;

class ChallengeController extends Controller
{
    /**
     * PUBLIC: List challenges (bisa filter).
     * Query params (opsional):
     * - campaign_id=...  (int)
     * - status=active|draft|closed
     * - type=weekly|monthly|one_off
     * - per_page=... (default 20; jika =0 atau 'all' -> tanpa paginate)
     */
    public function index(Request $r)
    {
        $q = Challenge::query()
            ->select('id','campaign_id','name','description','type','start_at','end_at','base_points','rules','status')
            ->with('campaign:id,name,status');

        if ($r->filled('campaign_id')) {
            $q->where('campaign_id', (int) $r->campaign_id);
        }
        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }
        if ($r->filled('type')) {
            $q->where('type', $r->type);
        }

        $q->orderByDesc('start_at');

        // paginate default, kecuali minta all
        $perPage = (string) $r->get('per_page', '20');
        if ($perPage === '0' || strtolower($perPage) === 'all') {
            return $q->get();
        }
        return $q->paginate((int) $perPage);
    }

    /**
     * PUBLIC: Detail challenge
     */
    public function show($id)
    {
        return Challenge::with('campaign:id,name,status')
            ->findOrFail($id);
    }

    /**
     * ADMIN: Create challenge
     * Body JSON:
     * - campaign_id (required|exists:m_campaigns,id)
     * - name (required|string|max:255)
     * - description (nullable|string)
     * - type (required|in:weekly,monthly,one_off)
     * - start_at (required|date)
     * - end_at (required|date|after:start_at)
     * - base_points (required|integer|min:0)
     * - rules (nullable|string)
     * - status (required|in:active,draft,closed)
     */
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
        ]);

        $challenge = Challenge::create($data);

        return response()->json($challenge, 201);
    }

    /**
     * ADMIN: Update challenge
     * Body JSON sama seperti store (semua optional, tapi tetap divalidasi).
     */
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
        ]);

        $challenge->update($data);

        return response()->json($challenge);
    }

    /**
     * ADMIN: Delete challenge
     * (Hati-hati jika sudah ada submissions)
     */
    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * ADMIN: Quick toggle status (optional helper)
     * Body: { "status": "active|draft|closed" }
     */
    public function setStatus($id, Request $r)
    {
        $r->validate([
            'status' => ['required','in:active,draft,closed'],
        ]);

        $c = Challenge::findOrFail($id);
        $c->update(['status' => $r->status]);

        return response()->json($c);
    }
}
