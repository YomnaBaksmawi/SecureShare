<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lets an admin update a user name and role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'name' => 'Old Name',
        'role' => 'user',
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson('/api/admin/users', [
        'email' => $user->email,
        'name' => 'New Name',
        'role' => 'admin',
    ])->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.role', 'admin');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'role' => 'admin',
    ]);
});

it('keeps the existing name when name is omitted from the update', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'name' => 'Keep Me',
        'role' => 'user',
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson('/api/admin/users', [
        'email' => $user->email,
        'role' => 'user',
    ])->assertStatus(200)
        ->assertJsonPath('data.name', 'Keep Me');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Keep Me',
    ]);
});

it('does not let a normal user update users', function () {
    $normal = User::factory()->create();
    $target = User::factory()->create();

    Sanctum::actingAs($normal);

    $this->patchJson('/api/admin/users', [
        'email' => $target->email,
        'name' => 'Hacked',
        'role' => 'admin',
    ])->assertStatus(403);
});

it('excludes soft-deleted users from the normal admin index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activeUser = User::factory()->create();
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($activeUser->id)
        ->and($ids)->not->toContain($deletedUser->id);
});

it('returns only soft-deleted users when trashed=only', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activeUser = User::factory()->create();
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users?trashed=only');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->not->toContain($activeUser->id)
        ->and($ids)->not->toContain($admin->id)
        ->and($ids)->toContain($deletedUser->id);
});

it('returns deleted and non-deleted users when trashed=with', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activeUser = User::factory()->create();
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users?trashed=with');

    $response->assertStatus(200);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($activeUser->id)
        ->and($ids)->toContain($deletedUser->id);
});

it('rejects an invalid trashed value', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/users?trashed=invalid')
        ->assertStatus(422);
});

it('lets an admin restore a user returned by the trash filter', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $user->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users?trashed=only');

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($user->id);

    $this->postJson("/api/admin/users/{$user->id}/restore")
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});