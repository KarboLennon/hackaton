<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'platform'    => $this->faker->randomElement(['instagram','tiktok']),
            'content_url' => $this->faker->url(),
            'caption'     => $this->faker->sentence(),
            'status'      => 'submitted',
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
