<?php

use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegencyController;
use App\Http\Controllers\Api\VillageController;
use Illuminate\Support\Facades\Route;

Route::get('provinces', [ProvinceController::class, 'index']);
Route::get('provinces/{province}', [ProvinceController::class, 'show']);
Route::get('provinces/{province}/regencies', [ProvinceController::class, 'regencies']);

Route::get('regencies/{regency}', [RegencyController::class, 'show']);
Route::get('regencies/{regency}/districts', [RegencyController::class, 'districts']);

Route::get('districts/{district}', [DistrictController::class, 'show']);
Route::get('districts/{district}/villages', [DistrictController::class, 'villages']);

Route::get('villages', [VillageController::class, 'index']);
Route::get('villages/{village}', [VillageController::class, 'show']);
