<?php

namespace App\Http\Resources;

use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Province
 */
class ProvinceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'regencies_count' => $this->whenCounted('regencies'),
        ];
    }
}
