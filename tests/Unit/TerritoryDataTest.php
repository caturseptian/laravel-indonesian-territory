<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TerritoryDataTest extends TestCase
{
    private const DATA = __DIR__.'/../../database/data';

    /**
     * Pola kode per tingkat, Permendagri 58 Tahun 2021 Pasal 4-6.
     */
    private const PATTERNS = [
        'provinces' => '/^\d{2}$/',
        'regencies' => '/^\d{2}\.\d{2}$/',
        'districts' => '/^\d{2}\.\d{2}\.\d{2}$/',
        'villages' => '/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/',
    ];

    private const PARENTS = [
        'regencies' => ['provinces', 'province_code'],
        'districts' => ['regencies', 'regency_code'],
        'villages' => ['districts', 'district_code'],
    ];

    /**
     * @var array<string, list<array<string, string>>>
     */
    private static array $rows = [];

    /**
     * @return list<array<string, string>>
     */
    private static function rows(string $level): array
    {
        if (! isset(self::$rows[$level])) {
            $handle = fopen(self::DATA."/{$level}.csv", 'r');
            $header = fgetcsv($handle, escape: '');

            self::$rows[$level] = [];
            while (($values = fgetcsv($handle, escape: '')) !== false) {
                self::$rows[$level][] = array_combine($header, $values);
            }

            fclose($handle);
        }

        return self::$rows[$level];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function levels(): array
    {
        return array_combine(array_keys(self::PATTERNS), array_map(fn ($level) => [$level], array_keys(self::PATTERNS)));
    }

    #[DataProvider('levels')]
    public function test_counts_match_metadata(string $level): void
    {
        $metadata = json_decode(file_get_contents(self::DATA.'/metadata.json'), true);

        $this->assertCount($metadata['counts'][$level], self::rows($level));
    }

    #[DataProvider('levels')]
    public function test_codes_are_well_formed_and_unique(string $level): void
    {
        $codes = array_column(self::rows($level), 'code');

        $this->assertSame([], array_values(preg_grep(self::PATTERNS[$level], $codes, PREG_GREP_INVERT)));
        $this->assertSame(count($codes), count(array_unique($codes)));
        $this->assertNotContains('', array_column(self::rows($level), 'name'));
    }

    public function test_every_child_has_its_parent(): void
    {
        foreach (self::PARENTS as $level => [$parentLevel, $column]) {
            $parents = array_flip(array_column(self::rows($parentLevel), 'code'));

            foreach (self::rows($level) as $row) {
                $this->assertArrayHasKey($row[$column], $parents, "{$level} {$row['code']}");
                $this->assertStringStartsWith($row[$column].'.', $row['code']);
            }
        }
    }

    public function test_types_follow_codes(): void
    {
        foreach (self::rows('regencies') as $row) {
            $this->assertSame((int) substr($row['code'], 3) >= 71 ? 'kota' : 'kabupaten', $row['type'], $row['code']);
        }

        $types = ['1' => 'kelurahan', '2' => 'desa', '3' => 'desa_adat'];
        foreach (self::rows('villages') as $row) {
            $this->assertSame($types[$row['code'][9]], $row['type'], $row['code']);
            $this->assertMatchesRegularExpression('/^(\d{5})?$/', $row['postal_code'], $row['code']);
        }
    }
}
