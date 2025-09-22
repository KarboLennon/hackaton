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
        'full_name'   => ['required','string','max:255'],
        'address'     => ['required','string','max:500'],
        'province'    => ['required','string','max:100'],
        'city'        => ['required','string','max:100'],
        'postal_code' => ['required','string','max:20'],
        'email'       => ['required','email','max:255','unique:users,email'],
        'password'    => ['required','string','min:6'],
        'phone_number'=> ['required','string','max:20'],
    ]);

    $user = User::create([
        'name'        => $data['full_name'], // opsional, bisa sama dengan full_name
        'full_name'   => $data['full_name'],
        'address'     => $data['address'],
        'province'    => $data['province'],
        'city'        => $data['city'],
        'postal_code' => $data['postal_code'],
        'email'       => $data['email'],
        'password'    => Hash::make($data['password']),
        'phone_number'=> $data['phone_number'],
        'role'        => 'member',
        'status'      => 'active',
    ]);

    $token = $user->createToken('api')->plainTextToken;

    return response()->json(['user'=>$user, 'token'=>$token], 201);
}


    public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error'=>'Invalid credentials'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['user'=>$user, 'token'=>$token]);
    }

    public function logout(Request $r)
    {
        $r->user()->tokens()->delete();
        return response()->json(['message'=>'Logged out']);
    }
}
