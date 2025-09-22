<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Submission;
use App\Models\Challenge;

class SubmissionController extends Controller
{
    // User submit link UGC ke challenge
    public function store($challengeId, Request $r)
    {
        $r->validate([
            'platform'    => 'required|in:instagram,tiktok,other',
            'content_url' => 'required|url|max:500',
            'caption'     => 'nullable|string'
        ]);

        // optional: cek challenge aktif
        $challenge = Challenge::findOrFail($challengeId);
        if ($challenge->status !== 'active') {
            return response()->json(['error'=>'Challenge is not active'], 400);
        }

        $submission = Submission::create([
            'user_id'     => $r->user()->id,
            'challenge_id'=> $challengeId,
            'platform'    => $r->platform,
            'content_url' => $r->content_url,
            'caption'     => $r->caption,
            'status'      => 'submitted',
        ]);

        return response()->json($submission, 201);
    }

    // (Opsional) list submission per challenge (buat admin/mod)
    public function indexByChallenge($challengeId)
    {
        $rows = Submission::with(['user:id,name,full_name','challenge:id,name'])
            ->where('challenge_id', $challengeId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $rows;
    }

    // Admin approve + kasih poin sesuai base_points challenge
    public function approve($id, Request $r)
    {
        return DB::transaction(function () use ($id) {
            $s = Submission::lockForUpdate()->findOrFail($id);
            if ($s->status !== 'submitted') {
                return response()->json(['error'=>'Already processed'], 400);
            }

            $s->update(['status'=>'approved','approved_at'=>now()]);

            $challenge = Challenge::find($s->challenge_id);

            DB::table('detail_points_ledger')->insert([
                'user_id'     => $s->user_id,
                'source_type' => 'challenge',
                'source_id'   => $s->id,
                'points'      => $challenge->base_points ?? 10,
                'description' => 'Approved: '.$challenge->name,
                'created_at'  => now(),
                'updated_at'  => now()
            ]);

            return response()->json(['ok'=>true]);
        });
    }
}
