<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    /**
     * Simpan atau update FCM token perangkat.
     * POST /api/fcm/token
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'     => 'required|string|max:512',
            'device_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        FcmToken::updateOrCreate(
            [
                'user_id'   => Auth::id(),
                'device_id' => $request->device_id,
            ],
            [
                'token' => $request->token,
            ]
        );

        return response()->json([
            'message' => 'FCM token berhasil disimpan.'
        ]);
    }

    /**
     * Hapus FCM token perangkat (saat logout).
     * DELETE /api/fcm/token
     */
    public function destroy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        FcmToken::where('user_id', Auth::id())
            ->where('device_id', $request->device_id)
            ->delete();

        return response()->json([
            'message' => 'FCM token berhasil dihapus.'
        ]);
    }
}
