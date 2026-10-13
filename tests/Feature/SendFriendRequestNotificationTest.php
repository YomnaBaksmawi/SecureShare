<?php

use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates a pending friend request and notifies the addressee', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    Notification::fake();

    Sanctum::actingAs($requester);

    $response = $this->postJson('/api/friend-requests', [
        'user_id' => $addressee->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'pending');

    $friendship = Friendship::query()
        ->where('requester_id', $requester->id)
        ->where('addressee_id', $addressee->id)
        ->first();

    expect($friendship)->not->toBeNull();
    expect($friendship->status)->toBe('pending');

    Notification::assertSentTo(
        $addressee,
        FriendRequestReceivedNotification::class,
        function (FriendRequestReceivedNotification $notification) use ($friendship, $requester, $addressee) {
            expect($notification->friendship->id)->toBe($friendship->id);

            $data = $notification->toDatabase($addressee);

            expect($data['type'])->toBe('friend_request')
                ->and($data['friendship_id'])->toBe($friendship->id)
                ->and($data['requester_id'])->toBe($requester->id)
                ->and($data['requester_name'])->toBe($requester->name)
                ->and($data['message'])->toBe($requester->name.' sent you a friend request.');

            return true;
        }
    );
});