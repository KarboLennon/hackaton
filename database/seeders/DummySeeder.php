<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Submission;
use App\Models\Challenge;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        // bikin 10 user
        $users = User::factory()->count(10)->create();

        // ambil challenge pertama (pastikan ada di DB)
        $challenge = Challenge::first();

        // bikin submission 1 per user
        foreach ($users as $user) {
            Submission::factory()->create([
                'user_id'      => $user->id,
                'challenge_id' => $challenge->id,
            ]);
        }
    }
}
