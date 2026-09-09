<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'destination' => ['required', 'string', 'max:120'],
            'fuel_efficiency' => ['required', 'numeric', 'min:1', 'max:100'],
            'fuel_price' => ['required', 'integer', 'min:1', 'max:1000'],
            'toll_preference' => ['required', 'in:compare,prefer_toll,avoid_toll'],
        ];
    }

    public function messages(): array
    {
        return [
            'origin.required' => '出発地を入力してください。',
            'origin.string' => '出発地は文字で入力してください。',
            'origin.max' => '出発地は120文字以内で入力してください。',
            'destination.required' => '目的地を入力してください。',
            'destination.string' => '目的地は文字で入力してください。',
            'destination.max' => '目的地は120文字以内で入力してください。',
            'fuel_efficiency.required' => '車の燃費を入力してください。',
            'fuel_efficiency.numeric' => '車の燃費は数値で入力してください。',
            'fuel_efficiency.min' => '車の燃費は1km/L以上で入力してください。',
            'fuel_efficiency.max' => '車の燃費は100km/L以下で入力してください。',
            'fuel_price.required' => '燃料単価を入力してください。',
            'fuel_price.integer' => '燃料単価は整数で入力してください。',
            'fuel_price.min' => '燃料単価は1円/L以上で入力してください。',
            'fuel_price.max' => '燃料単価は1,000円/L以下で入力してください。',
            'toll_preference.required' => '有料道路の利用条件を選択してください。',
            'toll_preference.in' => '有料道路の利用条件を正しく選択してください。',
        ];
    }
}
