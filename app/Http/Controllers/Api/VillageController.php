<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VillageResource;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VillageController extends Controller
{
    /**
     * Cari kelurahan/desa berdasarkan nama atau kode pos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['required_without:postal_code', 'string', 'min:3', 'max:100', 'not_regex:/[%_\\\\]/'],
            'postal_code' => ['required_without:q', 'digits:5'],
        ]);

        $villages = Village::query()
            ->with('district.regency.province')
            ->when($validated['q'] ?? null, fn ($query, string $q) => $query->whereLike('name', "%{$q}%"))
            ->when($validated['postal_code'] ?? null, fn ($query, string $postalCode) => $query->where('postal_code', $postalCode))
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return VillageResource::collection($villages);
    }

    public function show(Village $village): VillageResource
    {
        return VillageResource::make($village->load('district.regency.province'));
    }
}
