<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTourController extends Controller
{
    public function complete(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'route_name' => ['required', 'string', 'max:150'],
        ]);

        $user = $request->user();
        $preferences = $user->preferences ?? [];
        $tours = $preferences['tours'] ?? [];
        $tours[$payload['route_name']] = true;
        $preferences['tours'] = $tours;

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'ok' => true,
        ]);
    }
}
