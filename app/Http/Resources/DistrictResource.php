<?php

namespace App\Http\Resources;

use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin District
 */
class DistrictResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'regency_code' => $this->regency_code,
            'regency' => RegencyResource::make($this->whenLoaded('regency')),
            'villages_count' => $this->whenCounted('villages'),
        ];
    }
}
