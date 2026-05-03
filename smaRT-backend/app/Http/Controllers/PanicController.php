<?php

namespace App\Http\Controllers;

use App\Models\PanicLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PanicController extends Controller
{
    /**
     * POST /panic/trigger
     *
     * Warga activates the panic button, sending location data.
     */
    public function trigger(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $panicLog = PanicLog::create([
            'user_id' => auth()->id(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Load the user's name and RT info for the response
        $panicLog->load('user:id,nama,phone,id_rt');

        return response()->json([
            'message' => 'Sinyal darurat berhasil dikirim ke seluruh RT.',
            'data' => $panicLog,
        ]);
    }
}
