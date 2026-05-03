<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     *
     * Login with NIK + password, returns JWT token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'NIK' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('NIK', $request->NIK)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'NIK atau password salah.',
            ], 401);
        }

        $token = auth()->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * POST /auth/register
     *
     * Register a new user (only PENGURUS or KETUA can register).
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_rt' => 'required|uuid|exists:rt,id_rt',
            'nama' => 'required|string|max:255',
            'NIK' => 'required|string|unique:users,NIK',
            'role' => 'required|in:WARGA,PENGURUS,BENDAHARA',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if NIK already exists (explicit 409)
        if (User::where('NIK', $request->NIK)->exists()) {
            return response()->json([
                'message' => 'Conflict — NIK sudah terdaftar dalam sistem.',
            ], 409);
        }

        $user = User::create([
            'id_rt' => $request->id_rt,
            'nama' => $request->nama,
            'NIK' => $request->NIK,
            'role' => $request->role,
            'phone' => $request->phone,
            'password_hash' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Akun berhasil dibuat.',
            'user' => $user,
        ], 201);
    }

    /**
     * POST /auth/logout
     *
     * Invalidate the current JWT token.
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * POST /auth/refresh
     *
     * Refresh the current JWT token.
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'token' => auth()->refresh(),
            'user' => auth()->user(),
        ]);
    }
}
