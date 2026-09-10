<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="高速道路と下道の移動費比較結果です。">
    <title>移動費比較結果 | omatsu393.com</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    @php
        $formatDuration = static function (int $minutes): string {
            if ($minutes < 60) {
                return $minutes.'分';
            }

            return intdiv($minutes, 60).'時間'.($minutes % 60).'分';
        };
        $formatMoneyDifference = static fn (int $yen): string => ($yen > 0 ? '+' : '').number_format($yen).'円';
    @endphp

    <main class="mx-auto w-full max-w-6xl px-5 py-10 sm:px-8 lg:py-16">
        <header class="mb-9">
            <p class="text-sm font-semibold tracking-[0.24em] text-emerald-400">OMATSU393.COM</p>
            <h1 class="mt-4 text-4xl font-bold sm:text-5xl">高速道路 vs 下道 <span class="text-emerald-300">比較結果</span></h1>
            <p class="mt-4 text-sm leading-6 text-slate-300 sm:text-base">
                {{ $input['origin'] }} <span class="mx-2 text-slate-600">→</span> {{ $input['destination'] }}
            </p>
            <p class="mt-1 text-xs leading-5 text-slate-500">
                {{ $vehicleVariant->vehicleModel->make->name }} {{ $vehicleVariant->vehicleModel->name }} {{ $vehicleVariant->name }} / 燃費 {{ number_format((float) $input['fuel_efficiency'], 1) }}km/L / {{ $input['passenger_count'] }}人
            </p>
        </header>

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                <p class="text-xs font-semibold text-emerald-200">短縮できる時間</p>
                <p class="mt-2 text-2xl font-bold">{{ $formatDuration($comparison->timeSavedMinutes) }}</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold text-slate-300">高速を使うことで増える金額</p>
                <p class="mt-2 text-2xl font-bold {{ $comparison->additionalCostYen > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                    {{ $formatMoneyDifference($comparison->additionalCostYen) }}
                </p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold text-slate-300">1時間短縮あたりの追加費用</p>
                @if ($comparison->additionalCostPerHourSavedYen === null)
                    <p class="mt-2 text-base font-bold text-slate-300">短縮時間がないため計算不可</p>
                @else
                    <p class="mt-2 text-2xl font-bold">{{ $formatMoneyDifference($comparison->additionalCostPerHourSavedYen) }}<span class="ml-1 text-sm font-normal text-slate-400">/時間</span></p>
                @endif
            </div>
        </section>

        <section class="mt-8 overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-2xl">
            <div class="grid grid-cols-[minmax(7.5rem,1fr)_minmax(6.5rem,1fr)_minmax(6.5rem,1fr)] border-b border-white/10 bg-slate-900/80 px-4 py-4 text-sm font-bold sm:px-7">
                <span>比較項目</span>
                <span class="text-right text-emerald-300">高速ルート</span>
                <span class="text-right text-sky-300">下道ルート</span>
            </div>

            @foreach ([
                ['距離', number_format($comparison->highway->route->distanceKm, 1).'km', number_format($comparison->localRoad->route->distanceKm, 1).'km'],
                ['所要時間', $formatDuration($comparison->highway->route->durationMinutes), $formatDuration($comparison->localRoad->route->durationMinutes)],
                ['使用燃料量', number_format($comparison->highway->fuelUsedLiters, 2).'L', number_format($comparison->localRoad->fuelUsedLiters, 2).'L'],
                ['燃料代', number_format($comparison->highway->fuelCostYen).'円', number_format($comparison->localRoad->fuelCostYen).'円'],
                ['高速料金', number_format($comparison->highway->route->tollYen).'円', number_format($comparison->localRoad->route->tollYen).'円'],
                ['合計金額', number_format($comparison->highway->totalCostYen).'円', number_format($comparison->localRoad->totalCostYen).'円'],
                ['1人あたり', number_format($comparison->highway->costPerPersonYen).'円', number_format($comparison->localRoad->costPerPersonYen).'円'],
            ] as [$label, $highwayValue, $localRoadValue])
                <div class="grid grid-cols-[minmax(7.5rem,1fr)_minmax(6.5rem,1fr)_minmax(6.5rem,1fr)] items-center border-b border-white/5 px-4 py-4 text-sm last:border-b-0 sm:px-7 sm:text-base">
                    <span class="text-slate-400">{{ $label }}</span>
                    <span class="text-right font-semibold text-white">{{ $highwayValue }}</span>
                    <span class="text-right font-semibold text-white">{{ $localRoadValue }}</span>
                </div>
            @endforeach
        </section>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('comparisons.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-400 px-6 py-4 font-bold text-slate-950 transition hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-400/30">
                条件を変えて再計算
            </a>
            <p class="self-center text-xs leading-5 text-slate-500">所要時間と通行料金は経路サービスの推定値です。実際の交通状況・料金をご確認ください。</p>
        </div>
    </main>
</body>
</html>
