<?php
namespace App\Actions\Friendship;

use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestReceivedNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SendFriendRequestAction
{
    public function execute(
        int $requesterId,
        int $addresseeId
    ): Friendship {
        return DB::transaction(function () use (
            $requesterId,
            $addresseeId
        ) {
            $existing = Friendship::query()
                ->where(function ($query) use (
                    $requesterId,
                    $addresseeId
                ) {
                    $query
                        ->where('requester_id', $requesterId)
                        ->where('addressee_id', $addresseeId);
                })
                ->orWhere(function ($query) use (
                    $requesterId,
                    $addresseeId
                ) {
                    $query
                        ->where('requester_id', $addresseeId)
                        ->where('addressee_id', $requesterId);
                })
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === 'accepted') {
                    throw new RuntimeException(
                        'You are already friends with this user.'
                    );
                }

                if ($existing->status === 'pending') {
                    throw new RuntimeException(
                        'A friend request already exists.'
                    );
                }

                $existing->update([
                    'requester_id' => $requesterId,
                    'addressee_id' => $addresseeId,
                    'status' => 'pending',
                ]);

                $friendship = $existing->fresh();
            } else {
                $friendship = Friendship::create([
                    'requester_id' => $requesterId,
                    'addressee_id' => $addresseeId,
                    'status' => 'pending',
                ]);
            }

            $addressee = User::findOrFail($addresseeId);

            $addressee->notify(
                new FriendRequestReceivedNotification($friendship)
            );

            return $friendship;
        });
    }
}

