<?php

namespace Tests\Unit;

use App\Domain\Travel\RouteMetrics;
use App\Domain\Travel\TravelCostCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TravelCostCalculatorTest extends TestCase
{
    public function test_it_calculates_highway_and_local_road_costs(): void
    {
        $comparison = (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 300, durationMinutes: 240, tollYen: 5000),
            localRoad: new RouteMetrics(distanceKm: 330, durationMinutes: 420, tollYen: 0),
            fuelEfficiency: 15,
            fuelPriceYen: 180,
            passengerCount: 3,
        );

        $this->assertSame(20.0, $comparison->highway->fuelUsedLiters);
        $this->assertSame(3600, $comparison->highway->fuelCostYen);
        $this->assertSame(8600, $comparison->highway->totalCostYen);
        $this->assertSame(2867, $comparison->highway->costPerPersonYen);
        $this->assertSame(22.0, $comparison->localRoad->fuelUsedLiters);
        $this->assertSame(3960, $comparison->localRoad->fuelCostYen);
        $this->assertSame(3960, $comparison->localRoad->totalCostYen);
        $this->assertSame(1320, $comparison->localRoad->costPerPersonYen);
        $this->assertSame(180, $comparison->timeSavedMinutes);
        $this->assertSame(4640, $comparison->additionalCostYen);
        $this->assertSame(1547, $comparison->additionalCostPerHourSavedYen);
    }

    public function test_it_uses_unrounded_fuel_amount_for_cost_calculation(): void
    {
        $comparison = (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 123.4, durationMinutes: 100, tollYen: 1000),
            localRoad: new RouteMetrics(distanceKm: 123.4, durationMinutes: 120, tollYen: 0),
            fuelEfficiency: 28.6,
            fuelPriceYen: 175,
            passengerCount: 2,
        );

        $this->assertSame(4.31, $comparison->highway->fuelUsedLiters);
        $this->assertSame(755, $comparison->highway->fuelCostYen);
        $this->assertSame(1755, $comparison->highway->totalCostYen);
        $this->assertSame(878, $comparison->highway->costPerPersonYen);
    }

    public function test_cost_per_hour_is_unavailable_when_highway_saves_no_time(): void
    {
        $comparison = (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 10, durationMinutes: 60, tollYen: 500),
            localRoad: new RouteMetrics(distanceKm: 10, durationMinutes: 45, tollYen: 0),
            fuelEfficiency: 10,
            fuelPriceYen: 180,
            passengerCount: 1,
        );

        $this->assertSame(0, $comparison->timeSavedMinutes);
        $this->assertNull($comparison->additionalCostPerHourSavedYen);
    }

    public function test_it_rejects_invalid_calculation_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            localRoad: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            fuelEfficiency: 0,
            fuelPriceYen: 180,
            passengerCount: 1,
        );
    }

    public function test_it_rejects_a_negative_fuel_price(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            localRoad: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            fuelEfficiency: 10,
            fuelPriceYen: -1,
            passengerCount: 1,
        );
    }

    public function test_it_rejects_zero_passengers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TravelCostCalculator)->compare(
            highway: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            localRoad: new RouteMetrics(distanceKm: 10, durationMinutes: 10, tollYen: 0),
            fuelEfficiency: 10,
            fuelPriceYen: 180,
            passengerCount: 0,
        );
    }

    public function test_route_metrics_reject_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RouteMetrics(distanceKm: -1, durationMinutes: 10, tollYen: 0);
    }
}
