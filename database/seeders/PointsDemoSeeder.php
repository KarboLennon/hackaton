<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Submission;
use App\Models\Challenge;

class PointsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $challenge = Challenge::first();
        if (!$challenge) return;

        // Ambil semua submission yg belum di-approve (dari DummySeeder)
        $subs = Submission::where('status','submitted')
            ->where('challenge_id', $challenge->id)
            ->get();

        $now = now();
        foreach ($subs as $i => $s) {
            // Approve
            $approvedAt = $now->copy()->subDays(rand(0, min(10, $now->day - 1)))->setTime(rand(9, 22), rand(0,59));
            $s->update([
                'status'      => 'approved',
                'approved_at' => $approvedAt,
                'updated_at'  => $approvedAt,
            ]);

            // Masukkan poin sesuai base_points challenge (default 10)
            $points = $challenge->base_points ?? 10;

            DB::table('detail_points_ledger')->insert([
                'user_id'     => $s->user_id,
                'source_type' => 'challenge',
                'source_id'   => $s->id,
                'points'      => $points,
                'description' => 'Approved: '.$challenge->name,
                'created_at'  => $approvedAt, // penting: tetap di bulan ini
                'updated_at'  => $approvedAt,
            ]);
        }
    }
}
