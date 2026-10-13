<?php

use App\Models\Friendship;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\FriendRequestReceivedNotification;
use App\Services\FirebaseNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('stores the FCM token in cache via the device-token endpoint', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/device-token', [
        'token' => 'fcm-test-token-123',
    ])->assertStatus(200)
        ->assertJsonPath('success', true);

    expect(Cache::get("fcm_token:{$user->id}"))
        ->toBe('fcm-test-token-123');
});

it('sends an FCM push with the cached token when a friend request is received', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
        'status' => 'pending',
    ]);

    $firebase = Mockery::mock(FirebaseNotificationService::class);
    $firebase->shouldReceive('getToken')
        ->once()
        ->with($addressee->id)
        ->andReturn('fcm-token-abc');
    $firebase->shouldReceive('send')
        ->once()
        ->with(
            'fcm-token-abc',
            'New Friend Request',
            $requester->name.' sent you a friend request.'
        );

    $channel = new FcmChannel($firebase);
    $notification = new FriendRequestReceivedNotification($friendship);

    $channel->send($addressee, $notification);
});

it('skips FCM silently when the addressee has no cached token', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
        'status' => 'pending',
    ]);

    $firebase = Mockery::mock(FirebaseNotificationService::class);
    $firebase->shouldReceive('getToken')
        ->once()
        ->with($addressee->id)
        ->andReturn(null);
    $firebase->shouldNotReceive('send');

    $channel = new FcmChannel($firebase);
    $notification = new FriendRequestReceivedNotification($friendship);

    $channel->send($addressee, $notification);
});

it('wires the friend request notification to both database and FCM channels', function () {
    $requester = User::factory()->create();
    $addressee = User::factory()->create();

    $friendship = Friendship::create([
        'requester_id' => $requester->id,
        'addressee_id' => $addressee->id,
        'status' => 'pending',
    ]);

    $notification = new FriendRequestReceivedNotification($friendship);

    expect($notification->via($addressee))
        ->toContain('database')
        ->toContain(FcmChannel::class);
});