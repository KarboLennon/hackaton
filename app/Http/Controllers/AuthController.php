<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\ReferralService;

class AuthController extends Controller
{
    public function register(Request $r, ReferralService $ref)
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
            'ref' => ['nullable', 'string', 'max:32'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ]);

        $incomingRef = $data['referral_code'] ?? $data['ref'] ?? null;

        return DB::transaction(function () use ($data, $incomingRef, $ref) {
            $referrerId = null;
            if (!empty($incomingRef)) {
                $referrer = User::where('referral_code', $incomingRef)->first();
                if ($referrer)
                    $referrerId = $referrer->id;
            }

            $user = User::create([
                'name' => $data['full_name'],
                'full_name' => $data['full_name'],
                'address' => $data['address'] ?? null,
                'province' => $data['province'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone_number' => $data['phone_number'] ?? null,
                'role' => 'member',
                'status' => 'pending',
                'referred_by' => $referrerId,
            ]);

            $ref->ensureReferralCode($user);

            if ($referrerId) {
                $ref->creditForSignup($user);
            }

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'user' => $user->only([
                    'id',
                    'name',
                    'full_name',
                    'email',
                    'status',
                    'role',
                    'address',
                    'province',
                    'city',
                    'postal_code',
                    'phone_number',
                    'referral_code',
                    'referred_by',
                    'points'
                ]),
                'token' => $token,
            ], 201);
        });
    }

    public function login(Request $r)
    {
        $data = $r->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // OPSIONAL: matikan dulu kalau bikin logout device lain
        // $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user->only([
                'id',
                'name',
                'full_name',
                'email',
                'status',
                'role',
                'address',
                'province',
                'city',
                'postal_code',
                'phone_number',
                'referral_code',
                'referred_by',
                // 'total_points' => $user->total_points,
            ]),
            'token' => $token,
        ]);
    }

    public function me(Request $r)
    {
        $user = $r->user();
        if (!$user)
            return response()->json(['error' => 'Unauthenticated'], 401);

        return response()->json([
            'user' => $user->only([
                'id',
                'name',
                'full_name',
                'email',
                'status',
                'role',
                'address',
                'province',
                'city',
                'postal_code',
                'phone_number',
                'referral_code',
                'referred_by'
            ]) + ['total_points' => $user->total_points],
        ]);
    }


    public function logout(Request $r)
    {
        $r->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
