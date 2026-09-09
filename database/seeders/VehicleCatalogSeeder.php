<?php

namespace Database\Seeders;

use App\Enums\FuelType;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $toyota = VehicleMake::query()->updateOrCreate(
            ['slug' => 'toyota'],
            ['name' => 'トヨタ', 'country_code' => 'JP'],
        );

        $prius = $toyota->models()->updateOrCreate(
            ['slug' => 'prius'],
            [
                'name' => 'プリウス',
                'model_code' => '60',
                'production_start_year' => 2023,
                'production_end_year' => null,
            ],
        );

        $this->seedVariants($prius, [
            [
                'name' => '2.0L HEV 2WD',
                'drive_system' => '2WD',
                'fuel_type' => FuelType::Regular,
                'fuel_efficiency' => 28.6,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2023,
                'source_record_id' => 'sample-toyota-prius-20-hev-2wd-2023',
            ],
            [
                'name' => '2.0L HEV E-Four',
                'drive_system' => 'E-Four',
                'fuel_type' => FuelType::Regular,
                'fuel_efficiency' => 26.7,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2023,
                'source_record_id' => 'sample-toyota-prius-20-hev-efour-2023',
            ],
            [
                'name' => '1.8L HEV 2WD',
                'drive_system' => '2WD',
                'fuel_type' => FuelType::Regular,
                'fuel_efficiency' => 32.6,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2023,
                'source_record_id' => 'sample-toyota-prius-18-hev-2wd-2023',
            ],
        ]);

        $mazda = VehicleMake::query()->updateOrCreate(
            ['slug' => 'mazda'],
            ['name' => 'マツダ', 'country_code' => 'JP'],
        );

        $cx5 = $mazda->models()->updateOrCreate(
            ['slug' => 'cx-5'],
            [
                'name' => 'CX-5',
                'model_code' => 'KF',
                'production_start_year' => 2017,
                'production_end_year' => null,
            ],
        );

        $this->seedVariants($cx5, [
            [
                'name' => 'XD 2WD',
                'drive_system' => '2WD',
                'fuel_type' => FuelType::Diesel,
                'fuel_efficiency' => 17.4,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2024,
                'source_record_id' => 'sample-mazda-cx5-xd-2wd-2024',
            ],
            [
                'name' => '20S 4WD',
                'drive_system' => '4WD',
                'fuel_type' => FuelType::Regular,
                'fuel_efficiency' => 13.0,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2024,
                'source_record_id' => 'sample-mazda-cx5-20s-4wd-2024',
            ],
        ]);

        $lexus = VehicleMake::query()->updateOrCreate(
            ['slug' => 'lexus'],
            ['name' => 'レクサス', 'country_code' => 'JP'],
        );

        $is = $lexus->models()->updateOrCreate(
            ['slug' => 'is'],
            [
                'name' => 'IS',
                'model_code' => 'ASE30',
                'production_start_year' => 2020,
                'production_end_year' => null,
            ],
        );

        $this->seedVariants($is, [
            [
                'name' => 'IS300 2WD',
                'drive_system' => '2WD',
                'fuel_type' => FuelType::Premium,
                'fuel_efficiency' => 12.2,
                'fuel_efficiency_standard' => 'WLTC',
                'model_year' => 2024,
                'source_record_id' => 'sample-lexus-is300-2wd-2024',
            ],
        ]);
    }

    private function seedVariants(VehicleModel $vehicleModel, array $variants): void
    {
        foreach ($variants as $variant) {
            $vehicleModel->variants()->updateOrCreate(
                [
                    'source_name' => 'sample',
                    'source_record_id' => $variant['source_record_id'],
                ],
                [
                    ...$variant,
                    'fuel_efficiency_unit' => 'km/L',
                    'source_updated_at' => '2026-09-09',
                    'is_active' => true,
                ],
            );
        }
    }
}
