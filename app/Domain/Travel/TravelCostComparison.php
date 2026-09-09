<?php

namespace App\Domain\Travel;

final readonly class TravelCostComparison
{
    public function __construct(
        public RouteCostBreakdown $highway,
        public RouteCostBreakdown $localRoad,
        public int $timeSavedMinutes,
        public int $additionalCostYen,
        public ?int $additionalCostPerHourSavedYen,
    ) {}
}
