<?php

namespace App\Domain\Travel;

use InvalidArgumentException;

final readonly class RouteMetrics
{
    public function __construct(
        public float $distanceKm,
        public int $durationMinutes,
        public int $tollYen,
    ) {
        if ($distanceKm < 0) {
            throw new InvalidArgumentException('Distance cannot be negative.');
        }

        if ($durationMinutes < 0) {
            throw new InvalidArgumentException('Duration cannot be negative.');
        }

        if ($tollYen < 0) {
            throw new InvalidArgumentException('Toll cannot be negative.');
        }
    }
}
