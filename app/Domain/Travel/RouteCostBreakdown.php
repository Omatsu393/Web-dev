<?php

namespace App\Domain\Travel;

final readonly class RouteCostBreakdown
{
    public function __construct(
        public RouteMetrics $route,
        public float $fuelUsedLiters,
        public int $fuelCostYen,
        public int $totalCostYen,
        public int $costPerPersonYen,
    ) {}
}
