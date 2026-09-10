<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->getAttributes();
        $hasStorageUsage = array_key_exists('storage_used_bytes', $attributes)
            && array_key_exists('storage_quota_bytes', $attributes);
        $storageUsedBytes = (int) ($attributes['storage_used_bytes'] ?? 0);
        $storageQuotaBytes = (int) ($attributes['storage_quota_bytes'] ?? 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'storage_used_bytes' => $this->when($hasStorageUsage, $storageUsedBytes),
            'storage_quota_bytes' => $this->when($hasStorageUsage, $storageQuotaBytes),
            'storage_remaining_bytes' => $this->when(
                $hasStorageUsage,
                max(0, $storageQuotaBytes - $storageUsedBytes),
            ),
            'created_at' => $this->created_at,
        ];
    }
}
