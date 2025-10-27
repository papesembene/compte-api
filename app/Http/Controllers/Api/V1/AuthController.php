<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    use \App\Traits\ApiResponseTrait;

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        // Vérifier d'abord si c'est un utilisateur admin
        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            // C'est un admin
            $tokenResult = $user->createToken('access_token', ['*'], Carbon::now()->addHour());
            $accessToken = $tokenResult->accessToken;
            $refreshToken = $user->createToken('refresh_token', ['*'], Carbon::now()->addDays(30))->accessToken;

            return $this->successResponse([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => $user,
                'role' => 'admin',
            ], 'Connexion admin réussie');
        }

        // Sinon, vérifier si c'est un client
        $client = Client::where('email', $validated['email'])->first();

        if (!$client || !Hash::check($validated['password'], $client->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Générer access token (1 heure)
        $tokenResult = $client->createToken('access_token', ['*'], Carbon::now()->addHour());
        $accessToken = $tokenResult->accessToken;

        // Générer refresh token (30 jours)
        $refreshToken = $client->createToken('refresh_token', ['*'], Carbon::now()->addDays(30))->accessToken;

        return $this->successResponse([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'client' => $client,
            'role' => 'client',
        ], 'Connexion client réussie');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        // Pour simplifier, on utilise le client authentifié actuel
        $client = Auth::guard('api')->user();

        if (!$client) {
            return $this->errorResponse('Token invalide', 401);
        }

        // Révoquer l'ancien token d'accès
        $client->tokens()->where('name', 'access_token')->delete();

        // Générer nouveau access token
        $tokenResult = $client->createToken('access_token', ['*'], Carbon::now()->addHour());
        $accessToken = $tokenResult->accessToken;

        return $this->successResponse([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Token rafraîchi');
    }

    public function logout(Request $request)
    {
        $client = Auth::guard('api')->user();

        if ($client) {
            $client->tokens()->delete();
        }

        return $this->successResponse(null, 'Déconnexion réussie');
    }
}
