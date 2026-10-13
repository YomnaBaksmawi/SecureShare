<?php

namespace App\Notifications\Channels;

use App\Services\FirebaseNotificationService;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\FirebaseException;

class FcmChannel
{
    public function __construct(
        private FirebaseNotificationService $firebase
    ) {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $token = $this->firebase->getToken($notifiable->getKey());

        if ($token === null) {
            return;
        }

        $payload = $notification->toFcm($notifiable);

        try {
            $this->firebase->send(
                $token,
                $payload['title'],
                $payload['body']
            );
        } catch (FirebaseException) {
            // A push notification failure must not break the request flow.
        }
    }
}