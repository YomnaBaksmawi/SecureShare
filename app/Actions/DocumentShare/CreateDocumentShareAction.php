<?php

namespace App\Actions\DocumentShare;

use App\Models\Document;
use App\Models\DocumentShare;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateDocumentShareAction
{
    public function execute( Document $document,int $userId, string $permission, $expiresAt = null): DocumentShare {
        return DB::transaction(function () use ( $document, $userId, $permission, $expiresAt ) {
            $existingShare = DocumentShare::query()
                ->where('document_id', $document->id)
                ->where('user_id', $userId)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->first();

            if ($existingShare) {
                if (
                    $existingShare->expires_at !== null
                    && $existingShare->expires_at->isPast()
                ) {
                    $existingShare->update([
                        'revoked_at' => now(),
                    ]);
                } else {
                    throw new RuntimeException(
                        'An active share already exists.'
                    );
                }
            }

            return DocumentShare::create([
                'document_id' => $document->id,
                'user_id' => $userId,
                'permission' => $permission,
                'expires_at' => $expiresAt,
            ]);
        });
    }
}