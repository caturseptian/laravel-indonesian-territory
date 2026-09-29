<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\VillageResource;
use App\Models\District;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DistrictController extends Controller
{
    public function show(District $district): DistrictResource
    {
        return DistrictResource::make($district->load('regency.province')->loadCount('villages'));
    }

    public function villages(District $district): AnonymousResourceCollection
    {
        return VillageResource::collection($district->villages()->orderBy('code')->get());
    }
}
