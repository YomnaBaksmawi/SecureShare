<?php

use App\Actions\DocumentShare\CreateDocumentShareAction;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('creates a document share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);

    $action = app(CreateDocumentShareAction::class);

    $share = $action->execute(
        document: $document,
        userId: $recipient->id,
        permission: 'read',
        expiresAt: now()->addDay(),
    );

    expect($share)
        ->toBeInstanceOf(DocumentShare::class)
        ->and($share->document_id)->toBe($document->id)
        ->and($share->user_id)->toBe($recipient->id)
        ->and($share->permission)->toBe('read');

    $this->assertDatabaseHas('document_shares', [
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'revoked_at' => null,
    ]);
});

it('rejects a duplicate active share', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->addDay(),
    ]);

    $action = app(CreateDocumentShareAction::class);

    expect(fn () => $action->execute(
        document: $document,
        userId: $recipient->id,
        permission: 'write',
        expiresAt: now()->addDays(2),
    ))->toThrow(
        \RuntimeException::class,
        'An active share already exists.'
    );
});

it('allows a new share after the previous share expires', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);

    $expiredShare = DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->subDay(),
    ]);

    $action = app(CreateDocumentShareAction::class);

    $newShare = $action->execute(
        document: $document,
        userId: $recipient->id,
        permission: 'write',
        expiresAt: now()->addDay(),
    );

    expect($expiredShare->fresh()->revoked_at)->not->toBeNull();

    expect($newShare->user_id)
        ->toBe($recipient->id)
        ->and($newShare->permission)
        ->toBe('write');

    $this->assertDatabaseCount('document_shares', 2);
});

it('allows a new share after the previous share was revoked', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();

    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);

    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'permission' => 'read',
        'expires_at' => now()->addDay(),
        'revoked_at' => now(),
    ]);

    $action = app(CreateDocumentShareAction::class);

    $newShare = $action->execute(
        document: $document,
        userId: $recipient->id,
        permission: 'write',
        expiresAt: now()->addDays(2),
    );

    expect($newShare)
        ->toBeInstanceOf(DocumentShare::class)
        ->and($newShare->permission)->toBe('write');

    $this->assertDatabaseCount('document_shares', 2);
});

