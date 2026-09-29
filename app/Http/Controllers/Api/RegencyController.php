<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegencyResource;
use App\Models\Regency;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegencyController extends Controller
{
    public function show(Regency $regency): RegencyResource
    {
        return RegencyResource::make($regency->load('province')->loadCount('districts'));
    }

    public function districts(Regency $regency): AnonymousResourceCollection
    {
        return DistrictResource::collection($regency->districts()->orderBy('code')->get());
    }
}
