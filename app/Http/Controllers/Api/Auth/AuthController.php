<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected function getUserType(){}

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|confirmed|min:8'
        ]);

        $user = User::where('email', $data['email'])->first();
        if($user){
            return response()->json([
                'message' => 'Email already exists!',
            ], 409);
        }

        $data['role'] = $this->getUserType();

        $data['password'] = Hash::make($data['password']);

        $newUser = User::create($data);

        return response()->json([
            'message' => 'user created successfully!',
            'success' => true
        ], 201);
    }

    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);
        $user = User::where('email', $data['email'])->first();
        if(!$user || !Hash::check($data['password'], $user->password)){
            return response()->json([
                'message' => 'Invalid credentials!',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful!',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful!'
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'password' => 'sometimes|password|confirmed|min:8'
        ]);

        $user = $request->user();

        if(isset($data['email']) && $data['email'] !== $user->email){
            if(User::where('email', $data['email'])->exists()){
                return response()->json([
                    'message' => 'Email already exists!',
                ], 409);
            }
            $user->email = $data['email'];
        }

        if(isset($data['name'])){
            $user->name = $data['name'];
        }

        if(isset($data['password'])){
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user
        ], 200);
    }
}
