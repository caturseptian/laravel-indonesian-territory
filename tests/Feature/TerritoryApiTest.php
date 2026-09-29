<?php

namespace Tests\Feature;

use App\Enums\RegencyType;
use App\Enums\VillageType;
use App\Models\Province;
use App\Models\Village;
use Database\Seeders\TerritorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = TerritorySeeder::class;

    public function test_seeder_loads_every_region(): void
    {
        $this->assertDatabaseCount('provinces', 38);
        $this->assertDatabaseCount('regencies', 514);
        $this->assertDatabaseCount('districts', 7285);
        $this->assertDatabaseCount('villages', 83762);
    }

    public function test_models_walk_the_hierarchy(): void
    {
        $village = Village::query()->findOrFail('31.71.01.1001');

        $this->assertSame(VillageType::Kelurahan, $village->type);
        $this->assertSame('Gambir', $village->district->name);
        $this->assertSame(RegencyType::Kota, $village->district->regency->type);
        $this->assertSame('31', $village->district->regency->province->code);
        $this->assertSame(6, Province::query()->findOrFail('31')->regencies()->count());
    }

    public function test_lists_provinces(): void
    {
        $this->getJson('/api/provinces')
            ->assertOk()
            ->assertJsonCount(38, 'data')
            ->assertJsonPath('data.0', ['code' => '11', 'name' => 'Aceh']);
    }

    public function test_walks_down_from_province_to_village(): void
    {
        $this->getJson('/api/provinces/31/regencies')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonFragment(['code' => '31.71', 'type' => 'kota']);

        $this->getJson('/api/regencies/31.71/districts')
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonFragment(['code' => '31.71.01', 'name' => 'Gambir']);

        $this->getJson('/api/districts/31.71.01/villages')
            ->assertOk()
            ->assertJsonFragment(['code' => '31.71.01.1001', 'postal_code' => '10110']);
    }

    public function test_shows_a_village_with_its_parents(): void
    {
        $this->getJson('/api/villages/31.71.01.1001')
            ->assertOk()
            ->assertJsonPath('data.type', 'kelurahan')
            ->assertJsonPath('data.district.regency.name', 'Kota Administrasi Jakarta Pusat')
            ->assertJsonPath('data.district.regency.province.code', '31');
    }

    public function test_searches_villages_by_name_and_postal_code(): void
    {
        $this->getJson('/api/villages?q=gambir')
            ->assertOk()
            ->assertJsonFragment(['code' => '31.71.01.1001']);

        $this->getJson('/api/villages?postal_code=10110')
            ->assertOk()
            ->assertJsonPath('data.0.code', '31.71.01.1001');

        $this->getJson('/api/villages')->assertUnprocessable();
    }

    public function test_unknown_code_returns_not_found(): void
    {
        $this->getJson('/api/provinces/99')->assertNotFound();
        $this->getJson('/api/villages/31.71.01.9999')->assertNotFound();
    }

    public function test_home_page_shows_counts(): void
    {
        $this->get('/')->assertOk()->assertSee('83.762');
    }
}
