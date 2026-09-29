<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mengunduh data wilayah terbaru dari Indonesia Region API ke database/data.
 */
#[Signature('territory:sync {--url=https://indonesia-region.caturseptian.site : Alamat Indonesia Region API}')]
#[Description('Perbarui database/data/*.csv dari Indonesia Region API')]
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

    /**
     * Nilai kolom "level" di berkas ekspor, per tingkat wilayah.
     */
    private const LEVELS = [
        'province' => 'provinces',
        'regency' => 'regencies',
        'district' => 'districts',
        'village' => 'villages',
    ];

    public function handle(): int
    {
        $api = rtrim($this->option('url'), '/').'/api/v1';

        $metadata = Http::timeout(60)->get("{$api}/meta.json")->throw()->json();
        $export = tempnam(sys_get_temp_dir(), 'territory');
        Http::timeout(300)->sink($export)->get("{$api}/export/indonesia-regions.csv")->throw();

        $targets = [];
        foreach (self::COLUMNS as $level => $columns) {
            $targets[$level] = fopen(database_path("data/{$level}.csv"), 'w');
            fputcsv($targets[$level], $columns, escape: '');
        }

        $counts = array_fill_keys(array_keys(self::COLUMNS), 0);
        $postalCodes = 0;
        foreach ($this->read($export) as $row) {
            $level = self::LEVELS[$row['level']] ?? throw new RuntimeException("Tingkat tidak dikenal: {$row['level']}.");

            fputcsv($targets[$level], array_map(fn (string $column) => $row[$column], self::COLUMNS[$level]), escape: '');
            $counts[$level]++;
            $postalCodes += $level === 'villages' && $row['postal_code'] !== '' ? 1 : 0;
        }

        array_map(fclose(...), $targets);
        unlink($export);

        foreach ($counts as $level => $count) {
            $this->components->twoColumnDetail($level, number_format($count));

            if ($count !== $metadata['counts'][$level]) {
                throw new RuntimeException("Jumlah {$level} ({$count}) tidak sama dengan meta.json ({$metadata['counts'][$level]}).");
            }
        }

        file_put_contents(database_path('data/metadata.json'), json_encode([
            'dataset_version' => $metadata['dataset_version'],
            'document_title' => $this->title($metadata['dataset_version']),
            'effective_date' => $metadata['effective_date'],
            'counts' => array_intersect_key($metadata['counts'], array_flip([
                'provinces', 'regencies', 'regencies_by_type', 'districts', 'villages', 'villages_by_type',
            ])),
            'postal_codes' => $postalCodes,
            'source' => rtrim($this->option('url'), '/'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->components->info("Data {$metadata['dataset_version']} tersalin ke database/data.");

        return self::SUCCESS;
    }

    /**
     * "kepmendagri-300.2.2-2138-2025+2430-2025" menjadi "Kepmendagri 300.2.2-2138 Tahun 2025".
     */
    private function title(string $datasetVersion): string
    {
        preg_match('/^kepmendagri-(.+)-(\d{4})$/', explode('+', $datasetVersion)[0], $matches)
            ?: throw new RuntimeException("Versi dataset tidak dikenal: {$datasetVersion}.");

        return "Kepmendagri {$matches[1]} Tahun {$matches[2]}";
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
