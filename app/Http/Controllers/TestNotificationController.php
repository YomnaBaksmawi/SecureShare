<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendTestNotificationRequest;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Exception\FirebaseException;

class TestNotificationController extends Controller
{
    public function send(
        SendTestNotificationRequest $request,
        FirebaseNotificationService $firebase
    ): JsonResponse {
        try {
            $firebase->sendTestNotification(
                $request->string('token')->toString()
            );
        } catch (FirebaseException) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent successfully.',
        ]);
    }
}