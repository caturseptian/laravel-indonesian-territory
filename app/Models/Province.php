<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Provinsi, kode "NN" (contoh: 31 DKI Jakarta).
 */
class Province extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['code', 'name'];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class, 'province_code');
    }

    public function districts(): HasManyThrough
    {
        return $this->hasManyThrough(District::class, Regency::class, 'province_code', 'regency_code');
    }
}
