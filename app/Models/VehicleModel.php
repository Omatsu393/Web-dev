<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vehicle_make_id',
    'name',
    'slug',
    'model_code',
    'production_start_year',
    'production_end_year',
])]
class VehicleModel extends Model
{
    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'vehicle_make_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(VehicleVariant::class);
    }
}
