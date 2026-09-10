<?php

namespace Tests\Feature;

use App\Services\Routing\GoogleRoutesProvider;
use App\Services\Routing\RouteComparisonQuery;
use App\Services\Routing\RouteProviderException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleRoutesProviderTest extends TestCase
{
    private const ENDPOINT = 'https://routes.googleapis.test/directions/v2:computeRoutes';

    public function test_it_fetches_highway_and_local_routes_without_exposing_the_key_in_the_payload(): void
    {
        Http::fakeSequence()
            ->push([
                'routes' => [[
                    'distanceMeters' => 300500,
                    'duration' => '14400s',
                    'travelAdvisory' => [
                        'tollInfo' => [
                            'estimatedPrice' => [[
                                'currencyCode' => 'JPY',
                                'units' => '5120',
                            ]],
                        ],
                    ],
                ]],
            ])
            ->push([
                'routes' => [[
                    'distanceMeters' => 330250,
                    'duration' => '25200s',
                ]],
            ]);

        $routes = (new GoogleRoutesProvider('test-api-key', self::ENDPOINT))->compare(
            new RouteComparisonQuery(
                origin: '現在地',
                destination: '名古屋駅',
                originLatitude: 35.681236,
                originLongitude: 139.767125,
            ),
        );

        $this->assertSame(300.5, $routes->highway->distanceKm);
        $this->assertSame(240, $routes->highway->durationMinutes);
        $this->assertSame(5120, $routes->highway->tollYen);
        $this->assertSame(330.25, $routes->localRoad->distanceKm);
        $this->assertSame(420, $routes->localRoad->durationMinutes);
        $this->assertSame(0, $routes->localRoad->tollYen);

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-Goog-Api-Key', 'test-api-key')
            && ! str_contains($request->body(), 'test-api-key'));

        $requests = Http::recorded();
        $this->assertFalse($requests[0][0]['routeModifiers']['avoidTolls']);
        $this->assertSame(['JP_ETC'], $requests[0][0]['routeModifiers']['tollPasses']);
        $this->assertTrue($requests[1][0]['routeModifiers']['avoidTolls']);
        $this->assertTrue($requests[1][0]['routeModifiers']['avoidHighways']);
        $this->assertSame(35.681236, $requests[0][0]['origin']['location']['latLng']['latitude']);
    }

    public function test_it_uses_an_address_when_current_location_is_not_available(): void
    {
        Http::fakeSequence()
            ->push(['routes' => [['distanceMeters' => 1000, 'duration' => '60s']]])
            ->push(['routes' => [['distanceMeters' => 1200, 'duration' => '90s']]]);

        (new GoogleRoutesProvider('test-api-key', self::ENDPOINT))->compare(
            new RouteComparisonQuery(origin: '東京駅', destination: '名古屋駅'),
        );

        $requests = Http::recorded();
        $this->assertSame('東京駅', $requests[0][0]['origin']['address']);
    }

    public function test_it_requires_an_api_key(): void
    {
        Http::preventStrayRequests();
        $this->expectException(RouteProviderException::class);
        $this->expectExceptionMessage('経路検索APIが設定されていません。');

        (new GoogleRoutesProvider('', self::ENDPOINT))->compare(
            new RouteComparisonQuery(origin: '東京駅', destination: '名古屋駅'),
        );
    }

    public function test_it_does_not_treat_an_unknown_toll_as_zero_yen(): void
    {
        Http::fake([
            self::ENDPOINT => Http::response([
                'routes' => [[
                    'distanceMeters' => 1000,
                    'duration' => '60s',
                    'travelAdvisory' => ['tollInfo' => []],
                ]],
            ]),
        ]);
        $this->expectException(RouteProviderException::class);
        $this->expectExceptionMessage('この経路の通行料金を取得できませんでした。');

        (new GoogleRoutesProvider('test-api-key', self::ENDPOINT))->compare(
            new RouteComparisonQuery(origin: '東京駅', destination: '名古屋駅'),
        );
    }
}
