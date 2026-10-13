<?php

namespace App\Notifications;

use App\Models\Friendship;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FriendRequestReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Friendship $friendship
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'friend_request',
            'friendship_id' => $this->friendship->id,
            'requester_id' => $this->friendship->requester_id,
            'requester_name' => $this->friendship->requester->name,
            'message' => $this->friendship->requester->name
                . ' sent you a friend request.',
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'New Friend Request',
            'body' => $this->friendship->requester->name
                . ' sent you a friend request.',
        ];
    }
}