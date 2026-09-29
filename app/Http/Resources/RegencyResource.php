<?php

namespace App\Http\Resources;

use App\Models\Regency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Regency
 */
class RegencyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'province_code' => $this->province_code,
            'province' => ProvinceResource::make($this->whenLoaded('province')),
            'districts_count' => $this->whenCounted('districts'),
        ];
    }
}
