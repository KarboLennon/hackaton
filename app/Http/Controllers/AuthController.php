<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $data['full_name'] ?? $data['name'] ?? '',
            'full_name' => $data['full_name'] ?? $data['name'] ?? '',
            'address' => $data['address'] ?? null,
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'] ?? null,
            'role' => 'member',
            'status' => 'pending',  
        ]);


        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }


     public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // optional: bersihkan token lama
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }
     public function me(Request $r)
    {
        return response()->json($r->user());
    }

    public function logout(Request $r)
    {
        $r->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
