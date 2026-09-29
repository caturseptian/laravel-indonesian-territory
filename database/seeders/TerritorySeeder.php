<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Mengisi tabel wilayah dari database/data/*.csv (Kepmendagri 300.2.2-2138 Tahun 2025).
 */
class TerritorySeeder extends Seeder
{
    /**
     * Tabel diisi berurutan agar kunci asing induk selalu sudah ada.
     */
    private const TABLES = ['provinces', 'regencies', 'districts', 'villages'];

    private const CHUNK = 1000;

    public function run(): void
    {
        foreach (array_reverse(self::TABLES) as $table) {
            DB::table($table)->delete();
        }

        foreach (self::TABLES as $table) {
            DB::transaction(fn () => $this->seed($table));
            $this->command?->line(sprintf('  %s: %s', $table, number_format(DB::table($table)->count())));
        }
    }

    private function seed(string $table): void
    {
        $path = database_path("data/{$table}.csv");
        $handle = fopen($path, 'r') ?: throw new RuntimeException("Tidak bisa membuka {$path}.");
        $header = fgetcsv($handle, escape: '');

        $rows = [];
        while (($values = fgetcsv($handle, escape: '')) !== false) {
            $row = array_combine($header, $values);

            if (array_key_exists('postal_code', $row) && $row['postal_code'] === '') {
                $row['postal_code'] = null;
            }

            $rows[] = $row;

            if (count($rows) === self::CHUNK) {
                DB::table($table)->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }

        fclose($handle);
    }
}
