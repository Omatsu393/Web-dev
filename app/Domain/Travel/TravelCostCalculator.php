<?php

namespace App\Domain\Travel;

use InvalidArgumentException;

final class TravelCostCalculator
{
    public function compare(
        RouteMetrics $highway,
        RouteMetrics $localRoad,
        float $fuelEfficiency,
        int $fuelPriceYen,
        int $passengerCount,
    ): TravelCostComparison {
        $this->validateInputs($fuelEfficiency, $fuelPriceYen, $passengerCount);

        $highwayCost = $this->calculateRouteCost(
            $highway,
            $fuelEfficiency,
            $fuelPriceYen,
            $passengerCount,
        );
        $localRoadCost = $this->calculateRouteCost(
            $localRoad,
            $fuelEfficiency,
            $fuelPriceYen,
            $passengerCount,
        );
        $timeSavedMinutes = max($localRoad->durationMinutes - $highway->durationMinutes, 0);
        $additionalCostYen = $highwayCost->totalCostYen - $localRoadCost->totalCostYen;
        $additionalCostPerHourSavedYen = $timeSavedMinutes > 0
            ? (int) round($additionalCostYen * 60 / $timeSavedMinutes)
            : null;

        return new TravelCostComparison(
            highway: $highwayCost,
            localRoad: $localRoadCost,
            timeSavedMinutes: $timeSavedMinutes,
            additionalCostYen: $additionalCostYen,
            additionalCostPerHourSavedYen: $additionalCostPerHourSavedYen,
        );
    }

    private function calculateRouteCost(
        RouteMetrics $route,
        float $fuelEfficiency,
        int $fuelPriceYen,
        int $passengerCount,
    ): RouteCostBreakdown {
        $rawFuelUsedLiters = $route->distanceKm / $fuelEfficiency;
        $fuelCostYen = (int) round($rawFuelUsedLiters * $fuelPriceYen);
        $totalCostYen = $fuelCostYen + $route->tollYen;

        return new RouteCostBreakdown(
            route: $route,
            fuelUsedLiters: round($rawFuelUsedLiters, 2),
            fuelCostYen: $fuelCostYen,
            totalCostYen: $totalCostYen,
            costPerPersonYen: (int) ceil($totalCostYen / $passengerCount),
        );
    }

    private function validateInputs(float $fuelEfficiency, int $fuelPriceYen, int $passengerCount): void
    {
        if ($fuelEfficiency <= 0) {
            throw new InvalidArgumentException('Fuel efficiency must be greater than zero.');
        }

        if ($fuelPriceYen < 0) {
            throw new InvalidArgumentException('Fuel price cannot be negative.');
        }

        if ($passengerCount < 1) {
            throw new InvalidArgumentException('Passenger count must be at least one.');
        }
    }
}
