<?php

namespace App\Enums;

enum FuelType: string
{
    case Regular = 'regular';
    case Premium = 'premium';
    case Diesel = 'diesel';
    case Electricity = 'electricity';
    case Lpg = 'lpg';
    case Hydrogen = 'hydrogen';

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'レギュラー',
            self::Premium => 'ハイオク',
            self::Diesel => '軽油',
            self::Electricity => '電気',
            self::Lpg => 'LPG',
            self::Hydrogen => '水素',
        };
    }
}
