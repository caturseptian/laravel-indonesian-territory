<?php

namespace App\Http\Resources;

use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Village
 */
class VillageResource extends JsonResource
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
            'postal_code' => $this->postal_code,
            'district_code' => $this->district_code,
            'district' => DistrictResource::make($this->whenLoaded('district')),
        ];
    }
}
