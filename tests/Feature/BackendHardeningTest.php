<?php

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('rejects a friend request to a soft-deleted user with 422', function () {
    $requester = User::factory()->create();
    $deleted = User::factory()->create();
    $deleted->delete();

    Sanctum::actingAs($requester);

    $this->postJson('/api/friend-requests', ['user_id' => $deleted->id])
        ->assertStatus(422)
        ->assertJsonPath('message', 'You cannot send a friend request to this user.');
});

it('rejects a sanctum token that belongs to a soft-deleted user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('review')->plainTextToken;

    $user->delete();

    $this->withToken($token)
        ->getJson('/api/documents')
        ->assertStatus(401);
});

it('does not let a soft-deleted user log in', function () {
    $user = User::factory()->create([
        'email' => 'ghost@example.com',
        'password' => 'password',
    ]);
    $user->delete();

    $this->postJson('/api/login', [
        'email' => 'ghost@example.com',
        'password' => 'password',
    ])->assertStatus(422);
});

it('rejects sharing a document to a soft-deleted user', function () {
    $owner = User::factory()->create();
    $deleted = User::factory()->create();
    $deleted->delete();

    $document = Document::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $deleted->id,
        'permission' => 'read',
    ])->assertStatus(422);
});

it('enforces the unique active share constraint at the database level', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $owner->id]);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    expect(function () use ($document, $recipient) {
        DocumentShare::create([
            'document_id' => $document->id,
            'user_id' => $recipient->id,
            'permission' => 'write',
        ]);
    })->toThrow(QueryException::class);
});

it('keeps the actor identity in audit logs after the user is soft-deleted', function () {
    Storage::fake('private');

    $admin = User::factory()->create(['role' => 'admin']);
    $actor = User::factory()->create(['name' => 'Ghost User']);

    Sanctum::actingAs($actor);

    $this->postJson('/api/documents', [
        'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ])->assertStatus(201);

    $actor->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/audit-logs');

    $response->assertStatus(200)->assertJsonPath('success', true);

    $actors = collect($response->json('data'))
        ->pluck('user')
        ->filter(fn ($user) => $user !== null && $user['name'] === 'Ghost User');

    expect($actors)->not->toBeEmpty();
});

it('keeps existing shares active after the friendship is removed', function () {
    Storage::fake('private');

    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = Document::factory()->create(['user_id' => $owner->id]);
    Storage::disk('private')->put($document->storage_path, 'content');

    $friendship = Friendship::create([
        'requester_id' => $owner->id,
        'addressee_id' => $recipient->id,
        'status' => 'accepted',
    ]);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/friends/{$friendship->id}")
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    $share = DocumentShare::where('document_id', $document->id)
        ->where('user_id', $recipient->id)
        ->first();

    expect($share->revoked_at)->toBeNull();

    Sanctum::actingAs($recipient);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(200);
});

it('rejects a friend request from the reverse direction while a request is pending', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Sanctum::actingAs($userA);

    $this->postJson('/api/friend-requests', ['user_id' => $userB->id])
        ->assertStatus(201);

    Sanctum::actingAs($userB);

    $this->postJson('/api/friend-requests', ['user_id' => $userA->id])
        ->assertStatus(409)
        ->assertJsonPath('error_code', 'FRIEND_REQUEST_INVALID');
});

it('blocks non-admin users from deactivating, restoring, or deleting users', function () {
    $normal = User::factory()->create();
    $target = User::factory()->create();

    Sanctum::actingAs($normal);

    $this->patchJson("/api/admin/users/{$target->id}/status", [
        'status' => 'inactive',
    ])->assertStatus(403);

    $this->postJson("/api/admin/users/{$target->id}/restore")->assertStatus(403);

    $this->deleteJson("/api/admin/users/{$target->id}")->assertStatus(403);
});
