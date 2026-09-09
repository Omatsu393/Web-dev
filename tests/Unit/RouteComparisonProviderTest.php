<?php

namespace Tests\Unit;

use App\Domain\Travel\RouteMetrics;
use App\Services\Routing\FakeRouteComparisonProvider;
use App\Services\Routing\RouteComparisonQuery;
use App\Services\Routing\RoutePair;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RouteComparisonProviderTest extends TestCase
{
    public function test_fake_provider_returns_routes_and_records_the_query(): void
    {
        $routes = new RoutePair(
            highway: new RouteMetrics(distanceKm: 300, durationMinutes: 240, tollYen: 5000),
            localRoad: new RouteMetrics(distanceKm: 330, durationMinutes: 420, tollYen: 0),
        );
        $provider = new FakeRouteComparisonProvider($routes);
        $query = new RouteComparisonQuery(
            origin: '現在地',
            destination: '名古屋駅',
            originLatitude: 35.681236,
            originLongitude: 139.767125,
        );

        $this->assertSame($routes, $provider->compare($query));
        $this->assertSame([$query], $provider->queries);
        $this->assertTrue($query->hasOriginCoordinates());
    }

    public function test_query_allows_a_manually_entered_origin(): void
    {
        $query = new RouteComparisonQuery(origin: '東京駅', destination: '名古屋駅');

        $this->assertFalse($query->hasOriginCoordinates());
    }

    public function test_query_rejects_incomplete_origin_coordinates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RouteComparisonQuery(
            origin: '現在地',
            destination: '名古屋駅',
            originLatitude: 35.681236,
        );
    }
}
