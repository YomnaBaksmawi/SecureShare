<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'original_name' => $this->original_name,

            'mime_type' => $this->mime_type,

            'size' => $this->size,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

            // لا نرجع:
            // storage_path
            // disk
            // user_id
        ];
    }
}