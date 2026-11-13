<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Analítica y control</p>
            <h1>Reportes gerenciales</h1>
            <p class="muted">Previsualiza la operación y exporta informes con filtros controlados.</p>
        </div>
    </div>

    <section class="panel">
        <form wire:submit="preview">
            <div class="panel-toolbar">
                <strong>Parámetros del reporte</strong>
                <div class="heading-actions">
                    <button class="button primary" type="submit" wire:loading.attr="disabled">Previsualizar</button>
                    @if ($searched)
                        <a class="button secondary" href="{{ route('reports.export.pdf', $filters) }}">Exportar PDF</a>
                        <a class="button secondary" href="{{ route('reports.export.excel', $filters) }}">Exportar Excel protegido</a>
                    @endif
                </div>
            </div>
            <div class="resource-modal-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="report-type">Tipo de reporte</label>
                        <x-searchable-select wire:model="tipo" id="report-type" class="control">
                            @foreach ($reportTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-searchable-select>
                        @error('tipo') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-from">Desde</label>
                        <input wire:model="fecha_desde" id="report-from" class="control" type="date">
                        @error('fecha_desde') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-to">Hasta</label>
                        <input wire:model="fecha_hasta" id="report-to" class="control" type="date">
                        @error('fecha_hasta') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-customer">Cliente</label>
                        @if ($selectedCustomer)
                            <div class="control flex items-center justify-between gap-3">
                                <span>{{ $selectedCustomer->nombre }} · {{ $selectedCustomer->num_dni }}</span>
                                <button wire:click="clearCustomer" type="button" class="text-button">Todos los clientes</button>
                            </div>
                        @else
                            <input
                                wire:model.live.debounce.300ms="customerSearch"
                                id="report-customer"
                                type="search"
                                class="control"
                                maxlength="100"
                                placeholder="Buscar por nombre o identificación"
                                autocomplete="off"
                            >
                            @if (mb_strlen(trim($customerSearch)) >= 2)
                                <div class="mt-2 grid gap-1">
                                    @forelse ($customerResults as $customer)
                                        <button
                                            wire:key="report-customer-{{ $customer->id_persona }}"
                                            wire:click="selectCustomer({{ $customer->id_persona }})"
                                            type="button"
                                            class="control text-left"
                                        >
                                            {{ $customer->nombre }} · {{ $customer->num_dni }}
                                        </button>
                                    @empty
                                        <span class="muted">No se encontraron clientes activos.</span>
                                    @endforelse
                                </div>
                            @else
                                <span class="muted">Escribe al menos dos caracteres o deja vacío para incluir todos.</span>
                            @endif
                        @endif
                        @error('cliente') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-origin">Ciudad de origen</label>
                        <x-searchable-select wire:model="ciudad_origen" id="report-origin" class="control">
                            <option value="">Todas las ciudades</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </x-searchable-select>
                        @error('ciudad_origen') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-destination">Ciudad de destino</label>
                        <x-searchable-select wire:model="ciudad_destino" id="report-destination" class="control">
                            <option value="">Todas las ciudades</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </x-searchable-select>
                        @error('ciudad_destino') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-branch">Sucursal</label>
                        <x-searchable-select wire:model="sucursal" id="report-branch" class="control">
                            <option value="">Todas las sucursales</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id_sucursal }}">{{ $branch->nombre }}</option>
                            @endforeach
                        </x-searchable-select>
                        @error('sucursal') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="report-user">Usuario</label>
                        <x-searchable-select wire:model="usuario" id="report-user" class="control">
                            <option value="">Todos los usuarios</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </x-searchable-select>
                        @error('usuario') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </form>
    </section>

    @if ($searched && $guides)
        <section class="stats" aria-label="Resumen del reporte">
            <article class="stat"><div class="stat-label">Guías coincidentes</div><div class="stat-value">{{ number_format($guides->total()) }}</div></article>
            <article class="stat"><div class="stat-label">Valor total</div><div class="stat-value">${{ number_format((float) $reportTotal, 2) }}</div></article>
        </section>

        <section class="panel">
            <div class="panel-toolbar">
                <div><strong>{{ $reportTypes[$tipo] }}</strong><p class="muted">{{ $fecha_desde }} — {{ $fecha_hasta }}</p></div>
                <span class="badge">{{ $guides->total() }} registros</span>
            </div>
            <div class="table-wrap">
                <table>
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
                            <th>Estado de cobro</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guides as $guide)
                            <tr wire:key="report-guide-{{ $guide->id_cabecera }}">
                                <td><strong>{{ $guide->num_guia }}</strong></td>
                                <td>{{ $guide->fecha_emision?->format('d/m/Y') }}</td>
                                <td>{{ $guide->ciudad_origen }}</td>
                                <td>{{ $guide->ciudad_destino }}</td>
                                <td>{{ $guide->persona?->nombre ?? $guide->nom_remitente }}</td>
                                <td>{{ $guide->nom_destinatario }}</td>
                                <td>{{ $guide->sucursal?->nombre ?? '—' }}</td>
                                <td>{{ $guide->usuario?->name ?? '—' }}</td>
                                <td>{{ $guide->estatus_cobro ?? '—' }}</td>
                                <td>${{ number_format((float) $guide->valor_guia, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10"><div class="empty-state"><strong>Sin resultados</strong><span>Prueba con otros filtros.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($guides->hasPages())
                <div class="pagination-container">{{ $guides->links() }}</div>
            @endif
        </section>
    @endif
</div>
