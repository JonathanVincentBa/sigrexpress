<div wire:key="reportes-manager-root" class="rm-wrap">
    <style>
        .rm-wrap {
            display: flex;
            flex-direction: column;
            gap: 24px;
            width: 100%;
        }

        .rm-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #1d4ed8;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .rm-hero-sub {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #1d4ed8;
            margin-bottom: 6px;
        }

        .rm-hero-sub .rm-meta-type {
            color: #475569;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 600;
        }

        .rm-hero h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }

        .rm-hero p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.45;
        }

        .rm-hero-right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .rm-period-box {
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .rm-period-box span {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }

        .rm-period-box strong {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
        }

        .rm-panel {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .rm-panel-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .rm-panel-head h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .rm-panel-head p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #64748b;
        }

        .rm-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .rm-actions button:disabled {
            opacity: 0.55;
            cursor: not-allowed !important;
            pointer-events: none;
        }

        .rm-error-box {
            margin: 18px 24px 0;
            padding: 14px 16px;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            background: #fff1f2;
            color: #9f1239;
            font-size: 12.5px;
        }

        .rm-error-box ul {
            margin: 8px 0 0 18px;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .rm-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            padding: 24px;
        }

        .rm-card {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
        }

        .rm-card.is-disabled {
            background: #f8fafc;
            opacity: 0.72;
        }

        .rm-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .rm-card-code {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #1d4ed8;
        }

        .rm-card-header h3 {
            margin: 3px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .rm-card-header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        .rm-badge {
            flex-shrink: 0;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #475569;
        }

        .rm-badge.is-active {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .rm-locked-msg {
            padding: 12px 14px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            color: #64748b;
        }

        .rm-fields-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .rm-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .rm-field-full {
            grid-column: 1 / -1;
        }

        .rm-field label {
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
        }

        .rm-field-error {
            font-size: 11.5px;
            color: #dc2626;
            margin-top: 2px;
        }

        .rm-search-hint {
            font-size: 11.5px;
            color: #1d4ed8;
            font-weight: 500;
        }

        .rm-cat-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .rm-cat-btn {
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .rm-cat-btn.is-active {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .rm-cat-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            padding: 6px 8px 2px;
        }

        .rm-selected-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            background: #f0f9ff;
        }

        .rm-selected-card span {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0369a1;
        }

        .rm-selected-card strong {
            display: block;
            margin-top: 2px;
            font-size: 13px;
            color: #0f172a;
        }

        .rm-chips-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .rm-chip-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: 600;
        }

        .rm-chip-tag button {
            border: 0;
            background: transparent;
            color: #1d4ed8;
            font-weight: 700;
            cursor: pointer;
            padding: 0 2px;
        }

        .rm-city-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-height: 175px;
            overflow-y: auto;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
        }

        .rm-city-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #334155;
            font-size: 12px;
            cursor: pointer;
            text-align: left;
        }

        .rm-city-btn:hover {
            background: #f8fafc;
        }

        .rm-city-btn.is-selected {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
            font-weight: 600;
        }

        .rm-row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .rm-link-clear {
            border: 0;
            background: transparent;
            color: #dc2626;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }

        .rm-link-clear:hover {
            text-decoration: underline;
        }

        @media (max-width: 960px) {
            .rm-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .rm-fields-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php
        $catalogoReportes = [
            // CLIENTES
            ['value' => 'cliente_xfacturar', 'label' => 'Guías por facturar (por cliente)', 'categoria' => 'clientes'],
            [
                'value' => 'cliente_xfacturar_canceladas',
                'label' => 'Guías por facturar canceladas (por cliente)',
                'categoria' => 'clientes',
            ],
            [
                'value' => 'cliente_xfacturar_xpagar',
                'label' => 'Guías por facturar pendientes de pago (por cliente)',
                'categoria' => 'clientes',
            ],
            [
                'value' => 'cliente_xfacturar_xpagar_todos',
                'label' => 'Guías por facturar pendientes (Todos los clientes)',
                'categoria' => 'clientes',
            ],
            [
                'value' => 'cliente_todas_formas_pago',
                'label' => 'Historial completo (Todas las formas de pago)',
                'categoria' => 'clientes',
            ],

            // MENSUALIZADOS
            [
                'value' => 'sucursal_xfacturar_canceladas',
                'label' => 'Guías por facturar canceladas (por sucursal origen)',
                'categoria' => 'mensualizados',
            ],
            [
                'value' => 'sucursal_xfacturar_xpagar',
                'label' => 'Guías por facturar pendientes (por sucursal origen)',
                'categoria' => 'mensualizados',
            ],

            // VARIOS
            ['value' => 'manifiesto_destino', 'label' => 'Manifiesto por ciudades de destino', 'categoria' => 'varios'],
            ['value' => 'manifiesto_origen', 'label' => 'Manifiesto por sucursal de origen', 'categoria' => 'varios'],
            ['value' => 'cobro_pendiente', 'label' => 'Guías pendientes de pago (Al cobro)', 'categoria' => 'varios'],
            ['value' => 'cobro_pagado', 'label' => 'Guías pagadas (Al cobro)', 'categoria' => 'varios'],
            ['value' => 'cierres_diarios', 'label' => 'Cierre de caja diario por usuario', 'categoria' => 'varios'],
            ['value' => 'anuladas', 'label' => 'Reporte de guías anuladas', 'categoria' => 'varios'],
        ];

        $categoriaActiva = $reportCategoryTab ?? 'todos';
        $reporteSeleccionado = collect($catalogoReportes)->firstWhere('value', (string) ($tipo_reporte ?? ''));

        $reportesMostrados = collect($catalogoReportes);
        if ($categoriaActiva !== 'todos') {
            $reportesMostrados = $reportesMostrados->where('categoria', $categoriaActiva);
        }
        $reportesAgrupados = $reportesMostrados->groupBy('categoria');

        $etiquetasCategoria = [
            'clientes' => 'Clientes',
            'mensualizados' => 'Mensualizados',
            'varios' => 'Varios',
        ];

        $listaClientes = collect($filteredClientes ?? [])
            ->map(function ($c) {
                if (is_array($c)) {
                    return (string) ($c['nom_remitente'] ?? ($c['nombre'] ?? ($c['name'] ?? '')));
                }
                if (is_object($c)) {
                    return (string) ($c->nom_remitente ?? ($c->nombre ?? ($c->name ?? '')));
                }
                return (string) $c;
            })
            ->filter(fn($n) => $n !== '')
            ->values();

        $listaSucursales = collect($sucursales ?? [])
            ->map(function ($s) {
                if (is_array($s)) {
                    $val = $s['nombre'] ?? ($s['sucursal'] ?? ($s['descripcion'] ?? ($s['id'] ?? '')));
                    $label = $s['nombre'] ?? ($s['sucursal'] ?? ($s['descripcion'] ?? $val));
                } elseif (is_object($s)) {
                    $val = $s->nombre ?? ($s->sucursal ?? ($s->descripcion ?? ($s->id ?? '')));
                    $label = $s->nombre ?? ($s->sucursal ?? ($s->descripcion ?? $val));
                } else {
                    $val = (string) $s;
                    $label = (string) $s;
                }
                return ['value' => (string) $val, 'label' => (string) $label];
            })
            ->filter(fn($item) => $item['value'] !== '')
            ->values();

        $qSucursal = mb_strtolower(trim($branchSearch ?? ''));
        $sucursalesFiltradas =
            $qSucursal === ''
                ? $listaSucursales
                : $listaSucursales
                    ->filter(fn($item) => str_contains(mb_strtolower($item['label']), $qSucursal))
                    ->values();

        $sucursalSeleccionada = $listaSucursales->firstWhere('value', (string) ($ciudad_origen ?? ''));

        $listaCiudades = collect($filteredCiudades ?? [])
            ->map(function ($city) {
                if (is_array($city)) {
                    return (string) ($city['descripcion'] ?? ($city['nombre'] ?? ($city['name'] ?? '')));
                }
                if (is_object($city)) {
                    return (string) ($city->descripcion ?? ($city->nombre ?? ($city->name ?? '')));
                }
                return (string) $city;
            })
            ->filter(fn($n) => $n !== '')
            ->values();

        $listaUsuarios = collect($usuarios ?? [])
            ->map(function ($u) {
                $id = is_array($u) ? $u['id'] ?? '' : (is_object($u) ? $u->id ?? '' : $u);
                $name = is_array($u) ? $u['name'] ?? $id : (is_object($u) ? $u->name ?? $id : $u);
                return ['id' => (string) $id, 'name' => (string) $name];
            })
            ->filter(fn($item) => $item['id'] !== '')
            ->values();

        $qUsuario = mb_strtolower(trim($userSearch ?? ''));
        $usuariosFiltrados =
            $qUsuario === ''
                ? $listaUsuarios
                : $listaUsuarios->filter(fn($item) => str_contains(mb_strtolower($item['name']), $qUsuario))->values();

        $usuarioSeleccionado = $listaUsuarios->firstWhere('id', (string) ($id_users ?? ''));
    @endphp

    <div class="rm-hero">
        <div>
            <div class="rm-hero-sub">
                <span>Analítica y Control Gerencial</span>
                <span>•</span>
                <span class="rm-meta-type">
                    {{ $reporteSeleccionado['label'] ?? 'Selecciona tu reporte' }}
                </span>
            </div>
            <h1>Centro de Reportes Gerenciales</h1>
            <p>
                Selecciona el tipo de informe por categoría (Clientes, Mensualizados y Varios) y el rango de fechas.
            </p>
        </div>

        <div class="rm-hero-right">
            <div class="rm-period-box">
                <span>Período activo</span>
                <strong>{{ $fecha_inicio ?: '—' }} — {{ $fecha_final ?: '—' }}</strong>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('reports.cierre.usuario'))
                <a href="{{ route('reports.cierre.usuario') }}" class="button secondary">
                    Mi Cierre de Caja Hoy
                </a>
            @endif
        </div>
    </div>

    <section class="rm-panel">
        <div class="rm-panel-head">
            <div>
                <h2>Parámetros y criterios de consulta</h2>
                <p>
                    @if ($tipo_reporte === '')
                        Elige una categoría y selecciona tu reporte en el Bloque 01 para habilitar los filtros
                        necesarios.
                    @else
                        Los bloques 02, 03 y 04 se habilitan automáticamente según el tipo de reporte elegido.
                    @endif
                </p>
            </div>

            <div class="rm-actions" x-data="{ enviando: false }" x-on:livewire:navigated.window="enviando = false">
                <button type="button" wire:click="generar('pdf')"
                    x-on:click="if (enviando || $wire.generandoReporte) return; enviando = true; setTimeout(() => enviando = false, 15000)"
                    wire:loading.attr="disabled" wire:target="generar"
                    x-bind:disabled="enviando || $wire.generandoReporte || $wire.tipo_reporte === ''"
                    @disabled($tipo_reporte === '' || ($generandoReporte ?? false)) class="button danger" style="cursor: pointer;">
                    <span wire:loading.remove wire:target="generar">Generar Reporte PDF</span>
                    <span wire:loading wire:target="generar">Generando PDF...</span>
                </button>

                <button type="button" wire:click="generar('excel')"
                    x-on:click="if (enviando || $wire.generandoReporte) return; enviando = true; setTimeout(() => enviando = false, 15000)"
                    wire:loading.attr="disabled" wire:target="generar"
                    x-bind:disabled="enviando || $wire.generandoReporte || $wire.tipo_reporte === ''"
                    @disabled($tipo_reporte === '' || ($generandoReporte ?? false)) class="button primary" style="cursor: pointer;">
                    <span wire:loading.remove wire:target="generar">Exportar a Excel</span>
                    <span wire:loading wire:target="generar">Exportando Excel...</span>
                </button>
            </div>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="rm-error-box">
                <strong>Revisa los campos obligatorios:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rm-grid">
            <div class="rm-card">
                <div class="rm-card-header">
                    <div>
                        <span class="rm-card-code">Bloque 01</span>
                        <h3>Tipo de Reporte y Rango de Fechas</h3>
                        <p>Requerido en todas las consultas de clientes, mensualizados y varios.</p>
                    </div>
                    <span class="rm-badge is-active">Principal</span>
                </div>

                <div class="rm-fields-row">
                    <div class="rm-field rm-field-full">
                        <div class="rm-row-between">
                            <label>Tipo de reporte *</label>
                            @if (($tipo_reporte ?? '') !== '')
                                <button type="button" wire:click="$set('tipo_reporte', '')" class="rm-link-clear">
                                    Quitar selección
                                </button>
                            @endif
                        </div>

                        @if ($reporteSeleccionado)
                            <div class="rm-selected-card">
                                <div>
                                    <span>
                                        Reporte seleccionado
                                        ({{ $etiquetasCategoria[$reporteSeleccionado['categoria']] ?? 'General' }})
                                    </span>
                                    <strong>{{ $reporteSeleccionado['label'] }}</strong>
                                </div>
                                <button type="button" wire:click="$set('tipo_reporte', '')" class="button secondary">
                                    Cambiar
                                </button>
                            </div>
                        @else
                            <div class="rm-cat-tabs">
                                <button type="button" wire:click="$set('reportCategoryTab', 'todos')"
                                    class="rm-cat-btn {{ $categoriaActiva === 'todos' ? 'is-active' : '' }}">
                                    Todos
                                </button>
                                <button type="button" wire:click="$set('reportCategoryTab', 'clientes')"
                                    class="rm-cat-btn {{ $categoriaActiva === 'clientes' ? 'is-active' : '' }}">
                                    Clientes
                                </button>
                                <button type="button" wire:click="$set('reportCategoryTab', 'mensualizados')"
                                    class="rm-cat-btn {{ $categoriaActiva === 'mensualizados' ? 'is-active' : '' }}">
                                    Mensualizados
                                </button>
                                <button type="button" wire:click="$set('reportCategoryTab', 'varios')"
                                    class="rm-cat-btn {{ $categoriaActiva === 'varios' ? 'is-active' : '' }}">
                                    Varios
                                </button>
                            </div>

                            <div class="rm-city-list">
                                @foreach ($reportesAgrupados as $catKey => $itemsCat)
                                    <div class="rm-cat-section-title">
                                        {{ $etiquetasCategoria[$catKey] ?? ucfirst($catKey) }}
                                    </div>
                                    @foreach ($itemsCat as $rep)
                                        @php $isRepSel = ($tipo_reporte ?? '') === $rep['value']; @endphp
                                        <button type="button" wire:key="rep-{{ $rep['value'] }}"
                                            wire:click="$set('tipo_reporte', @js($rep['value']))"
                                            class="rm-city-btn {{ $isRepSel ? 'is-selected' : '' }}">
                                            <span>{{ $rep['label'] }}</span>
                                            <span style="font-size: 11px; color: #1d4ed8;">Seleccionar</span>
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif

                        @error('tipo_reporte')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rm-field">
                        <label for="fecha_inicio">Fecha inicial *</label>
                        <input wire:model.live="fecha_inicio" id="fecha_inicio" type="date"
                            max="{{ date('Y-m-d') }}" class="control">
                        @error('fecha_inicio')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rm-field">
                        <label for="fecha_final">Fecha final *</label>
                        <input wire:model.live="fecha_final" id="fecha_final" type="date" min="{{ $fecha_inicio }}"
                            max="{{ date('Y-m-d') }}" class="control">
                        @error('fecha_final')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            @php $b2Enabled = ($activeFilter ?? '') === 'cliente'; @endphp
            <div class="rm-card {{ $b2Enabled ? '' : 'is-disabled' }}">
                <div class="rm-card-header">
                    <div>
                        <span class="rm-card-code">Bloque 02</span>
                        <h3>Cliente / Remitente</h3>
                        <p>
                            {{ $b2Enabled ? 'Busca y selecciona el cliente o remitente (nom_remitente).' : 'Se habilita al elegir un reporte de Clientes.' }}
                        </p>
                    </div>
                    <span class="rm-badge {{ $b2Enabled ? 'is-active' : '' }}">
                        {{ $b2Enabled ? 'Habilitado' : 'Inhabilitado' }}
                    </span>
                </div>

                @if (!$b2Enabled)
                    <div class="rm-locked-msg">
                        Selecciona un reporte de Clientes en el Bloque 01 para habilitar este bloque.
                    </div>
                @elseif (($nom_remitente ?? '') !== '')
                    <div class="rm-selected-card">
                        <div>
                            <span>Cliente seleccionado</span>
                            <strong>{{ $nom_remitente }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" wire:click="clearCliente" class="button secondary">
                                Cambiar
                            </button>
                            <button type="button" wire:click="clearCliente" class="rm-link-clear">
                                Quitar selección
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rm-field">
                        <div class="rm-row-between">
                            <label for="customerSearch">Buscar cliente / remitente *</label>
                            @if (trim($customerSearch ?? '') !== '')
                                <button type="button" wire:click="$set('customerSearch', '')" class="rm-link-clear">
                                    Limpiar búsqueda
                                </button>
                            @endif
                        </div>

                        <input wire:model.live.debounce.250ms="customerSearch" id="customerSearch" type="search"
                            class="control" placeholder="Escribe para buscar cliente o remitente..."
                            autocomplete="off">

                        <span wire:loading wire:target="customerSearch" class="rm-search-hint">
                            Buscando coincidencias...
                        </span>

                        @if ($listaClientes->isNotEmpty())
                            <div class="rm-city-list" wire:loading.remove wire:target="customerSearch">
                                @foreach ($listaClientes as $clientName)
                                    <button type="button" wire:key="cli-{{ md5($clientName) }}"
                                        wire:click="selectCliente(@js($clientName))" class="rm-city-btn">
                                        <span>{{ $clientName }}</span>
                                        <span style="font-size: 11px; color: #1d4ed8;">Seleccionar</span>
                                    </button>
                                @endforeach
                            </div>
                        @elseif (mb_strlen(trim($customerSearch ?? '')) >= 1)
                            <span wire:loading.remove wire:target="customerSearch" class="rm-field-error">
                                Sin coincidencias para "{{ $customerSearch }}".
                            </span>
                        @endif

                        @error('nom_remitente')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>

            @php $b3Enabled = in_array(($activeFilter ?? ''), ['origen', 'destino'], true); @endphp
            <div class="rm-card {{ $b3Enabled ? '' : 'is-disabled' }}">
                <div class="rm-card-header">
                    <div>
                        <span class="rm-card-code">Bloque 03</span>
                        <h3>Sucursal de Origen y Ciudades de Destino</h3>
                        <p>
                            @if (($isSecretaria ?? false) && $b3Enabled)
                                Consulta restringida a tu ciudad asignada ({{ $ciudadSecretaria }}).
                            @elseif ($b3Enabled)
                                Busca y selecciona la sucursal de origen o las ciudades de destino.
                            @else
                                Se habilita al elegir reportes de Mensualizados o Varios con origen/destino.
                            @endif
                        </p>
                    </div>
                    <span class="rm-badge {{ $b3Enabled ? 'is-active' : '' }}">
                        {{ $b3Enabled ? 'Habilitado' : 'Inhabilitado' }}
                    </span>
                </div>

                @if (!$b3Enabled)
                    <div class="rm-locked-msg">
                        Selecciona un reporte con sucursal de origen o ciudades de destino en el Bloque 01.
                    </div>
                @elseif (($activeFilter ?? '') === 'origen')
                    <div class="rm-field">
                        <div class="rm-row-between">
                            <label for="branchSearch">Sucursal de origen *</label>
                            @if (!($isSecretaria ?? false) && ($ciudad_origen ?? '') !== '')
                                <button type="button" wire:click="$set('ciudad_origen', '')" class="rm-link-clear">
                                    Quitar selección
                                </button>
                            @endif
                        </div>

                        @if ($isSecretaria ?? false)
                            <div class="rm-selected-card">
                                <div>
                                    <span>Agencia asignada (rol secretaría — fija)</span>
                                    <strong>{{ $sucursalSecretariaNombre ?: $ciudadSecretaria }}</strong>
                                </div>
                                <span class="rm-badge is-active">Bloqueada</span>
                            </div>
                        @elseif ($sucursalSeleccionada)
                            <div class="rm-selected-card">
                                <div>
                                    <span>Sucursal seleccionada</span>
                                    <strong>{{ $sucursalSeleccionada['label'] }}</strong>
                                </div>
                                <button type="button" wire:click="$set('ciudad_origen', '')"
                                    class="button secondary">
                                    Cambiar
                                </button>
                            </div>
                        @else
                            <input wire:model.live.debounce.150ms="branchSearch" id="branchSearch" type="search"
                                class="control" placeholder="Buscar sucursal..." autocomplete="off">

                            <div class="rm-city-list">
                                @forelse ($sucursalesFiltradas as $sucItem)
                                    @php $isSucSel = ($ciudad_origen ?? '') === $sucItem['value']; @endphp
                                    <button type="button" wire:key="suc-{{ md5($sucItem['value']) }}"
                                        wire:click="$set('ciudad_origen', @js($sucItem['value']))"
                                        class="rm-city-btn {{ $isSucSel ? 'is-selected' : '' }}">
                                        <span>{{ $sucItem['label'] }}</span>
                                        <span>{{ $isSucSel ? 'Activa' : 'Seleccionar' }}</span>
                                    </button>
                                @empty
                                    <div class="rm-locked-msg">Sin sucursales coincidentes.</div>
                                @endforelse
                            </div>
                        @endif

                        @error('ciudad_origen')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                @elseif (($activeFilter ?? '') === 'destino')
                    <div class="rm-field">
                        <div class="rm-row-between">
                            <label for="destinationCitySearch">Ciudades de destino *</label>
                            @if (!($isSecretaria ?? false) && !empty($ciudad_destino))
                                <button type="button" wire:click="clearCiudadesDestino" class="rm-link-clear">
                                    Quitar selección ({{ count($ciudad_destino) }})
                                </button>
                            @endif
                        </div>

                        @if ($isSecretaria ?? false)
                            <div class="rm-selected-card">
                                <div>
                                    <span>Ciudad asignada (rol secretaría — fija)</span>
                                    <strong>{{ $ciudadSecretaria }}</strong>
                                </div>
                                <span class="rm-badge is-active">Bloqueada</span>
                            </div>
                        @else
                            @if (!empty($ciudad_destino))
                                <div class="rm-selected-card">
                                    <div>
                                        <span>Ciudades seleccionadas ({{ count($ciudad_destino) }})</span>
                                        <strong>{{ implode(', ', $ciudad_destino) }}</strong>
                                    </div>
                                    <button type="button" wire:click="clearCiudadesDestino"
                                        class="button secondary">
                                        Cambiar
                                    </button>
                                </div>

                                <div class="rm-chips-bar">
                                    @foreach ($ciudad_destino as $selCity)
                                        <span class="rm-chip-tag">
                                            <span>{{ $selCity }}</span>
                                            <button type="button"
                                                wire:click="removeCiudadDestino(@js($selCity))">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <input wire:model.live.debounce.150ms="destinationCitySearch" id="destinationCitySearch"
                                type="search" class="control" placeholder="Buscar ciudad de destino..."
                                autocomplete="off">

                            <div class="rm-city-list">
                                @forelse ($listaCiudades as $city)
                                    @php $isSelected = in_array($city, $ciudad_destino ?? [], true); @endphp
                                    <button type="button" wire:key="city-{{ md5($city) }}"
                                        wire:click="toggleCiudadDestino(@js($city))"
                                        class="rm-city-btn {{ $isSelected ? 'is-selected' : '' }}">
                                        <span>{{ $city }}</span>
                                        <span>{{ $isSelected ? 'Activa' : 'Seleccionar' }}</span>
                                    </button>
                                @empty
                                    <div class="rm-locked-msg">Sin ciudades coincidentes.</div>
                                @endforelse
                            </div>
                        @endif

                        @error('ciudad_destino')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>

            @php $b4Enabled = ($activeFilter ?? '') === 'usuario'; @endphp
            <div class="rm-card {{ $b4Enabled ? '' : 'is-disabled' }}">
                <div class="rm-card-header">
                    <div>
                        <span class="rm-card-code">Bloque 04</span>
                        <h3>Usuario de Caja</h3>
                        <p>
                            {{ $b4Enabled ? 'Busca y selecciona el usuario para el cierre de caja diario (id_users).' : 'Se habilita al elegir Cierre de caja diario por usuario.' }}
                        </p>
                    </div>
                    <span class="rm-badge {{ $b4Enabled ? 'is-active' : '' }}">
                        {{ $b4Enabled ? 'Habilitado' : 'Inhabilitado' }}
                    </span>
                </div>

                @if (!$b4Enabled)
                    <div class="rm-locked-msg">
                        Selecciona "Cierre de caja diario por usuario" en el Bloque 01 para habilitar este bloque.
                    </div>
                @else
                    <div class="rm-field">
                        <div class="rm-row-between">
                            <label for="userSearch">Buscar usuario de caja *</label>
                            @if (($id_users ?? '') !== '')
                                <button type="button" wire:click="$set('id_users', '')" class="rm-link-clear">
                                    Quitar selección
                                </button>
                            @endif
                        </div>

                        @if ($usuarioSeleccionado)
                            <div class="rm-selected-card">
                                <div>
                                    <span>Usuario seleccionado</span>
                                    <strong>{{ $usuarioSeleccionado['name'] }}</strong>
                                </div>
                                <button type="button" wire:click="$set('id_users', '')" class="button secondary">
                                    Cambiar
                                </button>
                            </div>
                        @else
                            <input wire:model.live.debounce.150ms="userSearch" id="userSearch" type="search"
                                class="control" placeholder="Buscar usuario por nombre..." autocomplete="off">

                            <div class="rm-city-list">
                                @forelse ($usuariosFiltrados as $uItem)
                                    @php $isUserSel = (string) ($id_users ?? '') === $uItem['id']; @endphp
                                    <button type="button" wire:key="usr-{{ $uItem['id'] }}"
                                        wire:click="$set('id_users', @js($uItem['id']))"
                                        class="rm-city-btn {{ $isUserSel ? 'is-selected' : '' }}">
                                        <span>{{ $uItem['name'] }}</span>
                                        <span>{{ $isUserSel ? 'Activo' : 'Seleccionar' }}</span>
                                    </button>
                                @empty
                                    <div class="rm-locked-msg">Sin usuarios coincidentes.</div>
                                @endforelse
                            </div>
                        @endif

                        @error('id_users')
                            <span class="rm-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
