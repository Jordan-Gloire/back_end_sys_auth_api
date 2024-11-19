<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function inscription(Request $request)
    {
        $validatedData = $request->validate([
            "name" => "required|max:60",
            "email" => "required|email|unique:users",
            "password" => "required|min:8",
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);
        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Utilisateur inscrit avec succès",
            "user" => $user->only(['id', 'name', 'email']),
            "token" => $token
        ], 201);
    }

    public function connexion(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Identifiants incorrects !"
            ], 401);
        }

        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Connexion réussie !",
            "token" => $token
        ]);
    }

    public function deconnexion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "success" => true,
            "message" => "Déconnexion réussie !"
        ]);
    }
}
