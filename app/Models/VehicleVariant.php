<?php

namespace App\Models;

use App\Enums\FuelType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vehicle_model_id',
    'name',
    'drive_system',
    'fuel_type',
    'fuel_efficiency',
    'fuel_efficiency_unit',
    'fuel_efficiency_standard',
    'model_year',
    'source_name',
    'source_url',
    'source_record_id',
    'source_updated_at',
    'is_active',
])]
class VehicleVariant extends Model
{
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    protected function casts(): array
    {
        return [
            'fuel_type' => FuelType::class,
            'fuel_efficiency' => 'decimal:2',
            'source_updated_at' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
