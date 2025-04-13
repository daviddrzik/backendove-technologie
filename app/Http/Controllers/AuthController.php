<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|confirmed|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);


            return response()->json([
                'message' => 'Účet bol vytvorený.',
                'token' => $user->createToken('api-token')->plainTextToken
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri registrácii',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'errors' => [
                        'email' => ['Nesprávny e-mail alebo heslo.']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            }

            $user = Auth::user();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Prihlásenie bolo úspešné.',
                'user' => $user,
                'token' => $token
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri prihlasovaní',
                'error' => $e->errors()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Odlásenie bolo úspešné']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri odhlásení',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

