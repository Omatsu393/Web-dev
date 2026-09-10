<?php

namespace App\Http\Controllers;

use App\Domain\Travel\TravelCostCalculator;
use App\Http\Requests\CompareTravelCostsRequest;
use App\Models\VehicleMake;
use App\Models\VehicleVariant;
use App\Services\Routing\RouteComparisonProvider;
use App\Services\Routing\RouteComparisonQuery;
use App\Services\Routing\RouteProviderException;
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

    public function store(
        CompareTravelCostsRequest $request,
        RouteComparisonProvider $routeProvider,
        TravelCostCalculator $calculator,
    ): View|RedirectResponse {
        $input = $request->validated();

        try {
            $routes = $routeProvider->compare(new RouteComparisonQuery(
                origin: $input['origin'],
                destination: $input['destination'],
                originLatitude: isset($input['origin_latitude']) ? (float) $input['origin_latitude'] : null,
                originLongitude: isset($input['origin_longitude']) ? (float) $input['origin_longitude'] : null,
            ));
        } catch (RouteProviderException $exception) {
            return to_route('comparisons.create')
                ->withInput($input)
                ->withErrors(['route' => $exception->getMessage()]);
        }

        $comparison = $calculator->compare(
            highway: $routes->highway,
            localRoad: $routes->localRoad,
            fuelEfficiency: (float) $input['fuel_efficiency'],
            fuelPriceYen: (int) $input['fuel_price'],
            passengerCount: (int) $input['passenger_count'],
        );
        $vehicleVariant = VehicleVariant::query()
            ->with('vehicleModel.make')
            ->findOrFail($input['vehicle_variant_id']);

        return view('comparisons.result', compact('comparison', 'input', 'vehicleVariant'));
    }
}
