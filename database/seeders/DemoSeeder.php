<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder {
    public function run(): void {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin', // kolom default laravel
            'email' => 'admin@hpz.test',
            'password' => Hash::make('password'),
            'full_name' => 'Admin HPZ',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $campaignId = DB::table('m_campaigns')->insertGetId([
            'name' => 'HPZ Crew Kickoff',
            'description' => 'Recruitment & weekly challenge',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('m_challenges')->insert([
            'campaign_id' => $campaignId,
            'name' => 'Motor Pride of the Week',
            'description' => 'Post gaya riding + hashtag resmi',
            'type' => 'weekly',
            'start_at' => now(),
            'end_at' => now()->addWeek(),
            'base_points' => 10,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
