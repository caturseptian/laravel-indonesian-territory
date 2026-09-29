<?php

namespace App\Enums;

enum VillageType: string
{
    case Kelurahan = 'kelurahan';
    case Desa = 'desa';
    case DesaAdat = 'desa_adat';
}
