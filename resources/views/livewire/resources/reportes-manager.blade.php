<div class="space-y-6">
    {{-- Encabezado ejecutivo integrado al estilo SIGRExpress --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6 shadow-xs border-l-4 border-l-blue-700">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-700">
                    <span>Analítica y Control Gerencial</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-600">{{ $reportTypes[$tipo] ?? 'Selecciona tu reporte' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reportes Gerenciales</h1>
                <p class="text-sm text-slate-600">
                    Selecciona el tipo de informe y utiliza los bloques habilitados para previsualizar en modal o
                    exportar a PDF y Excel.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-xs">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Período
                        activo</span>
                    <span class="font-semibold text-slate-800 tabular-nums">{{ $fecha_desde }} —
                        {{ $fecha_hasta }}</span>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Alcance de
                        sucursal</span>
                    <span
                        class="font-semibold text-slate-800">{{ $isBranchRestricted ? 'Sucursal asignada' : 'Todas las sucursales' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel principal de parámetros --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
        <form wire:submit="preview">
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Parámetros y criterios de consulta</h2>
                    <p class="text-xs text-slate-500">
                        @if ($tipo === '')
                            Selecciona tu reporte para habilitar los bloques correspondientes.
                        @else
                            Los bloques 02, 03 y 04 se habilitan automáticamente según el tipo de reporte elegido.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button class="button primary inline-flex items-center gap-2 font-semibold" type="submit"
                        wire:loading.attr="disabled" wire:target="preview">
                        <span wire:loading.remove wire:target="preview">Previsualizar reporte</span>
                        <span wire:loading wire:target="preview">Consultando...</span>
                    </button>
                </div>
            </div>

            <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- BLOQUE 01: Tipo de reporte y rango de fechas --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Bloque 01</span>
                            <h3 class="text-sm font-bold text-slate-900">Tipo de Reporte y Rango de Fechas</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Base para guías por facturar, al cobro, anuladas,
                                cierres diarios y manifiestos.</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">
                            Principal
                        </span>
                    </div>

                    <div class="form-grid">
                        <div class="field col-span-full">
                            <label for="report-type" class="font-medium text-slate-700">Tipo de reporte</label>
                            <div wire:ignore wire:key="report-type-wrapper">
                                <x-searchable-select wire:model.live="tipo" id="report-type" class="control">
                                    <option value="">Selecciona tu reporte</option>
                                    @foreach ($reportTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </x-searchable-select>
                            </div>
                            @error('tipo')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="report-from" class="font-medium text-slate-700">Fecha inicial</label>
                            <input wire:model="fecha_desde" id="report-from" class="control tabular-nums"
                                type="date">
                            @error('fecha_desde')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="report-to" class="font-medium text-slate-700">Fecha final</label>
                            <input wire:model="fecha_hasta" id="report-to" class="control tabular-nums" type="date">
                            @error('fecha_hasta')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BLOQUE 02: Remitente / Destinatario / Cliente --}}
                <div
                    class="rounded-xl border {{ $enabledBlocks['bloque_02'] ? 'border-slate-200 bg-white' : 'border-slate-200/80 bg-slate-50/80 opacity-65' }} p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Bloque 02</span>
                            <h3 class="text-sm font-bold text-slate-900">Remitente / Destinatario</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $enabledBlocks['bloque_02'] ? 'Búsqueda predictiva en tiempo real por nombre, DNI o RUC.' : ($tipo === '' ? 'Selecciona tu reporte para habilitar este bloque.' : 'No aplica para el tipo de reporte seleccionado.') }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full {{ $enabledBlocks['bloque_02'] ? 'bg-sky-50 border-sky-200 text-sky-700' : 'bg-slate-100 border-slate-200 text-slate-500' }} border px-2.5 py-0.5 text-[11px] font-semibold">
                            {{ $enabledBlocks['bloque_02'] ? 'Habilitado' : 'Inhabilitado' }}
                        </span>
                    </div>

                    @if (!$enabledBlocks['bloque_02'])
                        <div
                            class="rounded-lg border border-dashed border-slate-300 bg-white/70 p-3 text-xs text-slate-500">
                            {{ $tipo === '' ? 'Selecciona tu reporte en el Bloque 01 para habilitar este bloque.' : 'Este bloque permanece inhabilitado para ' . ($reportTypes[$tipo] ?? $tipo) . ' y no envía parámetros a la consulta.' }}
                        </div>
                    @else
                        <div class="field">
                            <label for="report-customer" class="font-medium text-slate-700">
                                Nombre o identificación (Remitente / Destinatario)
                            </label>

                            @if ($cliente_nombre !== '' && $customerSearch === '')
                                <div class="rounded-xl border border-sky-200 bg-sky-50/60 p-3.5 space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            @if ($cliente_tipo === 'Cliente')
                                                <span
                                                    class="rounded-full bg-blue-100 border border-blue-200 px-2 py-0.5 text-[10px] font-bold text-blue-800 uppercase tracking-wide">
                                                    Cliente
                                                </span>
                                            @elseif ($cliente_tipo === 'Remitente')
                                                <span
                                                    class="rounded-full bg-emerald-100 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-800 uppercase tracking-wide">
                                                    Remitente
                                                </span>
                                            @elseif ($cliente_tipo === 'Destinatario')
                                                <span
                                                    class="rounded-full bg-indigo-100 border border-indigo-200 px-2 py-0.5 text-[10px] font-bold text-indigo-800 uppercase tracking-wide">
                                                    Destinatario
                                                </span>
                                            @else
                                                <span
                                                    class="rounded-full bg-slate-100 border border-slate-300 px-2 py-0.5 text-[10px] font-bold text-slate-700 uppercase tracking-wide">
                                                    Filtro fijado
                                                </span>
                                            @endif
                                            <span class="font-bold text-slate-900 text-sm">{{ $cliente_nombre }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="editCustomer"
                                                class="button secondary text-xs py-1 px-2.5">
                                                Cambiar
                                            </button>
                                            <button type="button" wire:click="clearCustomer"
                                                class="button secondary text-xs py-1 px-2.5 text-rose-600 hover:text-rose-800">
                                                Limpiar
                                            </button>
                                        </div>
                                    </div>

                                    @if ($cliente_identificacion !== '')
                                        <div class="text-xs text-slate-600 flex items-center gap-2">
                                            <span class="text-slate-400 font-medium">Identificación (DNI/RUC):</span>
                                            <span
                                                class="font-mono font-semibold text-slate-800 bg-white border border-slate-200 rounded px-1.5 py-0.5">{{ $cliente_identificacion }}</span>
                                        </div>
                                    @endif
                                    <p class="text-[11px] text-slate-500">
                                        Filtro fijado para remitente o destinatario en las consultas y exportaciones.
                                    </p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input wire:model.live.debounce.250ms="customerSearch" id="report-customer"
                                            type="search" class="control flex-1" maxlength="100"
                                            placeholder="Ej. PING, remitente, destinatario o identificación..."
                                            autocomplete="off">
                                        @if ($cliente_nombre !== '' || $customerSearch !== '')
                                            <button wire:click="clearCustomer" type="button"
                                                class="button secondary text-xs shrink-0">
                                                Limpiar
                                            </button>
                                        @endif
                                    </div>

                                    @if ($customerSearch !== '')
                                        <p class="text-xs text-slate-500">
                                            Filtrando en tiempo real por: <strong>"{{ $customerSearch }}"</strong>.
                                            Puedes seleccionar una sugerencia o generar el reporte con este texto.
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500">
                                            Escribe al menos 2 caracteres para ver sugerencias predictivas (clientes o
                                            guías) o presiona Previsualizar para texto libre.
                                        </p>
                                    @endif

                                    @if (mb_strlen(trim($customerSearch)) >= 2 && $predictiveSuggestions->isNotEmpty())
                                        <div
                                            class="rounded-xl border border-slate-200 bg-white shadow-lg p-2 grid gap-1.5 max-h-56 overflow-y-auto">
                                            <div
                                                class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 flex items-center justify-between">
                                                <span>Sugerencias predictivas</span>
                                                <span class="text-slate-400 font-normal">Catálogo y Guías</span>
                                            </div>
                                            @foreach ($predictiveSuggestions as $suggestion)
                                                <button
                                                    wire:key="pred-sug-{{ $suggestion['tipo'] }}-{{ $suggestion['id'] ?? $loop->index }}-{{ $suggestion['nombre'] }}"
                                                    wire:click="selectSuggestion('{{ addslashes($suggestion['nombre']) }}', {{ $suggestion['id'] ? $suggestion['id'] : 'null' }}, '{{ addslashes($suggestion['identificacion']) }}', '{{ $suggestion['tipo'] }}')"
                                                    type="button"
                                                    class="w-full rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-blue-50/70 hover:border-blue-200 p-2.5 text-left transition flex items-center justify-between gap-3">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        @if ($suggestion['tipo'] === 'Cliente')
                                                            <span
                                                                class="shrink-0 rounded bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 uppercase">Cliente</span>
                                                        @elseif ($suggestion['tipo'] === 'Remitente')
                                                            <span
                                                                class="shrink-0 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 uppercase">Remitente</span>
                                                        @elseif ($suggestion['tipo'] === 'Destinatario')
                                                            <span
                                                                class="shrink-0 rounded bg-indigo-100 text-indigo-800 text-[10px] font-bold px-2 py-0.5 uppercase">Destinatario</span>
                                                        @endif
                                                        <span
                                                            class="font-semibold text-xs text-slate-900 truncate">{{ $suggestion['nombre'] }}</span>
                                                    </div>

                                                    @if (!empty($suggestion['identificacion']))
                                                        <span
                                                            class="shrink-0 font-mono text-[11px] text-slate-500 bg-white border border-slate-200 px-1.5 py-0.5 rounded">
                                                            {{ $suggestion['identificacion'] }}
                                                        </span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (mb_strlen(trim($customerSearch)) >= 2 && $predictiveSuggestions->isEmpty())
                                        <div
                                            class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-500">
                                            Sin coincidencias en catálogo. Se buscará por coincidencia de texto libre en
                                            guías al previsualizar.
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @error('cliente')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                            @error('cliente_nombre')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- BLOQUE 03: Sucursal de origen y Ciudades de destino --}}
                <div
                    class="rounded-xl border {{ $enabledBlocks['bloque_03'] ? 'border-slate-200 bg-white' : 'border-slate-200/80 bg-slate-50/80 opacity-65' }} p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Bloque
                                03</span>
                            <h3 class="text-sm font-bold text-slate-900">Sucursal de Origen y Destinos</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $enabledBlocks['bloque_03'] ? 'Filtra por sucursal de origen y selecciona uno o varios cantones/ciudades de destino.' : ($tipo === '' ? 'Selecciona tu reporte para habilitar este bloque.' : 'No aplica para el tipo de reporte seleccionado.') }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full {{ $enabledBlocks['bloque_03'] ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-slate-100 border-slate-200 text-slate-500' }} border px-2.5 py-0.5 text-[11px] font-semibold">
                            {{ $enabledBlocks['bloque_03'] ? 'Habilitado' : 'Inhabilitado' }}
                        </span>
                    </div>

                    @if (!$enabledBlocks['bloque_03'])
                        <div
                            class="rounded-lg border border-dashed border-slate-300 bg-white/70 p-3 text-xs text-slate-500">
                            {{ $tipo === '' ? 'Selecciona tu reporte en el Bloque 01 para habilitar este bloque.' : 'Este bloque permanece inhabilitado para ' . ($reportTypes[$tipo] ?? $tipo) . ' y no envía parámetros a la consulta.' }}
                        </div>
                    @else
                        <div class="form-grid">
                            <div class="field">
                                <label for="report-origin-branch" class="font-medium text-slate-700">Sucursal de
                                    origen</label>
                                <x-searchable-select wire:key="select-origin-branch-enabled"
                                    wire:model="sucursal_origen" id="report-origin-branch" class="control">
                                    <option value="">Todas las sucursales de origen</option>
                                    @foreach ($originBranches as $origBranch)
                                        <option value="{{ $origBranch->id_sucursal }}">{{ $origBranch->nombre }}
                                        </option>
                                    @endforeach
                                </x-searchable-select>
                                <p class="text-xs text-slate-500 mt-1">Opcional: déjalo en "Todas" para consultar
                                    cualquier sucursal de origen.</p>
                                @error('sucursal_origen')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="report-dest-search" class="font-medium text-slate-700">
                                        Ciudades / cantones de destino (múltiple)
                                    </label>
                                    <div class="flex items-center gap-3">
                                        @if (!empty($ciudad_destino))
                                            <button type="button" wire:click="clearCiudadesDestino"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline">
                                                Limpiar ({{ count($ciudad_destino) }})
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($ciudad_destino))
                                    <div
                                        class="flex flex-wrap items-center gap-1.5 p-2 rounded-lg border border-slate-200 bg-slate-50/70 min-h-[38px]">
                                        @foreach ($ciudad_destino as $selCity)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-1 text-xs font-semibold text-blue-800">
                                                <span>{{ $selCity }}</span>
                                                <button type="button"
                                                    wire:click="removeCiudadDestino('{{ addslashes($selCity) }}')"
                                                    class="rounded-full hover:bg-blue-200 text-blue-600 hover:text-blue-900 w-4 h-4 inline-flex items-center justify-center text-[11px] leading-none transition"
                                                    title="Remover {{ $selCity }}">
                                                    &times;
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500">
                                        Marca con visto una o varias ciudades/cantones, o deja sin marcar para incluir
                                        todos.
                                    </p>
                                @endif

                                <input wire:model.live.debounce.150ms="destinationCitySearch" id="report-dest-search"
                                    type="search" placeholder="Buscar ciudad o cantón de destino..."
                                    class="control text-xs w-full" autocomplete="off">

                                @php
                                    $trimmedCitySearch = trim($destinationCitySearch);
                                    $filteredCities = $cities
                                        ->filter(function ($c) use ($trimmedCitySearch) {
                                            if ($trimmedCitySearch === '') {
                                                return true;
                                            }
                                            return str_contains(mb_strtolower($c), mb_strtolower($trimmedCitySearch));
                                        })
                                        ->values();
                                @endphp

                                @if ($filteredCities->isNotEmpty())
                                    <div class="flex justify-end">
                                        <button type="button"
                                            wire:click="selectAllFilteredCities({{ $filteredCities->toJson() }})"
                                            class="text-xs font-semibold text-blue-700 hover:text-blue-900 hover:underline">
                                            Marcar visibles ({{ $filteredCities->count() }})
                                        </button>
                                    </div>
                                @endif

                                <div
                                    class="rounded-lg border border-slate-200 bg-white p-1.5 max-h-36 overflow-y-auto space-y-0.5">
                                    @forelse ($filteredCities as $city)
                                        @php $isSelected = in_array($city, $ciudad_destino, true); @endphp
                                        <button type="button" wire:key="dest-city-{{ md5($city) }}"
                                            wire:click="toggleCiudadDestino('{{ addslashes($city) }}')"
                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded text-xs transition {{ $isSelected ? 'bg-blue-50 text-blue-800 font-semibold border border-blue-200' : 'text-slate-700 hover:bg-slate-50' }}">
                                            <span class="flex items-center gap-2">
                                                <span
                                                    class="w-3.5 h-3.5 rounded border flex items-center justify-center text-[9px] {{ $isSelected ? 'bg-blue-600 border-blue-600 text-white font-bold' : 'border-slate-300 bg-white' }}">
                                                    @if ($isSelected)
                                                        ✓
                                                    @endif
                                                </span>
                                                <span>{{ $city }}</span>
                                            </span>
                                            @if ($isSelected)
                                                <span
                                                    class="text-[10px] text-blue-600 uppercase font-bold tracking-wider">Activa</span>
                                            @endif
                                        </button>
                                    @empty
                                        <p class="text-xs text-slate-400 p-2 text-center">
                                            No hay ciudades que coincidan con "{{ $destinationCitySearch }}".
                                        </p>
                                    @endforelse
                                </div>

                                @error('ciudad_destino')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                                @error('ciudad_destino.*')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>

                {{-- BLOQUE 04: Sucursal y Usuario --}}
                <div
                    class="rounded-xl border {{ $enabledBlocks['bloque_04'] ? 'border-slate-200 bg-white' : 'border-slate-200/80 bg-slate-50/80 opacity-65' }} p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Bloque
                                04</span>
                            <h3 class="text-sm font-bold text-slate-900">Sucursal y Usuario Operativo</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $enabledBlocks['bloque_04'] ? 'Filtra cierres de caja por usuario y aplica el control de sucursal asignada.' : ($tipo === '' ? 'Selecciona tu reporte para habilitar este bloque.' : 'No aplica para el tipo de reporte seleccionado.') }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full {{ $enabledBlocks['bloque_04'] ? 'bg-slate-100 border-slate-200 text-slate-700' : 'bg-slate-100 border-slate-200 text-slate-500' }} border px-2.5 py-0.5 text-[11px] font-semibold">
                            {{ $enabledBlocks['bloque_04'] ? 'Habilitado' : 'Inhabilitado' }}
                        </span>
                    </div>

                    @if (!$enabledBlocks['bloque_04'])
                        <div
                            class="rounded-lg border border-dashed border-slate-300 bg-white/70 p-3 text-xs text-slate-500">
                            {{ $tipo === '' ? 'Selecciona tu reporte en el Bloque 01 para habilitar este bloque.' : 'Este bloque permanece inhabilitado para ' . ($reportTypes[$tipo] ?? $tipo) . ' y no envía parámetros a la consulta.' }}
                        </div>
                    @else
                        <div class="form-grid">
                            <div class="field">
                                <label for="report-branch" class="font-medium text-slate-700">Sucursal</label>
                                @if ($isBranchRestricted)
                                    <select wire:model="sucursal" id="report-branch" class="control" disabled>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id_sucursal }}">{{ $branch->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">Tu usuario está restringido a su sucursal
                                        asignada.</p>
                                @else
                                    <x-searchable-select wire:key="select-branch-enabled" wire:model="sucursal"
                                        id="report-branch" class="control">
                                        <option value="">Todas las sucursales</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id_sucursal }}">{{ $branch->nombre }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                @endif
                                @error('sucursal')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="report-user" class="font-medium text-slate-700">Usuario</label>
                                <x-searchable-select wire:key="select-user-enabled" wire:model="usuario"
                                    id="report-user" class="control">
                                    <option value="">Todos los usuarios</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('usuario')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </section>

    {{-- MODAL DE PREVISUALIZACIÓN --}}
    @if ($showPreviewModal && $searched && $guides)
        <div wire:key="preview-modal" wire:keydown.escape.window="closePreviewModal"
            class="fixed inset-0 flex items-center justify-center bg-slate-900/60 p-3 sm:p-6" style="z-index: 9999;"
            role="dialog" aria-modal="true" aria-labelledby="preview-modal-title">
            <div class="flex w-full max-w-7xl flex-col min-h-0 rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden"
                style="max-height: 90vh;">
                {{-- Cabecera fija del modal --}}
                <div
                    class="shrink-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 id="preview-modal-title" class="text-lg font-bold text-slate-900">
                                {{ $reportTypes[$tipo] ?? 'Reporte' }}
                            </h2>
                            <span class="badge">{{ number_format($totalGuidesCount) }} registros</span>
                        </div>
                        <p class="text-xs text-slate-500 tabular-nums mt-0.5">
                            Período: {{ $fecha_desde }} — {{ $fecha_hasta }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="button secondary font-medium" href="{{ route('reports.export.pdf', $filters) }}">
                            Exportar PDF
                        </a>
                        <a class="button secondary font-medium" href="{{ route('reports.export.excel', $filters) }}">
                            Exportar Excel protegido
                        </a>
                        <button type="button" wire:click="closePreviewModal" class="button primary font-semibold">
                            Cerrar
                        </button>
                    </div>
                </div>

                {{-- Cuerpo con barra de desplazamiento vertical y horizontal --}}
                <div class="flex-1 min-h-0 overflow-y-auto overflow-x-auto p-5 space-y-5"
                    style="max-height: calc(90vh - 84px); overflow-y: auto;">
                    <section class="grid grid-cols-3 gap-4"
                        style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;"
                        aria-label="Resumen del reporte">
                        <article class="rounded-xl border border-slate-200 bg-white p-4 border-t-4 border-t-blue-700">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Guías
                                coincidentes</div>
                            <div class="mt-1 text-2xl font-bold text-slate-900 tabular-nums">
                                {{ number_format($totalGuidesCount) }}</div>
                        </article>

                        <article
                            class="rounded-xl border border-slate-200 bg-white p-4 border-t-4 border-t-indigo-600">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total ítems /
                                bultos</div>
                            <div class="mt-1 text-2xl font-bold text-slate-900 tabular-nums">
                                {{ number_format($totalItems) }}</div>
                        </article>

                        <article
                            class="rounded-xl border border-slate-200 bg-white p-4 border-t-4 border-t-emerald-600">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Valor total del
                                reporte</div>
                            <div class="mt-1 text-2xl font-bold text-emerald-700 tabular-nums">
                                ${{ number_format((float) $reportTotal, 2) }}</div>
                        </article>
                    </section>

                    <div class="table-wrap overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th>N.º guía</th>
                                    <th>Fecha</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Cliente / remitente</th>
                                    <th>Destinatario</th>
                                    <th>Sucursal</th>
                                    <th>Usuario</th>
                                    <th>Estado cobro</th>
                                    <th class="text-right">Ítems</th>
                                    <th class="text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($guides as $guide)
                                    <tr wire:key="report-guide-{{ $guide->id_cabecera }}">
                                        <td class="font-semibold text-slate-900 tabular-nums">{{ $guide->num_guia }}
                                        </td>
                                        <td class="tabular-nums whitespace-nowrap">
                                            {{ $guide->fecha_emision?->format('d/m/Y') }}</td>
                                        <td>{{ $guide->ciudad_origen }}</td>
                                        <td>{{ $guide->ciudad_destino }}</td>
                                        <td>{{ $guide->persona?->nombre ?? $guide->nom_remitente }}</td>
                                        <td>{{ $guide->nom_destinatario }}</td>
                                        <td>{{ $guide->sucursal?->nombre ?? '—' }}</td>
                                        <td>{{ $guide->usuario?->name ?? '—' }}</td>
                                        <td>{{ $guide->estatus_cobro ?? '—' }}</td>
                                        <td class="text-right tabular-nums">
                                            {{ (int) ($guide->detalles_sum_cantidad ?? 0) }}</td>
                                        <td class="text-right font-semibold text-slate-900 tabular-nums">
                                            ${{ number_format((float) $guide->valor_guia, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11">
                                            <div class="empty-state">
                                                <strong>Sin resultados</strong>
                                                <span>Prueba con otros filtros o amplía el rango de fechas.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="9" class="font-bold text-slate-800">Total del reporte</td>
                                    <td class="text-right font-bold text-slate-900 tabular-nums">
                                        {{ number_format($totalItems) }}</td>
                                    <td class="text-right font-bold text-emerald-700 tabular-nums">
                                        ${{ number_format((float) $reportTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($guides->hasPages())
                        <div class="pagination-container">{{ $guides->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
