<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate(
            ["email" => "required|email|max:255",
             "password" => "required|string|max:255" 
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || Hash::check($request->passowrd, $user->password)){
            return response()->json(["Message" => "Credencias inválidas"], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }


    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Realizado'
        ]);
    }
}
