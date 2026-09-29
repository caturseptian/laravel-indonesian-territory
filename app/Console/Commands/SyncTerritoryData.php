<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Menyalin data kanonik indonesia-region-api (https://indonesia-region.caturseptian.site) ke database/data.
 */
#[Signature('territory:sync {source : Path ke repositori indonesia-region-api}')]
#[Description('Perbarui database/data/*.csv dari data kanonik indonesia-region-api')]
class SyncTerritoryData extends Command
{
    /**
     * Kolom yang ditulis ke database/data, per tingkat wilayah.
     *
     * @var array<string, list<string>>
     */
    private const COLUMNS = [
        'provinces' => ['code', 'name'],
        'regencies' => ['code', 'province_code', 'type', 'name'],
        'districts' => ['code', 'regency_code', 'name'],
        'villages' => ['code', 'district_code', 'type', 'name', 'postal_code'],
    ];

    public function handle(): int
    {
        $source = rtrim($this->argument('source'), '/');
        $canonical = "{$source}/data/canonical";

        if (! is_file("{$canonical}/metadata.json")) {
            $this->error("Data kanonik tidak ditemukan di {$canonical}.");

            return self::FAILURE;
        }

        $postalCodes = [];
        foreach ($this->read("{$source}/data/postal/villages.csv") as $row) {
            $postalCodes[$row['code']] = $row['postal_code'];
        }

        $counts = [];
        foreach (self::COLUMNS as $level => $columns) {
            $target = fopen(database_path("data/{$level}.csv"), 'w');
            fputcsv($target, $columns, escape: '');

            $counts[$level] = 0;
            foreach ($this->read("{$canonical}/{$level}.csv") as $row) {
                $row['postal_code'] = $postalCodes[$row['code']] ?? '';
                fputcsv($target, array_map(fn (string $column) => $row[$column], $columns), escape: '');
                $counts[$level]++;
            }

            fclose($target);
            $this->components->twoColumnDetail($level, number_format($counts[$level]));
        }

        $metadata = json_decode(file_get_contents("{$canonical}/metadata.json"), true);

        file_put_contents(database_path('data/metadata.json'), json_encode([
            'dataset_version' => $metadata['dataset_version'],
            'effective_date' => $metadata['effective_date'],
            'base_document' => $metadata['base_document'],
            'amendments' => $metadata['amendments'],
            'counts' => $metadata['counts'],
            'postal_codes' => count(array_filter($postalCodes)),
            'source' => 'https://indonesia-region.caturseptian.site',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        foreach ($counts as $level => $count) {
            if ($count !== $metadata['counts'][$level]) {
                throw new RuntimeException("Jumlah {$level} ({$count}) tidak sama dengan metadata ({$metadata['counts'][$level]}).");
            }
        }

        $this->components->info("Data {$metadata['dataset_version']} tersalin ke database/data.");

        return self::SUCCESS;
    }

    /**
     * Baca CSV berheader sebagai array asosiatif, baris demi baris.
     *
     * @return \Generator<int, array<string, string>>
     */
    private function read(string $path): \Generator
    {
        $handle = fopen($path, 'r') ?: throw new RuntimeException("Tidak bisa membuka {$path}.");
        $header = fgetcsv($handle, escape: '');

        while (($values = fgetcsv($handle, escape: '')) !== false) {
            yield array_combine($header, $values);
        }

        fclose($handle);
    }
}
