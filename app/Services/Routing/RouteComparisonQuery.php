<?php

namespace App\Services\Routing;

use InvalidArgumentException;

final readonly class RouteComparisonQuery
{
    public function __construct(
        public string $origin,
        public string $destination,
        public ?float $originLatitude = null,
        public ?float $originLongitude = null,
    ) {
        if (trim($origin) === '' || trim($destination) === '') {
            throw new InvalidArgumentException('Origin and destination are required.');
        }

        if (($originLatitude === null) !== ($originLongitude === null)) {
            throw new InvalidArgumentException('Origin coordinates must be provided together.');
        }

        if ($originLatitude !== null && ($originLatitude < -90 || $originLatitude > 90)) {
            throw new InvalidArgumentException('Origin latitude is outside the valid range.');
        }

        if ($originLongitude !== null && ($originLongitude < -180 || $originLongitude > 180)) {
            throw new InvalidArgumentException('Origin longitude is outside the valid range.');
        }
    }

    public function hasOriginCoordinates(): bool
    {
        return $this->originLatitude !== null && $this->originLongitude !== null;
    }
}
