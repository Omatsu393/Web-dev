<?php

namespace App\Services\Routing;

interface RouteComparisonProvider
{
    public function compare(RouteComparisonQuery $query): RoutePair;
}
