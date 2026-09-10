<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="高速道路と下道の移動費を比較するomatsu393.comのWebツールです。">
    <title>高速道路 vs 下道 移動費比較 | omatsu393.com</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto w-full max-w-6xl px-5 py-10 sm:px-8 lg:py-16">
        <header class="mb-10">
            <p class="text-sm font-semibold tracking-[0.24em] text-emerald-400">OMATSU393.COM</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">
                高速道路 vs 下道<br>
                <span class="text-emerald-300">移動費をかんたん比較</span>
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                出発地と目的地、車の条件を入力してください。高速料金と燃料代を比べる準備をします。
            </p>
        </header>

        <section class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur sm:p-9">
                @if (session('status'))
                    <div class="mb-7 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4 text-sm leading-6 text-emerald-200" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-7 rounded-2xl border border-rose-400/30 bg-rose-400/10 px-5 py-4 text-sm text-rose-100" role="alert">
                        <p class="font-semibold">入力内容を確認してください。</p>
                        @error('route')
                            <p class="mt-1 text-rose-200">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-rose-200">{{ $errors->count() }}件の修正が必要です。</p>
                        @enderror
                    </div>
                @endif

                <form method="POST" action="{{ route('comparisons.store') }}" class="space-y-8" data-comparison-form novalidate>
                    @csrf

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label for="origin" class="block text-sm font-semibold text-slate-100">出発地</label>
                                <button id="use-current-location" type="button" class="rounded-full border border-emerald-400/40 bg-emerald-400/10 px-3 py-1.5 text-xs font-semibold text-emerald-200 transition hover:border-emerald-300 hover:bg-emerald-400/20 focus:outline-none focus:ring-4 focus:ring-emerald-400/20 disabled:cursor-not-allowed disabled:opacity-50">
                                    現在地を使用
                                </button>
                            </div>
                            <input
                                id="origin"
                                name="origin"
                                type="text"
                                value="{{ old('origin') }}"
                                placeholder="例：東京駅"
                                autocomplete="street-address"
                                aria-describedby="origin-hint origin-error"
                                @class([
                                    'mt-2 w-full rounded-2xl border bg-slate-900/80 px-4 py-3.5 text-white outline-none transition placeholder:text-slate-500 focus:ring-4',
                                    'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('origin'),
                                    'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('origin'),
                                ])
                            >
                            <input id="origin_latitude" name="origin_latitude" type="hidden" value="{{ old('origin_latitude') }}">
                            <input id="origin_longitude" name="origin_longitude" type="hidden" value="{{ old('origin_longitude') }}">
                            <p id="origin-hint" class="mt-2 text-xs text-slate-400">住所、駅名、施設名など。位置情報を許可しない場合も手入力できます。</p>
                            <p id="current-location-status" class="mt-2 text-xs text-slate-400" role="status" aria-live="polite"></p>
                            @error('origin')
                                <p id="origin-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                            @error('origin_latitude')
                                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                            @error('origin_longitude')
                                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="destination" class="block text-sm font-semibold text-slate-100">目的地</label>
                            <input
                                id="destination"
                                name="destination"
                                type="text"
                                value="{{ old('destination') }}"
                                placeholder="例：名古屋駅"
                                autocomplete="street-address"
                                aria-describedby="destination-hint destination-error"
                                @class([
                                    'mt-2 w-full rounded-2xl border bg-slate-900/80 px-4 py-3.5 text-white outline-none transition placeholder:text-slate-500 focus:ring-4',
                                    'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('destination'),
                                    'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('destination'),
                                ])
                            >
                            <p id="destination-hint" class="mt-2 text-xs text-slate-400">住所、駅名、施設名など</p>
                            @error('destination')
                                <p id="destination-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <fieldset class="rounded-3xl border border-white/10 bg-slate-900/50 p-5 sm:p-6">
                        <legend class="px-2 text-base font-bold text-emerald-300">車種から燃費を設定</legend>

                        <div class="mb-6">
                            <label for="vehicle_search" class="block text-sm font-semibold text-slate-100">車名を入力して検索</label>
                            <input
                                id="vehicle_search"
                                type="search"
                                list="vehicle-search-options"
                                placeholder="例：プリウス 2WD"
                                autocomplete="off"
                                aria-describedby="vehicle-search-hint vehicle-search-status"
                                class="mt-2 w-full rounded-2xl border border-emerald-400/30 bg-slate-950/80 px-4 py-3.5 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10"
                            >
                            <datalist id="vehicle-search-options">
                                @foreach ($vehicleCatalog as $make)
                                    @foreach ($make['models'] as $model)
                                        @foreach ($model['variants'] as $variant)
                                            <option value="{{ $make['name'] }} {{ $model['name'] }} {{ $variant['name'] }}（{{ $variant['drive_system'] }}）"></option>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </datalist>
                            <p id="vehicle-search-hint" class="mt-2 text-xs leading-5 text-slate-400">車名やグレードを入力し、表示された候補を選んでください。</p>
                            <p id="vehicle-search-status" class="mt-1 text-xs text-emerald-300" role="status" aria-live="polite"></p>
                        </div>

                        <div class="mb-5 flex items-center gap-3 text-xs text-slate-500" aria-hidden="true">
                            <span class="h-px flex-1 bg-white/10"></span>
                            <span>または順番に選択</span>
                            <span class="h-px flex-1 bg-white/10"></span>
                        </div>

                        <div class="grid gap-5 lg:grid-cols-3">
                            <div>
                                <label for="vehicle_make_id" class="block text-sm font-semibold text-slate-100">メーカー</label>
                                <select
                                    id="vehicle_make_id"
                                    name="vehicle_make_id"
                                    data-selected="{{ old('vehicle_make_id') }}"
                                    @class([
                                        'mt-2 w-full rounded-2xl border bg-slate-950/80 px-4 py-3.5 text-white outline-none transition focus:ring-4',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('vehicle_make_id'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('vehicle_make_id'),
                                    ])
                                >
                                    <option value="">選択してください</option>
                                    @foreach ($vehicleCatalog as $make)
                                        <option value="{{ $make['id'] }}" @selected((string) old('vehicle_make_id') === (string) $make['id'])>{{ $make['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_make_id')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="vehicle_model_id" class="block text-sm font-semibold text-slate-100">車種</label>
                                <select
                                    id="vehicle_model_id"
                                    name="vehicle_model_id"
                                    data-selected="{{ old('vehicle_model_id') }}"
                                    disabled
                                    @class([
                                        'mt-2 w-full rounded-2xl border bg-slate-950/80 px-4 py-3.5 text-white outline-none transition focus:ring-4 disabled:cursor-not-allowed disabled:opacity-50',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('vehicle_model_id'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('vehicle_model_id'),
                                    ])
                                >
                                    <option value="">メーカーを先に選択</option>
                                </select>
                                @error('vehicle_model_id')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="vehicle_variant_id" class="block text-sm font-semibold text-slate-100">グレード・駆動方式</label>
                                <select
                                    id="vehicle_variant_id"
                                    name="vehicle_variant_id"
                                    data-selected="{{ old('vehicle_variant_id') }}"
                                    disabled
                                    @class([
                                        'mt-2 w-full rounded-2xl border bg-slate-950/80 px-4 py-3.5 text-white outline-none transition focus:ring-4 disabled:cursor-not-allowed disabled:opacity-50',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('vehicle_variant_id'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('vehicle_variant_id'),
                                    ])
                                >
                                    <option value="">車種を先に選択</option>
                                </select>
                                @error('vehicle_variant_id')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="fuel_efficiency" class="block text-sm font-semibold text-slate-100">燃費</label>
                            <div class="relative mt-2">
                                <input
                                    id="fuel_efficiency"
                                    name="fuel_efficiency"
                                    type="number"
                                    inputmode="decimal"
                                    min="1"
                                    max="100"
                                    step="0.1"
                                    value="{{ old('fuel_efficiency', '15.0') }}"
                                    aria-describedby="fuel-efficiency-error"
                                    @class([
                                        'w-full rounded-2xl border bg-slate-900/80 px-4 py-3.5 pr-20 text-white outline-none transition focus:ring-4',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('fuel_efficiency'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('fuel_efficiency'),
                                    ])
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm text-slate-400">km/L</span>
                            </div>
                            <p id="catalog-fuel-efficiency" class="mt-2 text-xs text-slate-400" aria-live="polite">車種を選ぶとカタログ燃費を自動入力します。実燃費に合わせて変更できます。</p>
                            @error('fuel_efficiency')
                                <p id="fuel-efficiency-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="fuel_type_display" class="block text-sm font-semibold text-slate-100">燃料種別</label>
                            <input id="fuel_type" name="fuel_type" type="hidden" value="{{ old('fuel_type') }}">
                            <input
                                id="fuel_type_display"
                                type="text"
                                value=""
                                placeholder="車種から自動設定"
                                readonly
                                class="mt-2 w-full cursor-default rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3.5 text-slate-300 outline-none"
                            >
                            @error('fuel_type')
                                <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="fuel_price" class="block text-sm font-semibold text-slate-100">燃料単価</label>
                            <div class="relative mt-2">
                                <input
                                    id="fuel_price"
                                    name="fuel_price"
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    max="1000"
                                    step="1"
                                    value="{{ old('fuel_price', '175') }}"
                                    aria-describedby="fuel-price-error"
                                    @class([
                                        'w-full rounded-2xl border bg-slate-900/80 px-4 py-3.5 pr-16 text-white outline-none transition focus:ring-4',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('fuel_price'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('fuel_price'),
                                    ])
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm text-slate-400">円/L</span>
                            </div>
                            @error('fuel_price')
                                <p id="fuel-price-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="passenger_count" class="block text-sm font-semibold text-slate-100">乗車人数</label>
                            <div class="relative mt-2">
                                <input
                                    id="passenger_count"
                                    name="passenger_count"
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    max="20"
                                    step="1"
                                    value="{{ old('passenger_count', '1') }}"
                                    aria-describedby="passenger-count-hint passenger-count-error"
                                    @class([
                                        'w-full rounded-2xl border bg-slate-900/80 px-4 py-3.5 pr-12 text-white outline-none transition focus:ring-4',
                                        'border-rose-400 focus:border-rose-300 focus:ring-rose-400/10' => $errors->has('passenger_count'),
                                        'border-white/10 focus:border-emerald-400 focus:ring-emerald-400/10' => ! $errors->has('passenger_count'),
                                    ])
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm text-slate-400">人</span>
                            </div>
                            <p id="passenger-count-hint" class="mt-2 text-xs text-slate-400">1人あたりの金額計算に使用します。</p>
                            @error('passenger_count')
                                <p id="passenger-count-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <fieldset>
                        <legend class="text-sm font-semibold text-slate-100">有料道路の利用条件</legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                'compare' => ['両方を比較', '高速と下道を並べる'],
                                'prefer_toll' => ['高速道路を優先', '時間短縮を重視'],
                                'avoid_toll' => ['下道を優先', '通行料金を節約'],
                            ] as $value => [$label, $description])
                                <label class="cursor-pointer rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition hover:border-emerald-400/50 has-checked:border-emerald-400 has-checked:bg-emerald-400/10">
                                    <span class="flex items-start gap-3">
                                        <input
                                            type="radio"
                                            name="toll_preference"
                                            value="{{ $value }}"
                                            @checked(old('toll_preference', 'compare') === $value)
                                            class="mt-1 size-4 accent-emerald-400"
                                        >
                                        <span>
                                            <span class="block text-sm font-semibold text-white">{{ $label }}</span>
                                            <span class="mt-1 block text-xs leading-5 text-slate-400">{{ $description }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('toll_preference')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-400 px-6 py-4 font-bold text-slate-950 transition hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-400/30">
                        高速と下道を比較する
                    </button>
                </form>
            </div>

            <aside class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 lg:self-start">
                <p class="text-sm font-semibold text-emerald-300">このツールで比較するもの</p>
                <ul class="mt-5 space-y-4 text-sm leading-6 text-slate-300">
                    <li class="flex gap-3"><span class="text-emerald-400">01</span><span>高速道路料金と燃料代</span></li>
                    <li class="flex gap-3"><span class="text-emerald-400">02</span><span>高速ルートと下道ルートの距離</span></li>
                    <li class="flex gap-3"><span class="text-emerald-400">03</span><span>費用差と短縮できる時間</span></li>
                </ul>
                <p class="mt-7 border-t border-white/10 pt-5 text-xs leading-5 text-slate-500">
                    入力内容は現時点では保存されません。比較計算は次の開発ステップで追加します。
                </p>
            </aside>
        </section>
    </main>

    <script type="application/json" id="vehicle-catalog-data">@json($vehicleCatalog)</script>
</body>
</html>
