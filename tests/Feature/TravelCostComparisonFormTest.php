<?php

namespace Tests\Feature;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use Database\Seeders\VehicleCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelCostComparisonFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(VehicleCatalogSeeder::class);
    }

    public function test_comparison_form_is_displayed(): void
    {
        $response = $this->get(route('comparisons.create'));

        $response
            ->assertOk()
            ->assertSee('高速道路 vs 下道')
            ->assertSee('出発地')
            ->assertSee('現在地を使用')
            ->assertSee('origin_latitude', false)
            ->assertSee('origin_longitude', false)
            ->assertSee('目的地')
            ->assertSee('メーカー')
            ->assertSee('車種')
            ->assertSee('グレード・駆動方式')
            ->assertSee('車名を入力して検索')
            ->assertSee('例：プリウス 2WD')
            ->assertSee('vehicle-search-options', false)
            ->assertSee('トヨタ プリウス 2.0L HEV 2WD（2WD）')
            ->assertSee('トヨタ')
            ->assertSee('vehicle-catalog-data')
            ->assertSee('燃費')
            ->assertSee('燃料種別')
            ->assertSee('燃料単価')
            ->assertSee('乗車人数')
            ->assertSee('有料道路の利用条件');
    }

    public function test_valid_comparison_conditions_are_accepted(): void
    {
        [$make, $model, $variant] = $this->priusSelection();

        $response = $this->post(route('comparisons.store'), [
            'origin' => '東京駅',
            'destination' => '名古屋駅',
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_variant_id' => $variant->id,
            'fuel_efficiency' => 15.5,
            'fuel_type' => 'premium',
            'fuel_price' => 175,
            'passenger_count' => 3,
            'toll_preference' => 'compare',
        ]);

        $response
            ->assertRedirect(route('comparisons.create'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');
    }

    public function test_required_fields_show_japanese_validation_messages(): void
    {
        $response = $this
            ->from(route('comparisons.create'))
            ->post(route('comparisons.store'), []);

        $response
            ->assertRedirect(route('comparisons.create'))
            ->assertSessionHasErrors([
                'origin' => '出発地を入力してください。',
                'destination' => '目的地を入力してください。',
                'vehicle_make_id' => 'メーカーを選択してください。',
                'vehicle_model_id' => '車種を選択してください。',
                'vehicle_variant_id' => 'グレード・駆動方式を選択してください。',
                'fuel_efficiency' => '車の燃費を入力してください。',
                'fuel_type' => '燃料種別を選択してください。',
                'fuel_price' => '燃料単価を入力してください。',
                'passenger_count' => '乗車人数を入力してください。',
                'toll_preference' => '有料道路の利用条件を選択してください。',
            ]);
    }

    public function test_numeric_fields_reject_out_of_range_values(): void
    {
        [$make, $model, $variant] = $this->priusSelection();

        $response = $this->post(route('comparisons.store'), [
            'origin' => '東京駅',
            'destination' => '名古屋駅',
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_variant_id' => $variant->id,
            'fuel_efficiency' => 0,
            'fuel_price' => 1001,
            'passenger_count' => 21,
            'toll_preference' => 'unknown',
        ]);

        $response->assertSessionHasErrors([
            'fuel_efficiency' => '車の燃費は1km/L以上で入力してください。',
            'fuel_price' => '燃料単価は1,000円/L以下で入力してください。',
            'passenger_count' => '乗車人数は1〜20人で入力してください。',
            'toll_preference' => '有料道路の利用条件を正しく選択してください。',
        ]);
    }

    public function test_vehicle_selection_must_use_a_consistent_catalog_hierarchy(): void
    {
        $priusVariant = VehicleVariant::query()->where('name', '2.0L HEV 2WD')->firstOrFail();
        $mazda = VehicleMake::query()->where('slug', 'mazda')->firstOrFail();

        $response = $this->post(route('comparisons.store'), [
            'origin' => '東京駅',
            'destination' => '名古屋駅',
            'vehicle_make_id' => $mazda->id,
            'vehicle_model_id' => $priusVariant->vehicleModel->id,
            'vehicle_variant_id' => $priusVariant->id,
            'fuel_efficiency' => 28.6,
            'fuel_price' => 175,
            'passenger_count' => 1,
            'toll_preference' => 'compare',
        ]);

        $response->assertSessionHasErrors([
            'vehicle_variant_id' => 'メーカー、車種、グレードの組み合わせを正しく選択してください。',
        ]);
    }

    public function test_current_location_coordinates_are_accepted(): void
    {
        [$make, $model, $variant] = $this->priusSelection();

        $response = $this->post(route('comparisons.store'), [
            'origin' => '現在地（35.681236, 139.767125）',
            'origin_latitude' => 35.681236,
            'origin_longitude' => 139.767125,
            'destination' => '名古屋駅',
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_variant_id' => $variant->id,
            'fuel_efficiency' => 28.6,
            'fuel_price' => 175,
            'passenger_count' => 4,
            'toll_preference' => 'compare',
        ]);

        $response
            ->assertRedirect(route('comparisons.create'))
            ->assertSessionHasNoErrors();
    }

    public function test_current_location_requires_a_valid_coordinate_pair(): void
    {
        [$make, $model, $variant] = $this->priusSelection();

        $response = $this->post(route('comparisons.store'), [
            'origin' => '現在地',
            'origin_latitude' => 91,
            'destination' => '名古屋駅',
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_variant_id' => $variant->id,
            'fuel_efficiency' => 28.6,
            'fuel_price' => 175,
            'passenger_count' => 2,
            'toll_preference' => 'compare',
        ]);

        $response->assertSessionHasErrors([
            'origin_latitude' => '現在地の緯度を正しく取得できませんでした。',
            'origin_longitude' => '現在地の緯度と経度を両方取得してください。',
        ]);
    }

    private function priusSelection(): array
    {
        $variant = VehicleVariant::query()->where('name', '2.0L HEV 2WD')->firstOrFail();
        $model = VehicleModel::query()->findOrFail($variant->vehicle_model_id);
        $make = VehicleMake::query()->findOrFail($model->vehicle_make_id);

        return [$make, $model, $variant];
    }
}
