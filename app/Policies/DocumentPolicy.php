<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        if ($document->user_id === $user->id) {
            return true;
        }

        return $document->shares()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function share(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }
    public function update(User $user, Document $document): bool
{
    // Owner can always update
    if ($document->user_id === $user->id) {
        return true;
    }

    return $document->shares()
        ->where('user_id', $user->id)
        ->where('permission', 'write')
        ->whereNull('revoked_at')
        ->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })
        ->exists();
}
}