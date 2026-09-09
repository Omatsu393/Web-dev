<?php

namespace App\Services\Routing;

use App\Domain\Travel\RouteMetrics;

final readonly class RoutePair
{
    public function __construct(
        public RouteMetrics $highway,
        public RouteMetrics $localRoad,
    ) {}
}
