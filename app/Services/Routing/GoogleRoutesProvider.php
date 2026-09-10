<?php

namespace App\Services\Routing;

use App\Domain\Travel\RouteMetrics;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class GoogleRoutesProvider implements RouteComparisonProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $endpoint,
    ) {}

    public function compare(RouteComparisonQuery $query): RoutePair
    {
        if (trim($this->apiKey) === '') {
            throw new RouteProviderException('経路検索APIが設定されていません。');
        }

        return new RoutePair(
            highway: $this->computeRoute($query, avoidTollsAndHighways: false),
            localRoad: $this->computeRoute($query, avoidTollsAndHighways: true),
        );
    }

    private function computeRoute(RouteComparisonQuery $query, bool $avoidTollsAndHighways): RouteMetrics
    {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.travelAdvisory.tollInfo.estimatedPrice',
                ])
                ->timeout(15)
                ->post($this->endpoint, $this->payload($query, $avoidTollsAndHighways))
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new RouteProviderException('経路情報を取得できませんでした。時間をおいて再度お試しください。', previous: $exception);
        }

        $route = $response->json('routes.0');

        if (! is_array($route)) {
            throw new RouteProviderException('指定した出発地と目的地の経路が見つかりませんでした。');
        }

        return new RouteMetrics(
            distanceKm: $this->distanceKm($route),
            durationMinutes: $this->durationMinutes($route),
            tollYen: $this->tollYen($route),
        );
    }

    private function payload(RouteComparisonQuery $query, bool $avoidTollsAndHighways): array
    {
        return [
            'origin' => $query->hasOriginCoordinates()
                ? [
                    'location' => [
                        'latLng' => [
                            'latitude' => $query->originLatitude,
                            'longitude' => $query->originLongitude,
                        ],
                    ],
                ]
                : ['address' => $query->origin],
            'destination' => ['address' => $query->destination],
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'languageCode' => 'ja',
            'units' => 'METRIC',
            'routeModifiers' => [
                'avoidTolls' => $avoidTollsAndHighways,
                'avoidHighways' => $avoidTollsAndHighways,
                'avoidFerries' => true,
                'tollPasses' => $avoidTollsAndHighways ? [] : ['JP_ETC'],
            ],
            'extraComputations' => ['TOLLS'],
        ];
    }

    private function distanceKm(array $route): float
    {
        $distanceMeters = $route['distanceMeters'] ?? null;

        if (! is_numeric($distanceMeters) || $distanceMeters < 0) {
            throw new RouteProviderException('経路の距離情報を取得できませんでした。');
        }

        return round((float) $distanceMeters / 1000, 2);
    }

    private function durationMinutes(array $route): int
    {
        $duration = $route['duration'] ?? null;

        if (! is_string($duration) || preg_match('/^(?<seconds>\d+(?:\.\d+)?)s$/', $duration, $matches) !== 1) {
            throw new RouteProviderException('経路の所要時間を取得できませんでした。');
        }

        return (int) ceil((float) $matches['seconds'] / 60);
    }

    private function tollYen(array $route): int
    {
        $tollInfo = $route['travelAdvisory']['tollInfo'] ?? null;

        if ($tollInfo === null) {
            return 0;
        }

        $prices = $tollInfo['estimatedPrice'] ?? [];
        $price = collect($prices)->firstWhere('currencyCode', 'JPY');

        if (! is_array($price)) {
            throw new RouteProviderException('この経路の通行料金を取得できませんでした。');
        }

        $units = $price['units'] ?? 0;
        $nanos = $price['nanos'] ?? 0;

        if (! is_numeric($units) || ! is_numeric($nanos)) {
            throw new RouteProviderException('この経路の通行料金を取得できませんでした。');
        }

        return (int) round((float) $units + ((float) $nanos / 1_000_000_000));
    }
}
