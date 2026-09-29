<?php

namespace App\Models;

use App\Enums\VillageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kelurahan/desa, kode "NN.NN.NN.NNNN" (1xxx kelurahan, 2xxx desa, 3xxx desa adat).
 */
class Village extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['code', 'district_code', 'type', 'name', 'postal_code'];

    protected function casts(): array
    {
        return [
            'type' => VillageType::class,
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code');
    }
}
