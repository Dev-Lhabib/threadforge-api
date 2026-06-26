<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
    * Inscription d'un nouveau créateur.
    *
    * @group Authentification
    * @unauthenticated
    *
    * @bodyParam name string required Le nom du créateur. Example: Yassine
    * @bodyParam email string required Email unique. Example: yassine@threadforge.dev
    * @bodyParam password string required Min 8 caractères. Example: password123
    * @bodyParam password_confirmation string required Doit correspondre à password. Example: password123
    *
    * @response 201 {
    *   "user": {"id": 1, "name": "Yassine", "email": "yassine@threadforge.dev", "created_at": "2026-06-22T10:00:00+00:00"},
    *   "token": "1|XxXxXxXxXxXxXxXxXxXxXx"
    * }
    * @response 422 {
    *   "message": "The email field is required.",
    *   "errors": {"email": ["The email field is required."]}
    * }
    */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('threadforge-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Connexion et émission d'un Bearer Token.
     *
     * @group Authentification
     * @unauthenticated
     *
     * @bodyParam email string required Example: yassine@threadforge.dev
     * @bodyParam password string required Example: password123
     *
     * @response 200 {
     *   "user": {"id": 1, "name": "Yassine", "email": "yassine@threadforge.dev", "created_at": "2026-06-22T10:00:00+00:00"},
     *   "token": "2|YyYyYyYyYyYyYyYyYyYyYy"
     * }
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)){
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $token = $user->createToken('threadforge-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion (révoque uniquement le token courant).
     *
     * @group Authentification
     * @authenticated
     *
     * @response 200 {"message": "Déconnecté avec succès."}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnecté avec succès.',
        ]);
    }
}
