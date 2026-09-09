<?php

namespace Tests\Feature;

use Tests\TestCase;

class TravelCostComparisonFormTest extends TestCase
{
    public function test_comparison_form_is_displayed(): void
    {
        $response = $this->get(route('comparisons.create'));

        $response
            ->assertOk()
            ->assertSee('高速道路 vs 下道')
            ->assertSee('出発地')
            ->assertSee('目的地')
            ->assertSee('車の燃費')
            ->assertSee('燃料単価')
            ->assertSee('有料道路の利用条件');
    }

    public function test_valid_comparison_conditions_are_accepted(): void
    {
        $response = $this->post(route('comparisons.store'), [
            'origin' => '東京駅',
            'destination' => '名古屋駅',
            'fuel_efficiency' => 15.5,
            'fuel_price' => 175,
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
                'fuel_efficiency' => '車の燃費を入力してください。',
                'fuel_price' => '燃料単価を入力してください。',
                'toll_preference' => '有料道路の利用条件を選択してください。',
            ]);
    }

    public function test_numeric_fields_reject_out_of_range_values(): void
    {
        $response = $this->post(route('comparisons.store'), [
            'origin' => '東京駅',
            'destination' => '名古屋駅',
            'fuel_efficiency' => 0,
            'fuel_price' => 1001,
            'toll_preference' => 'unknown',
        ]);

        $response->assertSessionHasErrors([
            'fuel_efficiency' => '車の燃費は1km/L以上で入力してください。',
            'fuel_price' => '燃料単価は1,000円/L以下で入力してください。',
            'toll_preference' => '有料道路の利用条件を正しく選択してください。',
        ]);
    }
}
