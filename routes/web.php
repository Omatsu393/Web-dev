<?php

use App\Http\Controllers\TravelCostComparisonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TravelCostComparisonController::class, 'create'])
    ->name('comparisons.create');

Route::post('/compare', [TravelCostComparisonController::class, 'store'])
    ->name('comparisons.store');
