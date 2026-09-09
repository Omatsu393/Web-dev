<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompareTravelCostsRequest;
use App\Models\VehicleMake;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TravelCostComparisonController extends Controller
{
    public function create(): View
    {
        $vehicleCatalog = VehicleMake::query()
            ->with([
                'models' => fn ($query) => $query
                    ->orderBy('name')
                    ->with([
                        'variants' => fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('name'),
                    ]),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (VehicleMake $make): array => [
                'id' => $make->id,
                'name' => $make->name,
                'models' => $make->models
                    ->filter(fn ($model): bool => $model->variants->isNotEmpty())
                    ->map(fn ($model): array => [
                        'id' => $model->id,
                        'name' => $model->name,
                        'variants' => $model->variants->map(fn ($variant): array => [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'drive_system' => $variant->drive_system,
                            'fuel_efficiency' => $variant->fuel_efficiency,
                            'fuel_type' => $variant->fuel_type->value,
                            'fuel_type_label' => $variant->fuel_type->label(),
                        ])->values(),
                    ])->values(),
            ])
            ->filter(fn (array $make): bool => $make['models']->isNotEmpty())
            ->values();

        return view('comparisons.create', compact('vehicleCatalog'));
    }

    public function store(CompareTravelCostsRequest $request): RedirectResponse
    {
        return to_route('comparisons.create')
            ->withInput($request->validated())
            ->with('status', '入力内容を確認しました。移動費の計算機能は次のステップで追加します。');
    }
}
