<?php

namespace App\Http\Requests;

use App\Enums\FuelType;
use App\Models\VehicleVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CompareTravelCostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin' => ['required', 'string', 'max:120'],
            'origin_latitude' => ['nullable', 'required_with:origin_longitude', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['nullable', 'required_with:origin_latitude', 'numeric', 'between:-180,180'],
            'destination' => ['required', 'string', 'max:120'],
            'vehicle_make_id' => ['required', 'integer', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => ['required', 'integer', 'exists:vehicle_models,id'],
            'vehicle_variant_id' => ['required', 'integer', 'exists:vehicle_variants,id'],
            'fuel_efficiency' => ['required', 'numeric', 'min:1', 'max:100'],
            'fuel_type' => ['required', Rule::enum(FuelType::class)],
            'fuel_price' => ['required', 'integer', 'min:1', 'max:1000'],
            'toll_preference' => ['required', 'in:compare,prefer_toll,avoid_toll'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['vehicle_make_id', 'vehicle_model_id', 'vehicle_variant_id'])) {
                    return;
                }

                $variant = VehicleVariant::query()
                    ->with('vehicleModel')
                    ->whereKey($this->integer('vehicle_variant_id'))
                    ->where('is_active', true)
                    ->first();

                if (! $variant
                    || $variant->vehicle_model_id !== $this->integer('vehicle_model_id')
                    || $variant->vehicleModel->vehicle_make_id !== $this->integer('vehicle_make_id')) {
                    $validator->errors()->add('vehicle_variant_id', 'メーカー、車種、グレードの組み合わせを正しく選択してください。');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $variant = VehicleVariant::query()
            ->whereKey($this->integer('vehicle_variant_id'))
            ->where('is_active', true)
            ->first();

        if ($variant) {
            $this->merge(['fuel_type' => $variant->fuel_type->value]);
        }
    }

    public function messages(): array
    {
        return [
            'origin.required' => '出発地を入力してください。',
            'origin.string' => '出発地は文字で入力してください。',
            'origin.max' => '出発地は120文字以内で入力してください。',
            'origin_latitude.required_with' => '現在地の緯度と経度を両方取得してください。',
            'origin_latitude.numeric' => '現在地の緯度を正しく取得できませんでした。',
            'origin_latitude.between' => '現在地の緯度を正しく取得できませんでした。',
            'origin_longitude.required_with' => '現在地の緯度と経度を両方取得してください。',
            'origin_longitude.numeric' => '現在地の経度を正しく取得できませんでした。',
            'origin_longitude.between' => '現在地の経度を正しく取得できませんでした。',
            'destination.required' => '目的地を入力してください。',
            'destination.string' => '目的地は文字で入力してください。',
            'destination.max' => '目的地は120文字以内で入力してください。',
            'vehicle_make_id.required' => 'メーカーを選択してください。',
            'vehicle_make_id.exists' => 'メーカーを正しく選択してください。',
            'vehicle_model_id.required' => '車種を選択してください。',
            'vehicle_model_id.exists' => '車種を正しく選択してください。',
            'vehicle_variant_id.required' => 'グレード・駆動方式を選択してください。',
            'vehicle_variant_id.exists' => 'グレード・駆動方式を正しく選択してください。',
            'fuel_efficiency.required' => '車の燃費を入力してください。',
            'fuel_efficiency.numeric' => '車の燃費は数値で入力してください。',
            'fuel_efficiency.min' => '車の燃費は1km/L以上で入力してください。',
            'fuel_efficiency.max' => '車の燃費は100km/L以下で入力してください。',
            'fuel_type.required' => '燃料種別を選択してください。',
            'fuel_type.enum' => '燃料種別を正しく選択してください。',
            'fuel_price.required' => '燃料単価を入力してください。',
            'fuel_price.integer' => '燃料単価は整数で入力してください。',
            'fuel_price.min' => '燃料単価は1円/L以上で入力してください。',
            'fuel_price.max' => '燃料単価は1,000円/L以下で入力してください。',
            'toll_preference.required' => '有料道路の利用条件を選択してください。',
            'toll_preference.in' => '有料道路の利用条件を正しく選択してください。',
        ];
    }
}
