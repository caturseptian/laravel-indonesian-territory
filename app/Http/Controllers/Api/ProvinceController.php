<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\RegencyResource;
use App\Models\Province;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProvinceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProvinceResource::collection(Province::query()->orderBy('code')->get());
    }

    public function show(Province $province): ProvinceResource
    {
        return ProvinceResource::make($province->loadCount('regencies'));
    }

    public function regencies(Province $province): AnonymousResourceCollection
    {
        return RegencyResource::collection($province->regencies()->orderBy('code')->get());
    }
}
