<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'action' => $this->action,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],

            'auditable_type' => $this->auditable_type,

            'auditable_id' => $this->auditable_id,

            'metadata' => $this->metadata,

            'created_at' => $this->created_at,
        ];
    }
}