<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompareTravelCostsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TravelCostComparisonController extends Controller
{
    public function create(): View
    {
        return view('comparisons.create');
    }

    public function store(CompareTravelCostsRequest $request): RedirectResponse
    {
        return to_route('comparisons.create')
            ->withInput($request->validated())
            ->with('status', '入力内容を確認しました。移動費の計算機能は次のステップで追加します。');
    }
}
