<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'document_id' => $this->document_id,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],

            'permission' => $this->permission,

            'expires_at' => $this->expires_at,

            'revoked_at' => $this->revoked_at,

            'created_at' => $this->created_at,
        ];
    }
}