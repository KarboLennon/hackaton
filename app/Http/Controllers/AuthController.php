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
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'full_name'  => $data['name'],
            'role'       => 'member',
            'status'     => 'active',
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
