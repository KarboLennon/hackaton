<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'password'   => Hash::make('password'), // default pass
            'full_name'  => $this->faker->name(),
            'role'       => 'member',
            'status'     => 'active',
            'remember_token' => Str::random(10),
        ];
    }
}
