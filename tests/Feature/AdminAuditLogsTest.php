<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('rejects unauthenticated access to audit logs', function () {
    $this->getJson('/api/admin/audit-logs')->assertStatus(401);
});

it('rejects non-admin access to audit logs', function () {
    $normal = User::factory()->create();

    Sanctum::actingAs($normal);

    $this->getJson('/api/admin/audit-logs')->assertStatus(403);
});

it('lets an admin fetch audit logs', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_UPLOADED',
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/audit-logs')
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('includes pagination meta in the audit logs response', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_UPLOADED',
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_SHARED',
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_DELETED',
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/audit-logs')
        ->assertStatus(200)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.total', 3)
        ->assertJsonCount(3, 'data');
});

it('filters audit logs by the DOCUMENT_SHARE_REVOKED action', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_SHARED',
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_SHARE_REVOKED',
    ]);

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'DOCUMENT_UPLOADED',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson(
        '/api/admin/audit-logs?action=DOCUMENT_SHARE_REVOKED'
    );

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.action', 'DOCUMENT_SHARE_REVOKED');
});

it('includes soft-deleted users in admin user options', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activeUser = User::factory()->create();
    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/users/options');

    $response->assertStatus(200)->assertJsonPath('success', true);

    $options = collect($response->json('data'));

    $ids = $options->pluck('id');

    expect($ids)->toContain($activeUser->id)
        ->and($ids)->toContain($deletedUser->id);

    foreach ($options as $option) {
        expect(array_keys($option))->toBe(['id', 'name', 'email']);
    }
});

it('rejects non-admin access to user options', function () {
    $normal = User::factory()->create();

    Sanctum::actingAs($normal);

    $this->getJson('/api/admin/users/options')->assertStatus(403);
});