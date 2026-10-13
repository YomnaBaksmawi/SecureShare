<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    public function sendTestNotification(string $token): void
    {
        $this->send(
            $token,
            'Test Notification',
            'Firebase push notification is working!'
        );
    }

    public function send(string $token, string $title, string $body): void
    {
        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        $this->messaging()->send($message);
    }

    public function storeToken(int $userId, string $token): void
    {
        Cache::put(
            "fcm_token:{$userId}",
            $token,
            now()->addDays(30)
        );
    }

    public function getToken(int $userId): ?string
    {
        return Cache::get("fcm_token:{$userId}");
    }

    private function messaging(): Messaging
    {
        $factory = (new Factory())
            ->withServiceAccount(
                base_path(config('firebase.credentials'))
            );

        $caCert = config('firebase.ca_cert');

        if ($caCert && is_file(base_path($caCert))) {
            $options = HttpClientOptions::default()
                ->withGuzzleConfigOption('verify', base_path($caCert));

            $factory = $factory->withHttpClientOptions($options);
        }

        return $factory->createMessaging();
    }
}