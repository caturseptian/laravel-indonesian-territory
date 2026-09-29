<?php

namespace App\Models;

use App\Enums\RegencyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Kabupaten/kota, kode "NN.NN" (01-69 kabupaten, 71-99 kota).
 */
class Regency extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['code', 'province_code', 'type', 'name'];

    protected function casts(): array
    {
        return [
            'type' => RegencyType::class,
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'regency_code');
    }

    public function villages(): HasManyThrough
    {
        return $this->hasManyThrough(Village::class, District::class, 'regency_code', 'district_code');
    }
}
