<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Submission;
use App\Models\Challenge;
use App\Models\User;

class SubmissionController extends Controller
{
    /**
     * User submit link UGC ke challenge
     */
    public function store($challengeId, Request $r)
    {
        $r->validate([
            'platform'     => 'required|in:instagram,tiktok,other',
            'content_url'  => 'required|url|max:500',
            'caption'      => 'nullable|string',
        ]);

        // Pastikan challenge aktif
        $challenge = Challenge::findOrFail($challengeId);
        if ($challenge->status !== 'active') {
            return response()->json(['error' => 'Challenge is not active'], 400);
        }

        $submission = Submission::create([
            'user_id'      => $r->user()->id,
            'challenge_id' => $challengeId,
            'platform'     => $r->platform,
            'content_url'  => $r->content_url,
            'caption'      => $r->caption,
            'status'       => 'submitted',
        ]);

        return response()->json($submission, 201);
    }

    public function indexByChallenge($challengeId)
    {
        $rows = Submission::with([
                'user:id,name,full_name,status',
                'challenge:id,name',
            ])
            ->where('challenge_id', $challengeId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($rows);
    }

    public function approve($id)
{
    return DB::transaction(function () use ($id) {
        $s = Submission::with('user')->lockForUpdate()->findOrFail($id);

        if ($s->status !== 'submitted') {
            return response()->json(['error' => 'Already processed'], 400);
        }

        $s->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        $basePoints = Challenge::where('id', $s->challenge_id)->value('base_points') ?? 10;

        $alreadyLogged = DB::table('detail_points_ledger')
            ->where('user_id', $s->user_id)
            ->where('source_type', 'challenge')
            ->where('source_id', $s->id)
            ->exists();

        if (!$alreadyLogged) {
            DB::table('detail_points_ledger')->insert([
                'user_id'     => $s->user_id,
                'source_type' => 'challenge',
                'source_id'   => $s->id,
                'points'      => $basePoints,
                'description' => 'Approved challenge #'.$s->challenge_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 🔧 Aktivasi lebih fleksibel
        if (optional($s->user)->status !== 'active') {
            $approvedCount = Submission::where('user_id', $s->user_id)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount >= 1) {
                $s->user->update([
                    'status'     => 'active',
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    });
}

      public function index(Request $r)
    {
        $perPage = (int) $r->input('per_page', 10);

        $q = Submission::query()
            ->with([
                'user:id,name,full_name',
                'challenge:id,campaign_id,name',
                'challenge.campaign:id,name', // butuh nama campaign
            ])
            ->when($r->filled('status') && $r->status !== 'all', fn($qq) => $qq->where('status', $r->status))
            ->when($r->filled('campaign_id') && $r->campaign_id !== 'all', fn($qq) => $qq->whereHas('challenge', fn($c) => $c->where('campaign_id', $r->campaign_id)))
            ->when($r->filled('challenge_id') && $r->challenge_id !== 'all', fn($qq) => $qq->where('challenge_id', $r->challenge_id))
            ->when($r->filled('q'), function ($qq) use ($r) {
                $s = $r->q;
                $qq->where(function ($w) use ($s) {
                    $w->where('content_url', 'like', "%{$s}%")
                      ->orWhere('caption', 'like', "%{$s}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%"));
                });
            })
            ->orderByDesc('created_at');

        // paginate
        $page = $q->paginate($perPage);

        // opsional: format ringan agar FE dapat field yang enak dipakai
        $page->getCollection()->transform(function ($s) {
            return [
                'id'             => $s->id,
                'user_id'        => $s->user_id,
                'user_name'      => $s->user->full_name ?? $s->user->name ?? null,
                'campaign_id'    => $s->challenge->campaign_id ?? null,
                'campaign_name'  => $s->challenge->campaign->name ?? null,
                'challenge_id'   => $s->challenge_id,
                'challenge_name' => $s->challenge->name ?? null,
                'platform'       => $s->platform,
                'content_url'    => $s->content_url,
                'caption'        => $s->caption,
                'status'         => $s->status,
                'metrics'        => $s->metrics,
                'approved_at'    => $s->approved_at,
                'created_at'     => $s->created_at,
            ];
        });

        return response()->json($page);
    }

    public function reject($id)
    {
        $s = Submission::findOrFail($id);

        if ($s->status !== 'submitted') {
            return response()->json(['error' => 'Already processed'], 400);
        }

        $s->update([
            'status' => 'rejected',
        ]);

        return response()->json(['ok' => true]);
    }

    public function mine(Request $r)
{
    $userId = $r->user()->id;

    // Ambil challenge_id yg pernah user submit (boleh latest status sekalian)
    $rows = \App\Models\Submission::query()
        ->select('challenge_id', \DB::raw('MAX(status) as latest_status'), \DB::raw('MAX(created_at) as last_submitted_at'))
        ->where('user_id', $userId)
        ->groupBy('challenge_id')
        ->get();

    // Response ringan: array of ids + optional map
    return response()->json([
        'challenge_ids' => $rows->pluck('challenge_id')->values(),
        'items' => $rows, // kalau mau dipakai
    ]);
}
    
}
