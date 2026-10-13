<?php

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


uses(RefreshDatabase::class);


it('allows a user with write permission to update a document', function () {

    Storage::fake('private');


    $owner = User::factory()->create();

    $writer = User::factory()->create();


    $document = Document::factory()->create([
        'user_id' => $owner->id,
        'disk' => 'private',
        'storage_path' => 'documents/old.pdf',
    ]);


    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $writer->id,
        'permission' => 'write',
    ]);


    $response = $this
        ->actingAs($writer)
        ->put("/api/documents/{$document->id}", [
            'file' => UploadedFile::fake()
                ->create('new.pdf', 100),
        ]);


    $response->assertStatus(200);


    expect($document->fresh()->original_name)
        ->toBe('new.pdf');
});


it('blocks a user with read permission from updating a document', function () {

    $owner = User::factory()->create();

    $reader = User::factory()->create();


    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);


    DocumentShare::create([
        'document_id' => $document->id,
        'user_id' => $reader->id,
        'permission' => 'read',
    ]);


    $response = $this
        ->actingAs($reader)
        ->put("/api/documents/{$document->id}", [
            'file' => UploadedFile::fake()
                ->create('new.pdf'),
        ]);


    $response->assertStatus(403);
});


it('allows the owner to update their document', function () {

    Storage::fake('private');


    $owner = User::factory()->create();


    $document = Document::factory()->create([
        'user_id' => $owner->id,
    ]);


    $response = $this
        ->actingAs($owner)
        ->put("/api/documents/{$document->id}", [
            'file' => UploadedFile::fake()
                ->create('owner-update.pdf'),
        ]);


    $response->assertStatus(200);
});