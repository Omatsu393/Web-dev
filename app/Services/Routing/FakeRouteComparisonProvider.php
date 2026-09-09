<?php

namespace App\Services\Routing;

final class FakeRouteComparisonProvider implements RouteComparisonProvider
{
    public array $queries = [];

    public function __construct(private readonly RoutePair $routes) {}

    public function compare(RouteComparisonQuery $query): RoutePair
    {
        $this->queries[] = $query;

        return $this->routes;
    }
}
