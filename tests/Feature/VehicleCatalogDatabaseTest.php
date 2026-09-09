<?php

namespace Tests\Feature;

use App\Enums\FuelType;
use App\Models\VehicleMake;
use App\Models\VehicleVariant;
use Database\Seeders\VehicleCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCatalogDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_catalog_seeder_creates_relational_catalog_data(): void
    {
        $this->seed(VehicleCatalogSeeder::class);

        $prius = VehicleVariant::query()
            ->where('source_record_id', 'sample-toyota-prius-20-hev-2wd-2023')
            ->firstOrFail();

        $this->assertSame('プリウス', $prius->vehicleModel->name);
        $this->assertSame('トヨタ', $prius->vehicleModel->make->name);
        $this->assertSame(FuelType::Regular, $prius->fuel_type);
        $this->assertSame('28.60', $prius->fuel_efficiency);
        $this->assertSame('WLTC', $prius->fuel_efficiency_standard);
        $this->assertSame('sample', $prius->source_name);
    }

    public function test_vehicle_catalog_seeder_can_be_run_more_than_once(): void
    {
        $this->seed(VehicleCatalogSeeder::class);
        $this->seed(VehicleCatalogSeeder::class);

        $this->assertSame(3, VehicleMake::query()->count());
        $this->assertSame(6, VehicleVariant::query()->count());
    }
}
