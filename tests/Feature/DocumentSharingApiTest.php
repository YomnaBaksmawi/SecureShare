<?php

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');
});

function seedDocumentForDownload(User $owner, string $fileName = 'document.pdf'): Document
{
    $document = Document::factory()->create([
        'user_id' => $owner->id,
        'original_name' => $fileName,
    ]);

    Storage::disk('private')->put($document->storage_path, 'fake-file-contents');

    return $document;
}

function makeFriends(User $userA, User $userB): void
{
    Friendship::create([
        'requester_id' => $userA->id,
        'addressee_id' => $userB->id,
        'status' => 'accepted',
    ]);
}

it('registers a user and returns 201 without a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.token')
        ->assertJsonStructure([
            'message',
            'data' => ['user' => ['id', 'name', 'email']],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

it('logs in an existing user and returns 200 without a token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.token')
        ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email']]]);
});

it('returns 422 for invalid login credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422);
});

it('lets an authenticated user upload a document to the private disk', function () {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/documents', [
        'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('documents', [
        'user_id' => $owner->id,
        'original_name' => 'document.pdf',
        'disk' => 'private',
    ]);

    $document = Document::where('user_id', $owner->id)->first();

    expect($document)->not->toBeNull();
    expect(Storage::disk('private')->exists($document->storage_path))->toBeTrue();
});

it('lists only the authenticated user\'s own documents', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $ownDocument = seedDocumentForDownload($owner, 'mine.pdf');
    $otherDocument = seedDocumentForDownload($other, 'theirs.pdf');

    Sanctum::actingAs($owner);

    $response = $this->getJson('/api/documents');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($ownDocument->id)
        ->and($ids)->not->toContain($otherDocument->id);
});

it('lets the owner view a document', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($owner);

    $this->getJson("/api/documents/{$document->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.id', $document->id);
});

it('blocks another user from viewing a document they do not own', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($intruder);

    $this->getJson("/api/documents/{$document->id}")->assertStatus(403);
});

it('lets the owner delete their document', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/documents/{$document->id}")
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->assertSoftDeleted('documents', ['id' => $document->id]);
});

it('lets the owner create a read share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $recipient);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'read',
    ])->assertStatus(201)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('document_shares', [
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'revoked_at' => null,
    ]);
});

it('lets the owner create a write share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $recipient);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'write',
    ])->assertStatus(201)
        ->assertJsonPath('data.permission', 'write');
});

it('returns 409 with error_code DUPLICATE_SHARE for a duplicate active share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $recipient);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->addDay(),
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'write',
        'expires_at' => now()->addDays(2)->toDateTimeString(),
    ])->assertStatus(409)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error_code', 'DUPLICATE_SHARE');
});

it('allows an expired share to be replaced', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $recipient);

    $expiredShare = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'write',
        'expires_at' => now()->addDay()->toDateTimeString(),
    ])->assertStatus(201)
        ->assertJsonPath('success', true);

    expect($expiredShare->fresh()->revoked_at)->not->toBeNull();

    $this->assertDatabaseCount('document_shares', 2);
    $this->assertDatabaseHas('document_shares', [
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'write',
        'revoked_at' => null,
    ]);
});

it('lets the owner list document shares', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    $share = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($owner);

    $this->getJson("/api/documents/{$document->id}/shares")
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    $ids = collect($this->getJson("/api/documents/{$document->id}/shares")->json('data'))->pluck('id');

    expect($ids)->toContain($share->id);
});

it('blocks a non-owner from listing document shares', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($intruder);

    $this->getJson("/api/documents/{$document->id}/shares")->assertStatus(403);
});

it('lets the owner revoke a share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    $share = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/documents/{$document->id}/shares/{$share->id}")
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('document_shares', ['id' => $share->id]);
    expect($share->fresh()->revoked_at)->not->toBeNull();
});

it('blocks a non-owner from revoking a share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $intruder = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    $share = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($intruder);

    $this->deleteJson("/api/documents/{$document->id}/shares/{$share->id}")
        ->assertStatus(403);

    expect($share->fresh()->revoked_at)->toBeNull();
});

it('returns 404 when revoking a share that belongs to another document', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = seedDocumentForDownload($owner);
    $otherDocument = seedDocumentForDownload($owner, 'other.pdf');

    $share = DocumentShare::create([
        'document_id' => $otherDocument->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/documents/{$document->id}/shares/{$share->id}")
        ->assertStatus(404);
});

it('lets the owner download a document from the private disk', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner, 'quarterly-report.pdf');

    Sanctum::actingAs($owner);

    $response = $this->get("/api/documents/{$document->id}/download");

    $response->assertStatus(200);
    expect($response->headers->get('content-disposition'))->toContain('quarterly-report.pdf');
    expect($response->streamedContent())
        ->toBe(Storage::disk('private')->get($document->storage_path));
});

it('lets an active shared user download the document', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($recipient);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(200);
});

it('does not let a user with an expired share download the document', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->subDay(),
    ]);

    Sanctum::actingAs($recipient);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(403);
});

it('does not let a user with a revoked share download the document', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'revoked_at' => now(),
    ]);

    Sanctum::actingAs($recipient);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(403);
});

it('does not let an unrelated authenticated user download the document', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($intruder);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(403);
});

it('does not allow downloading another user\'s document by changing the document id', function () {
    [$targetOwner, $intruder] = User::factory()->count(2)->create();

    $intrudersDocument = seedDocumentForDownload($intruder, 'mine.pdf');
    $targetsDocument = seedDocumentForDownload($targetOwner, 'secret.pdf');

    Sanctum::actingAs($intruder);

    $this->get("/api/documents/{$intrudersDocument->id}/download")->assertStatus(200);

    $this->get("/api/documents/{$targetsDocument->id}/download")->assertStatus(403);
});

it('does not expose shared files publicly', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    expect($document->disk)->toBe('private');
    expect(Storage::disk('public')->missing($document->storage_path))->toBeTrue();
});

it('creates a DOCUMENT_UPLOADED audit log on upload', function () {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $this->postJson('/api/documents', [
        'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ])->assertStatus(201);

    $document = Document::where('user_id', $owner->id)->first();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $owner->id,
        'action' => 'DOCUMENT_UPLOADED',
        'auditable_type' => Document::class,
        'auditable_id' => $document->id,
    ]);
});

it('creates a DOCUMENT_DOWNLOADED audit log on download', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($owner);

    $this->get("/api/documents/{$document->id}/download")->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $owner->id,
        'action' => 'DOCUMENT_DOWNLOADED',
        'auditable_type' => Document::class,
        'auditable_id' => $document->id,
    ]);
});

it('creates a DOCUMENT_SHARED audit log when sharing a document', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $recipient);

    Sanctum::actingAs($owner);

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'read',
    ])->assertStatus(201);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $owner->id,
        'action' => 'DOCUMENT_SHARED',
        'auditable_type' => DocumentShare::class,
    ]);
});

it('creates a DOCUMENT_SHARE_REVOKED audit log when revoking a share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    $share = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/documents/{$document->id}/shares/{$share->id}")->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $owner->id,
        'action' => 'DOCUMENT_SHARE_REVOKED',
        'auditable_type' => DocumentShare::class,
        'auditable_id' => $share->id,
    ]);
});

it('creates a DOCUMENT_DELETED audit log when deleting a document', function () {
    $owner = User::factory()->create();
    $document = seedDocumentForDownload($owner);

    Sanctum::actingAs($owner);

    $this->deleteJson("/api/documents/{$document->id}")->assertStatus(200);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $owner->id,
        'action' => 'DOCUMENT_DELETED',
        'auditable_type' => Document::class,
        'auditable_id' => $document->id,
    ]);
});

it('does not store secrets or file contents in audit metadata', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $secret = 'super-secret-token-value';
    $fileBody = 'raw-file-body-content';
    makeFriends($owner, $recipient);

    Sanctum::actingAs($owner);

    $this->postJson('/api/documents', [
        'file' => UploadedFile::fake()->createWithContent('document.pdf', $fileBody, 'application/pdf'),
    ])->assertStatus(201);

    $document = Document::where('user_id', $owner->id)->first();

    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $recipient->id,
        'permission' => 'read',
    ])->assertStatus(201);

    $share = DocumentShare::where('document_id', $document->id)->first();

    $this->deleteJson("/api/documents/{$document->id}/shares/{$share->id}")->assertStatus(200);
    $this->get("/api/documents/{$document->id}/download")->assertStatus(200);
    $this->deleteJson("/api/documents/{$document->id}")->assertStatus(200);

    $logs = AuditLog::all();

    expect($logs->count())->toBeGreaterThanOrEqual(5);

    foreach ($logs as $log) {
        $metadata = $log->metadata ?? [];

        expect($metadata)
            ->not->toHaveKey('password')
            ->not->toHaveKey('token')
            ->not->toHaveKey('file')
            ->not->toContain($secret)
            ->not->toContain($fileBody);

        if (is_object($metadata) || is_array($metadata)) {
            $flattened = json_encode($metadata);

            expect($flattened)->not->toContain($secret);
            expect($flattened)->not->toContain($fileBody);
        }
    }
});

it('forbids a normal user from viewing audit logs (403)', function () {
    $owner = User::factory()->create();
    $unrelated = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $unrelated);

    Sanctum::actingAs($owner);
    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $unrelated->id,
        'permission' => 'read',
    ])->assertStatus(201);

    Sanctum::actingAs($unrelated);

    $this->getJson('/api/admin/audit-logs')
        ->assertStatus(403);
});

it('allows an admin to view all audit logs', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $unrelated = User::factory()->create();
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $unrelated);

    Sanctum::actingAs($owner);
    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $unrelated->id,
        'permission' => 'read',
    ])->assertStatus(201);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/audit-logs');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [['action']],
        ]);

    // Admin can see audit logs created by the owner.
    $actions = collect($response->json('data'))->pluck('action');

    expect($actions)->toContain('DOCUMENT_SHARED');
});

it('allows an admin to filter audit logs by action', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $document = seedDocumentForDownload($owner);
    makeFriends($owner, $admin);

    Sanctum::actingAs($owner);
    $this->postJson("/api/documents/{$document->id}/shares", [
        'user_id' => $admin->id,
        'permission' => 'read',
    ])->assertStatus(201);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/audit-logs?action=DOCUMENT_SHARED');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $actions = collect($response->json('data'))->pluck('action');

    expect($actions)->each->toBe('DOCUMENT_SHARED');
});
