<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de guía · {{ config('app.name', 'SIGRExpress') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-16">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">← Volver al inicio</a>
        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-slate-900 px-6 py-8 text-white sm:px-9">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">SIGRExpress · Tracking</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight">Consulta tu envío</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Ingresa el número de guía y la identificación del destinatario para consultar el estado de forma segura.</p>
            </div>
            <div class="p-6 sm:p-9">
                <form wire:submit="search" class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="tracking-number" class="mb-1.5 block text-sm font-semibold">Número de guía</label>
                        <input wire:model="num_guia" id="tracking-number" type="text" inputmode="numeric" autocomplete="off" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                        @error('num_guia') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tracking-identification" class="mb-1.5 block text-sm font-semibold">Identificación del destinatario</label>
                        <input wire:model="identificacion" id="tracking-identification" type="text" autocomplete="off" maxlength="20" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100" required>
                        @error('identificacion') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-800 disabled:opacity-60" wire:loading.attr="disabled">
                            <span wire:loading.remove>Consultar estado</span>
                            <span wire:loading>Consultando…</span>
                        </button>
                    </div>
                </form>

                @error('lookup')
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">{{ $message }}</div>
                @enderror

                @if ($guide)
                    <section class="mt-8 border-t border-slate-200 pt-7" aria-labelledby="tracking-result">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Guía n.º {{ $guide->num_guia }}</p>
                                <h2 id="tracking-result" class="mt-1 text-2xl font-bold">Estado del envío</h2>
                            </div>
                            <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-800">{{ $guide->estado_tracking }}</span>
                        </div>
                        <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Remitente</dt><dd class="mt-1 font-semibold">{{ $guide->nom_remitente }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Destinatario</dt><dd class="mt-1 font-semibold">{{ $guide->nom_destinatario }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Origen</dt><dd class="mt-1 font-semibold">{{ $guide->ciudad_origen }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Destino</dt><dd class="mt-1 font-semibold">{{ $guide->ciudad_destino }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Emisión</dt><dd class="mt-1 font-semibold">{{ $guide->fecha_emision?->format('d/m/Y') }}</dd></div>
                        </dl>
                        @if ($guide->detalles->isNotEmpty())
                            <div class="mt-7">
                                <h3 class="font-bold">Detalle de la carga</h3>
                                <ul class="mt-3 divide-y divide-slate-100 rounded-lg border border-slate-200">
                                    @foreach ($guide->detalles as $detail)
                                        <li wire:key="tracking-detail-{{ $detail->id_detalle }}" class="flex justify-between gap-4 px-4 py-3 text-sm">
                                            <span>{{ $detail->descripcion }}</span><span class="font-semibold">Cantidad: {{ $detail->cantidad }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                @endif
            </div>
        </section>
        <p class="mt-5 text-center text-xs text-slate-500">La consulta requiere la identificación del destinatario para proteger la información de cada envío.</p>
    </main>
    @livewireScripts
</body>
</html>
