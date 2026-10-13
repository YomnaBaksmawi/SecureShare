<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceTokenRequest;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function store(  StoreDeviceTokenRequest $request, FirebaseNotificationService $firebase ): JsonResponse {
        $firebase->storeToken(
            auth()->id(),
            $request->string('token')->toString()
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token stored successfully.',
        ]);
    }
}