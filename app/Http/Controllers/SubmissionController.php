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

    public function approve($id, Request $r)
    {
        return DB::transaction(function () use ($id) {
            $s = Submission::lockForUpdate()->findOrFail($id);

            if ($s->status !== 'submitted') {
                return response()->json(['error' => 'Already processed'], 400);
            }

            // Update status submission
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

            $approvedCount = Submission::where('user_id', $s->user_id)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount === 1) {
                User::where('id', $s->user_id)->update([
                    'status'     => 'active',
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['ok' => true]);
        });
    }
}
