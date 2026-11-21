<div class="space-y-6">
    {{-- Encabezado ejecutivo integrado al estilo SIGRExpress --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6 shadow-xs border-l-4 border-l-blue-700">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-700">
                    <span>Analítica y Control Gerencial</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-600">{{ $reportTypes[$tipo] ?? 'Reporte' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reportes Gerenciales</h1>
                <p class="text-sm text-slate-600">
                    Selecciona el tipo de informe y utiliza los bloques correspondientes para previsualizar en modal o
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
                    <p class="text-xs text-slate-500">Cada bloque indica a qué reportes o manifiestos pertenece cada
                        filtro.</p>
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
                            <x-searchable-select wire:model="tipo" id="report-type" class="control">
                                @foreach ($reportTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-searchable-select>
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
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Bloque 02</span>
                            <h3 class="text-sm font-bold text-slate-900">Remitente / Destinatario</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Filtra por nombre o identificación del remitente o
                                destinatario (o selecciona un cliente).</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-sky-50 border border-sky-200 px-2.5 py-0.5 text-[11px] font-semibold text-sky-700">
                            Remitente / Destinatario
                        </span>
                    </div>

                    <div class="field">
                        <label for="report-customer" class="font-medium text-slate-700">
                            Nombre o identificación (Remitente / Destinatario)
                        </label>

                        @if ($selectedCustomer)
                            <div
                                class="rounded-lg border border-sky-200 bg-sky-50/50 px-3.5 py-2.5 flex items-center justify-between gap-3">
                                <div class="text-sm">
                                    <span class="font-semibold text-slate-900">{{ $selectedCustomer->nombre }}</span>
                                    @if ($selectedCustomer->num_dni)
                                        <span
                                            class="ml-1.5 rounded bg-white border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 tabular-nums">
                                            {{ $selectedCustomer->num_dni }}
                                        </span>
                                    @endif
                                </div>
                                <button wire:click="clearCustomer" type="button"
                                    class="text-button text-xs font-semibold text-blue-700 hover:underline">
                                    Limpiar filtro
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <input wire:model.live.debounce.300ms="customerSearch" id="report-customer"
                                    type="search" class="control flex-1" maxlength="100"
                                    placeholder="Ej. PING, nombre de remitente/destinatario o identificación..."
                                    autocomplete="off">
                                @if ($cliente_nombre !== '' || $customerSearch !== '')
                                    <button wire:click="clearCustomer" type="button"
                                        class="button secondary text-xs shrink-0">
                                        Limpiar
                                    </button>
                                @endif
                            </div>

                            @if ($cliente_nombre !== '')
                                <p class="text-xs font-medium text-sky-800 mt-1.5">
                                    Filtrando remitente o destinatario que contenga:
                                    <strong>"{{ $cliente_nombre }}"</strong>
                                </p>
                            @else
                                <p class="text-xs text-slate-500 mt-1">
                                    Escribe el nombre o identificación del remitente o destinatario, o deja vacío para
                                    incluir todos.
                                </p>
                            @endif

                            @if (mb_strlen(trim($customerSearch)) >= 2 && $customerResults->isNotEmpty())
                                <div
                                    class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-2 grid gap-1.5 max-h-44 overflow-y-auto">
                                    @foreach ($customerResults as $customer)
                                        <button wire:key="report-customer-{{ $customer->id_persona }}"
                                            wire:click="selectCustomer({{ $customer->id_persona }})" type="button"
                                            class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-left text-xs text-slate-800 hover:bg-slate-100 transition flex items-center justify-between gap-2">
                                            <span class="font-semibold">{{ $customer->nombre }}</span>
                                            <span class="text-slate-500 tabular-nums">{{ $customer->num_dni }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        @error('cliente')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                        @error('cliente_nombre')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- BLOQUE 03: Manifiestos y rutas --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Bloque
                                03</span>
                            <h3 class="text-sm font-bold text-slate-900">Manifiestos, Origen y Destinos</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Para manifiestos por ciudad de origen/destino y
                                guías pendientes o pagadas al cobro.</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-700">
                            Rutas
                        </span>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="report-origin" class="font-medium text-slate-700">Ciudad de origen</label>
                            <x-searchable-select wire:model="ciudad_origen" id="report-origin" class="control">
                                <option value="">Todas las ciudades</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </x-searchable-select>
                            @error('ciudad_origen')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="report-destination" class="font-medium text-slate-700">Ciudades de destino
                                (múltiple)</label>
                            <select wire:model="ciudad_destino" id="report-destination" class="control" multiple
                                size="4">
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Selecciona una o varias ciudades (Ctrl/Cmd) o deja
                                vacío para todas.</p>
                            @error('ciudad_destino')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                            @error('ciudad_destino.*')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BLOQUE 04: Sucursal y Usuario --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Bloque
                                04</span>
                            <h3 class="text-sm font-bold text-slate-900">Sucursal y Usuario Operativo</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Filtra cierres de caja por usuario y aplica el
                                control de sucursal asignada.</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                            Operación
                        </span>
                    </div>

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
                                <x-searchable-select wire:model="sucursal" id="report-branch" class="control">
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
                            <x-searchable-select wire:model="usuario" id="report-user" class="control">
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
                </div>
            </div>
        </form>
    </section>

    {{-- MODAL DE PREVISUALIZACIÓN --}}
    @if ($showPreviewModal && $searched && $guides)
        @teleport('body')
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
                                    {{ $reportTypes[$tipo] }}
                                </h2>
                                <span class="badge">{{ number_format($guides->total()) }} registros</span>
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
                                    {{ number_format($guides->total()) }}</div>
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
        @endteleport
    @endif
</div>
