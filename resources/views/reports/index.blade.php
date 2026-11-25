@extends('components.layouts.app')

@section('title', 'Reportes Gerenciales')

@section('contenido')
    @php
        $reportMeta = [
            'cliente_xfacturar' => ['label' => 'Guías por facturar (por cliente)', 'bloque' => 'cliente'],
            'cliente_xfacturar_canceladas' => [
                'label' => 'Guías por facturar canceladas (por cliente)',
                'bloque' => 'cliente',
            ],
            'cliente_xfacturar_xpagar' => [
                'label' => 'Guías por facturar pendientes de pago (por cliente)',
                'bloque' => 'cliente',
            ],
            'cliente_xfacturar_xpagar_todos' => [
                'label' => 'Guías por facturar pendientes (Todos los clientes)',
                'bloque' => 'ninguno',
            ],
            'cliente_todas_formas_pago' => [
                'label' => 'Historial completo (Todas las formas de pago)',
                'bloque' => 'cliente',
            ],
            'manifiesto_destino' => ['label' => 'Manifiesto por ciudades de destino', 'bloque' => 'destino'],
            'manifiesto_origen' => ['label' => 'Manifiesto por ciudad de origen', 'bloque' => 'origen'],
            'cobro_pendiente' => ['label' => 'Guías pendientes de pago (Al cobro)', 'bloque' => 'destino'],
            'cobro_pagado' => ['label' => 'Guías pagadas (Al cobro)', 'bloque' => 'destino'],
            'cierres_diarios' => ['label' => 'Cierre de caja diario por usuario', 'bloque' => 'usuario'],
            'sucursal_xfacturar_canceladas' => [
                'label' => 'Guías por facturar canceladas (por ciudad origen)',
                'bloque' => 'origen',
            ],
            'sucursal_xfacturar_xpagar' => [
                'label' => 'Guías por facturar pendientes (por ciudad origen)',
                'bloque' => 'origen',
            ],
            'anuladas' => ['label' => 'Reporte de guías anuladas', 'bloque' => 'ninguno'],
        ];
        $tipoActual = old('tipo_reporte', '');
        $oldDestinos = array_map('strval', (array) old('ciudad_destino', []));
    @endphp

    <style>
        .rep-stack {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .rep-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #185abc;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgb(23 36 59 / 4%);
        }

        .rep-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .rep-chip {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            font-size: 12px;
        }

        .rep-chip span {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #77849a;
        }

        .rep-chip strong {
            color: #17243b;
            font-weight: 650;
        }

        .rep-section {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            overflow: visible;
            box-shadow: 0 2px 8px rgb(23 36 59 / 4%);
        }

        .rep-sec-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 22px;
            border-bottom: 1px solid #e8edf4;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .rep-sec-head p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #77849a;
        }

        .rep-blocks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            padding: 20px 22px;
        }

        .rep-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            transition: opacity .12s ease, background .12s ease, border-color .12s ease;
        }

        .rep-card.is-disabled {
            background: #f8fafc;
            opacity: .68;
        }

        .rep-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .rep-tag {
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #185abc;
        }

        .rep-card h3 {
            margin: 2px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #17243b;
        }

        .rep-card-desc {
            margin: 4px 0 0;
            font-size: 12px;
            color: #77849a;
        }

        .rep-pill {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            color: #475569;
            white-space: nowrap;
        }

        .rep-pill.on {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .rep-locked {
            padding: 10px 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: rgb(255 255 255 / 75%);
            font-size: 12px;
            color: #64748b;
        }

        .rep-alert {
            padding: 12px 16px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 12.5px;
            margin-bottom: 16px;
        }

        .rep-alert ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }

        /* Buscador estilo Select2 ligero en JS/CSS puro */
        .s2x-box {
            position: relative;
        }

        .s2x-trigger {
            width: 100%;
            min-height: 38px;
            padding: 6px 34px 6px 12px;
            border: 1px solid #d9e0ea;
            border-radius: 8px;
            background: #fff;
            color: #17243b;
            font-size: 12.5px;
            text-align: left;
            cursor: pointer;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
            position: relative;
            transition: border-color .15s, box-shadow .15s;
        }

        .s2x-trigger:hover {
            border-color: #b8c6d8;
        }

        .s2x-box.is-open .s2x-trigger {
            border-color: #2671dc;
            box-shadow: 0 0 0 3px rgb(38 113 220 / 12%);
        }

        .s2x-placeholder {
            color: #77849a;
        }

        .s2x-selected-text {
            color: #17243b;
            font-weight: 500;
        }

        .s2x-arrow {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            color: #64748b;
            pointer-events: none;
        }

        .s2x-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 11.5px;
            font-weight: 600;
        }

        .s2x-chip-x {
            border: 0;
            background: transparent;
            color: #1d4ed8;
            font-weight: 700;
            cursor: pointer;
            padding: 0 1px;
            line-height: 1;
        }

        .s2x-dropdown {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            z-index: 90;
            border: 1px solid #d9e0ea;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 12px 28px rgb(23 36 59 / 14%);
            overflow: hidden;
        }

        .s2x-box.is-open .s2x-dropdown {
            display: block;
        }

        .s2x-search-wrap {
            padding: 8px;
            border-bottom: 1px solid #edf0f5;
            background: #f8fafc;
        }

        .s2x-search {
            width: 100%;
            height: 34px;
            padding: 6px 10px;
            border: 1px solid #d9e0ea;
            border-radius: 7px;
            background: #fff;
            color: #17243b;
            font-size: 12px;
            outline: none;
        }

        .s2x-search:focus {
            border-color: #2671dc;
        }

        .s2x-list {
            max-height: 220px;
            overflow-y: auto;
            padding: 4px;
        }

        .s2x-group {
            padding: 6px 10px 3px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #77849a;
        }

        .s2x-option {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 7px 10px;
            border-radius: 6px;
            text-align: left;
            font-size: 12px;
            color: #17243b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .s2x-option:hover {
            background: #eff6ff;
            color: #185abc;
        }

        .s2x-option.is-selected {
            background: #185abc;
            color: #fff;
            font-weight: 600;
        }

        .s2x-empty {
            padding: 12px;
            text-align: center;
            font-size: 12px;
            color: #77849a;
        }

        .s2x-native-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .rep-blocks {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="rep-stack">
        {{-- Encabezado --}}
        <div class="rep-hero">
            <div>
                <div class="eyebrow" style="margin-bottom: 4px;">
                    Analítica y Control Gerencial
                    <span style="color:#cbd5e1">•</span>
                    <span id="rep-active-label" style="color:#536176; text-transform:none; letter-spacing:0;">
                        {{ $reportMeta[$tipoActual]['label'] ?? 'Selecciona tu reporte' }}
                    </span>
                </div>
                <h1>Centro de Reportes Gerenciales</h1>
                <p class="welcome-copy" style="margin-top: 4px;">
                    Selecciona el tipo de informe y utiliza los bloques habilitados para exportar a PDF o Excel.
                </p>
            </div>

            <div class="rep-chips">
                <div class="rep-chip">
                    <span>Período activo</span>
                    <strong id="rep-period-text">{{ old('fecha_inicio', date('Y-m-01')) }} —
                        {{ old('fecha_final', date('Y-m-d')) }}</strong>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('reports.cierre.usuario') ? route('reports.cierre.usuario') : '#' }}"
                    class="button secondary">
                    Mi Cierre de Caja Hoy
                </a>
            </div>
        </div>

        {{-- Panel de parámetros --}}
        <section class="rep-section">
            <form action="{{ route('reports.generar') }}" method="POST" autocomplete="off" id="form-reportes">
                @csrf

                <div class="rep-sec-head">
                    <div>
                        <h2>Parámetros y criterios de consulta</h2>
                        <p id="rep-helper-text">
                            Selecciona tu reporte para habilitar únicamente los filtros requeridos por la consulta.
                        </p>
                    </div>

                    <div class="heading-actions">
                        <button type="submit" name="formato" value="pdf" class="button danger" id="btn-pdf" disabled>
                            Generar Reporte PDF
                        </button>
                        <button type="submit" name="formato" value="excel" class="button primary" id="btn-excel" disabled>
                            Exportar a Excel
                        </button>
                    </div>
                </div>

                <div style="padding: 20px 22px 0;">
                    @if ($errors->any())
                        <div class="rep-alert">
                            <strong>Revisa los campos obligatorios:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="rep-blocks">
                    {{-- BLOQUE 01: Tipo de reporte y rango de fechas --}}
                    <div class="rep-card" id="card-bloque-01">
                        <div class="rep-card-top">
                            <div>
                                <span class="rep-tag">Bloque 01</span>
                                <h3>Tipo de Reporte y Rango de Fechas</h3>
                                <p class="rep-card-desc">Requerido en todas las consultas de guías, manifiestos, cobros y
                                    cierres.</p>
                            </div>
                            <span class="rep-pill on">Principal</span>
                        </div>

                        <div class="form-grid">
                            <div class="field field-wide">
                                <label for="tipo_reporte">Tipo de reporte <span style="color:#b42318">*</span></label>
                                <select name="tipo_reporte" id="tipo_reporte" class="s2x-native-hidden"
                                    data-s2x="single" data-placeholder="-- Selecciona tu reporte --" required>
                                    <option value="">-- Selecciona tu reporte --</option>
                                    <optgroup label="Reportes por Cliente">
                                        <option value="cliente_xfacturar" data-filtro="cliente"
                                            {{ $tipoActual === 'cliente_xfacturar' ? 'selected' : '' }}>Guías por facturar
                                            (por cliente)</option>
                                        <option value="cliente_xfacturar_canceladas" data-filtro="cliente"
                                            {{ $tipoActual === 'cliente_xfacturar_canceladas' ? 'selected' : '' }}>Guías por
                                            facturar canceladas (por cliente)</option>
                                        <option value="cliente_xfacturar_xpagar" data-filtro="cliente"
                                            {{ $tipoActual === 'cliente_xfacturar_xpagar' ? 'selected' : '' }}>Guías por
                                            facturar pendientes de pago (por cliente)</option>
                                        <option value="cliente_xfacturar_xpagar_todos" data-filtro="ninguno"
                                            {{ $tipoActual === 'cliente_xfacturar_xpagar_todos' ? 'selected' : '' }}>Guías
                                            por facturar pendientes (Todos los clientes)</option>
                                        <option value="cliente_todas_formas_pago" data-filtro="cliente"
                                            {{ $tipoActual === 'cliente_todas_formas_pago' ? 'selected' : '' }}>Historial
                                            completo (Todas las formas de pago)</option>
                                    </optgroup>
                                    <optgroup label="Manifiestos y Control Al Cobro">
                                        <option value="manifiesto_destino" data-filtro="destino"
                                            {{ $tipoActual === 'manifiesto_destino' ? 'selected' : '' }}>Manifiesto por
                                            ciudades de destino</option>
                                        <option value="manifiesto_origen" data-filtro="origen"
                                            {{ $tipoActual === 'manifiesto_origen' ? 'selected' : '' }}>Manifiesto por
                                            ciudad de origen</option>
                                        <option value="cobro_pendiente" data-filtro="destino"
                                            {{ $tipoActual === 'cobro_pendiente' ? 'selected' : '' }}>Guías pendientes de
                                            pago (Al cobro)</option>
                                        <option value="cobro_pagado" data-filtro="destino"
                                            {{ $tipoActual === 'cobro_pagado' ? 'selected' : '' }}>Guías pagadas (Al cobro)
                                        </option>
                                    </optgroup>
                                    <optgroup label="Control de Sucursales, Caja y Auditoría">
                                        <option value="cierres_diarios" data-filtro="usuario"
                                            {{ $tipoActual === 'cierres_diarios' ? 'selected' : '' }}>Cierre de caja diario
                                            por usuario</option>
                                        <option value="sucursal_xfacturar_canceladas" data-filtro="origen"
                                            {{ $tipoActual === 'sucursal_xfacturar_canceladas' ? 'selected' : '' }}>Guías
                                            por facturar canceladas (por ciudad origen)</option>
                                        <option value="sucursal_xfacturar_xpagar" data-filtro="origen"
                                            {{ $tipoActual === 'sucursal_xfacturar_xpagar' ? 'selected' : '' }}>Guías por
                                            facturar pendientes (por ciudad origen)</option>
                                        <option value="anuladas" data-filtro="ninguno"
                                            {{ $tipoActual === 'anuladas' ? 'selected' : '' }}>Reporte de guías anuladas
                                        </option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="field">
                                <label for="fecha_inicio">Fecha inicial <span style="color:#b42318">*</span></label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="control"
                                    value="{{ old('fecha_inicio', date('Y-m-01')) }}" max="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="field">
                                <label for="fecha_final">Fecha final <span style="color:#b42318">*</span></label>
                                <input type="date" name="fecha_final" id="fecha_final" class="control"
                                    value="{{ old('fecha_final', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE 02: Cliente / Remitente --}}
                    <div class="rep-card is-disabled" id="card-bloque-02">
                        <div class="rep-card-top">
                            <div>
                                <span class="rep-tag">Bloque 02</span>
                                <h3>Cliente / Remitente</h3>
                                <p class="rep-card-desc">Requerido en reportes por cliente (`nom_remitente`).</p>
                            </div>
                            <span class="rep-pill" id="pill-bloque-02">Inhabilitado</span>
                        </div>

                        <div class="rep-locked" id="lock-bloque-02">
                            Selecciona un reporte por cliente en el Bloque 01 para habilitar este filtro.
                        </div>

                        <div class="field" id="wrap-cliente" style="display: none;">
                            <label for="nom_remitente">Seleccione un cliente <span style="color:#b42318">*</span></label>
                            <select name="nom_remitente" id="nom_remitente" class="s2x-native-hidden"
                                data-s2x="single" data-placeholder="-- Busca y selecciona un cliente --">
                                <option value="">-- Busca y selecciona un cliente --</option>
                                @foreach ($personas as $per)
                                    <option value="{{ $per->nom_remitente }}"
                                        {{ old('nom_remitente') === $per->nom_remitente ? 'selected' : '' }}>
                                        {{ $per->nom_remitente }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- BLOQUE 03: Origen y Destinos --}}
                    <div class="rep-card is-disabled" id="card-bloque-03">
                        <div class="rep-card-top">
                            <div>
                                <span class="rep-tag">Bloque 03</span>
                                <h3>Origen y Ciudades de Destino</h3>
                                <p class="rep-card-desc">Requerido según el reporte: ciudad/sucursal de origen o ciudades
                                    de destino.</p>
                            </div>
                            <span class="rep-pill" id="pill-bloque-03">Inhabilitado</span>
                        </div>

                        <div class="rep-locked" id="lock-bloque-03">
                            Selecciona un manifiesto, reporte al cobro o por sucursal en el Bloque 01.
                        </div>

                        <div class="field" id="wrap-origen" style="display: none;">
                            <label for="ciudad_origen">Ciudad / Sucursal de origen <span
                                    style="color:#b42318">*</span></label>
                            <select name="ciudad_origen" id="ciudad_origen" class="s2x-native-hidden"
                                data-s2x="single" data-placeholder="-- Busca ciudad de origen --">
                                <option value="">-- Busca ciudad de origen --</option>
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->descripcion }}"
                                        {{ old('ciudad_origen') === $suc->descripcion ? 'selected' : '' }}>
                                        {{ $suc->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field" id="wrap-destino" style="display: none;">
                            <label for="ciudad_destino">Ciudades de destino (múltiple) <span
                                    style="color:#b42318">*</span></label>
                            <select name="ciudad_destino[]" id="ciudad_destino" class="s2x-native-hidden"
                                data-s2x="multiple" data-placeholder="-- Busca y marca ciudades --" multiple>
                                @foreach ($ciudades as $ciu)
                                    <option value="{{ $ciu->descripcion }}"
                                        {{ in_array((string) $ciu->descripcion, $oldDestinos, true) ? 'selected' : '' }}>
                                        {{ $ciu->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- BLOQUE 04: Usuario de Caja --}}
                    <div class="rep-card is-disabled" id="card-bloque-04">
                        <div class="rep-card-top">
                            <div>
                                <span class="rep-tag">Bloque 04</span>
                                <h3>Usuario de Caja</h3>
                                <p class="rep-card-desc">Requerido para cierre de caja diario por usuario (`id_users`).</p>
                            </div>
                            <span class="rep-pill" id="pill-bloque-04">Inhabilitado</span>
                        </div>

                        <div class="rep-locked" id="lock-bloque-04">
                            Selecciona "Cierre de caja diario por usuario" en el Bloque 01 para habilitar este filtro.
                        </div>

                        <div class="field" id="wrap-usuario" style="display: none;">
                            <label for="id_users">Seleccione un usuario <span style="color:#b42318">*</span></label>
                            <select name="id_users" id="id_users" class="s2x-native-hidden"
                                data-s2x="single" data-placeholder="-- Busca un usuario --">
                                <option value="">-- Busca un usuario --</option>
                                @foreach ($usuarios as $use)
                                    <option value="{{ $use->id }}"
                                        {{ (string) old('id_users') === (string) $use->id ? 'selected' : '' }}>
                                        {{ $use->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const MAX_RENDER = 60;

            function buildSelect2Like(selectEl) {
                if (!selectEl || selectEl.dataset.s2xReady === '1') return null;
                selectEl.dataset.s2xReady = '1';

                const isMultiple = selectEl.dataset.s2x === 'multiple' || selectEl.multiple;
                const placeholder = selectEl.dataset.placeholder || 'Selecciona una opción';

                const items = [];
                Array.from(selectEl.children).forEach(child => {
                    if (child.tagName === 'OPTGROUP') {
                        items.push({ type: 'group', label: child.label });
                        Array.from(child.children).forEach(opt => {
                            items.push({
                                type: 'option',
                                value: opt.value,
                                label: opt.textContent.trim(),
                                searchText: opt.textContent.trim().toLowerCase(),
                                filtro: opt.getAttribute('data-filtro') || ''
                            });
                        });
                    } else if (child.tagName === 'OPTION') {
                        items.push({
                            type: 'option',
                            value: child.value,
                            label: child.textContent.trim(),
                            searchText: child.textContent.trim().toLowerCase(),
                            filtro: child.getAttribute('data-filtro') || ''
                        });
                    }
                });

                const box = document.createElement('div');
                box.className = 's2x-box';

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 's2x-trigger';
                trigger.setAttribute('aria-expanded', 'false');

                const arrow = document.createElement('span');
                arrow.className = 's2x-arrow';
                arrow.textContent = '▼';
                trigger.appendChild(arrow);

                const dropdown = document.createElement('div');
                dropdown.className = 's2x-dropdown';

                const searchWrap = document.createElement('div');
                searchWrap.className = 's2x-search-wrap';

                const searchInput = document.createElement('input');
                searchInput.type = 'search';
                searchInput.className = 's2x-search';
                searchInput.placeholder = 'Escribe para buscar...';
                searchInput.autocomplete = 'off';
                searchWrap.appendChild(searchInput);

                const list = document.createElement('div');
                list.className = 's2x-list';

                dropdown.appendChild(searchWrap);
                dropdown.appendChild(list);
                box.appendChild(trigger);
                box.appendChild(dropdown);

                selectEl.parentNode.insertBefore(box, selectEl.nextSibling);

                function getSelectedValues() {
                    if (isMultiple) {
                        return Array.from(selectEl.selectedOptions).map(o => o.value);
                    }
                    return selectEl.value ? [selectEl.value] : [];
                }

                function setSingleValue(val) {
                    selectEl.value = val;
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    renderTrigger();
                    close();
                }

                function toggleMultiValue(val) {
                    const opt = Array.from(selectEl.options).find(o => o.value === val);
                    if (opt) {
                        opt.selected = !opt.selected;
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                        renderTrigger();
                        renderList(searchInput.value);
                    }
                }

                function renderTrigger() {
                    const selected = getSelectedValues();
                    Array.from(trigger.children).forEach(ch => {
                        if (ch !== arrow) ch.remove();
                    });

                    if (isMultiple) {
                        if (!selected.length) {
                            const ph = document.createElement('span');
                            ph.className = 's2x-placeholder';
                            ph.textContent = placeholder;
                            trigger.insertBefore(ph, arrow);
                        } else {
                            selected.forEach(val => {
                                const found = items.find(it => it.type === 'option' && it.value === val);
                                const chip = document.createElement('span');
                                chip.className = 's2x-chip';
                                chip.textContent = found ? found.label : val;

                                const xBtn = document.createElement('button');
                                xBtn.type = 'button';
                                xBtn.className = 's2x-chip-x';
                                xBtn.textContent = '×';
                                xBtn.addEventListener('click', function(ev) {
                                    ev.stopPropagation();
                                    toggleMultiValue(val);
                                });

                                chip.appendChild(xBtn);
                                trigger.insertBefore(chip, arrow);
                            });
                        }
                    } else {
                        const currentVal = selected[0] || '';
                        const found = items.find(it => it.type === 'option' && it.value === currentVal && it.value !== '');
                        const span = document.createElement('span');
                        if (found) {
                            span.className = 's2x-selected-text';
                            span.textContent = found.label;
                        } else {
                            span.className = 's2x-placeholder';
                            span.textContent = placeholder;
                        }
                        trigger.insertBefore(span, arrow);
                    }
                }

                function renderList(query = '') {
                    const q = query.trim().toLowerCase();
                    const selectedSet = new Set(getSelectedValues());
                    const frag = document.createDocumentFragment();
                    let count = 0;

                    for (let i = 0; i < items.length; i++) {
                        const it = items[i];
                        if (it.type === 'group') {
                            if (!q) {
                                const g = document.createElement('div');
                                g.className = 's2x-group';
                                g.textContent = it.label;
                                frag.appendChild(g);
                            }
                            continue;
                        }

                        if (!isMultiple && it.value === '' && q) continue;
                        if (q && !it.searchText.includes(q)) continue;

                        if (count >= MAX_RENDER) break;
                        count++;

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        const isSel = it.value !== '' && selectedSet.has(it.value);
                        btn.className = 's2x-option' + (isSel ? ' is-selected' : '');
                        btn.textContent = it.label;

                        if (isSel && isMultiple) {
                            const mark = document.createElement('span');
                            mark.textContent = '✓';
                            btn.appendChild(mark);
                        }

                        btn.addEventListener('click', function(ev) {
                            ev.stopPropagation();
                            if (isMultiple) {
                                toggleMultiValue(it.value);
                            } else {
                                setSingleValue(it.value);
                            }
                        });

                        frag.appendChild(btn);
                    }

                    if (!count) {
                        const empty = document.createElement('div');
                        empty.className = 's2x-empty';
                        empty.textContent = 'Sin resultados coincidentes';
                        frag.appendChild(empty);
                    }

                    list.innerHTML = '';
                    list.appendChild(frag);
                }

                function open() {
                    document.querySelectorAll('.s2x-box.is-open').forEach(other => {
                        if (other !== box) other.classList.remove('is-open');
                    });
                    box.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    searchInput.value = '';
                    renderList('');
                    requestAnimationFrame(() => searchInput.focus());
                }

                function close() {
                    box.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                trigger.addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    if (box.classList.contains('is-open')) {
                        close();
                    } else {
                        open();
                    }
                });

                searchInput.addEventListener('input', function() {
                    renderList(searchInput.value);
                });

                searchInput.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Escape') {
                        close();
                        trigger.focus();
                    }
                });

                selectEl.addEventListener('change', function() {
                    renderTrigger();
                });

                renderTrigger();

                return {
                    reset() {
                        if (isMultiple) {
                            Array.from(selectEl.options).forEach(o => { o.selected = false; });
                        } else {
                            selectEl.value = '';
                        }
                        renderTrigger();
                    },
                    close
                };
            }

            document.addEventListener('click', function() {
                document.querySelectorAll('.s2x-box.is-open').forEach(b => b.classList.remove('is-open'));
            });

            function setupReportsView() {
                const container = document.querySelector('.rep-stack');
                if (!container || container.dataset.repInit === '1') return;
                container.dataset.repInit = '1';

                const tipo = document.getElementById('tipo_reporte');
                const btnPdf = document.getElementById('btn-pdf');
                const btnExcel = document.getElementById('btn-excel');
                const activeLabel = document.getElementById('rep-active-label');
                const fechaIni = document.getElementById('fecha_inicio');
                const fechaFin = document.getElementById('fecha_final');
                const periodText = document.getElementById('rep-period-text');

                if (!tipo || !btnPdf || !btnExcel) return;

                const cards = {
                    b2: document.getElementById('card-bloque-02'),
                    b3: document.getElementById('card-bloque-03'),
                    b4: document.getElementById('card-bloque-04'),
                };
                const pills = {
                    b2: document.getElementById('pill-bloque-02'),
                    b3: document.getElementById('pill-bloque-03'),
                    b4: document.getElementById('pill-bloque-04'),
                };
                const locks = {
                    b2: document.getElementById('lock-bloque-02'),
                    b3: document.getElementById('lock-bloque-03'),
                    b4: document.getElementById('lock-bloque-04'),
                };
                const wraps = {
                    cliente: document.getElementById('wrap-cliente'),
                    origen: document.getElementById('wrap-origen'),
                    destino: document.getElementById('wrap-destino'),
                    usuario: document.getElementById('wrap-usuario'),
                };
                const inputs = {
                    cliente: document.getElementById('nom_remitente'),
                    origen: document.getElementById('ciudad_origen'),
                    destino: document.getElementById('ciudad_destino'),
                    usuario: document.getElementById('id_users'),
                };

                const widgets = {};
                [tipo, inputs.cliente, inputs.origen, inputs.destino, inputs.usuario].forEach(el => {
                    if (el) widgets[el.id] = buildSelect2Like(el);
                });

                function setCardState(key, enabled, lockMessage) {
                    if (!cards[key] || !pills[key] || !locks[key]) return;
                    cards[key].classList.toggle('is-disabled', !enabled);
                    pills[key].classList.toggle('on', enabled);
                    pills[key].textContent = enabled ? 'Habilitado' : 'Inhabilitado';
                    locks[key].style.display = enabled ? 'none' : 'block';
                    if (!enabled && lockMessage) {
                        locks[key].textContent = lockMessage;
                    }
                }

                function clearFilterValue(key, keepIfActive) {
                    if (keepIfActive) return;
                    const el = inputs[key];
                    if (!el) return;
                    if (widgets[el.id]) {
                        widgets[el.id].reset();
                    } else if (el.multiple) {
                        Array.from(el.options).forEach(opt => { opt.selected = false; });
                    } else {
                        el.value = '';
                    }
                }

                function sync(resetUnused = true) {
                    const opt = tipo.options[tipo.selectedIndex];
                    const filtro = opt ? opt.getAttribute('data-filtro') : '';
                    const hasType = Boolean(tipo.value);

                    if (activeLabel) {
                        activeLabel.textContent = hasType && opt ? opt.text.trim() : 'Selecciona tu reporte';
                    }

                    btnPdf.disabled = !hasType;
                    btnExcel.disabled = !hasType;

                    Object.values(wraps).forEach(el => { if (el) el.style.display = 'none'; });
                    Object.values(inputs).forEach(el => { if (el) el.required = false; });

                    if (resetUnused) {
                        clearFilterValue('cliente', hasType && filtro === 'cliente');
                        clearFilterValue('origen', hasType && filtro === 'origen');
                        clearFilterValue('destino', hasType && filtro === 'destino');
                        clearFilterValue('usuario', hasType && filtro === 'usuario');
                    }

                    setCardState('b2', false, 'Selecciona un reporte por cliente en el Bloque 01 para habilitar este filtro.');
                    setCardState('b3', false, 'Selecciona un manifiesto, reporte al cobro o por sucursal en el Bloque 01.');
                    setCardState('b4', false, 'Selecciona "Cierre de caja diario por usuario" en el Bloque 01 para habilitar este filtro.');

                    if (!hasType) return;

                    if (filtro === 'cliente') {
                        setCardState('b2', true);
                        if (wraps.cliente) wraps.cliente.style.display = 'block';
                        if (inputs.cliente) inputs.cliente.required = true;
                    } else if (filtro === 'origen') {
                        setCardState('b3', true);
                        if (wraps.origen) wraps.origen.style.display = 'block';
                        if (inputs.origen) inputs.origen.required = true;
                    } else if (filtro === 'destino') {
                        setCardState('b3', true);
                        if (wraps.destino) wraps.destino.style.display = 'block';
                        if (inputs.destino) inputs.destino.required = true;
                    } else if (filtro === 'usuario') {
                        setCardState('b4', true);
                        if (wraps.usuario) wraps.usuario.style.display = 'block';
                        if (inputs.usuario) inputs.usuario.required = true;
                    }
                }

                function syncDates() {
                    if (fechaIni && fechaFin && fechaIni.value) {
                        fechaFin.min = fechaIni.value;
                        if (fechaFin.value && fechaFin.value < fechaIni.value) {
                            fechaFin.value = fechaIni.value;
                        }
                    }
                    if (periodText && fechaIni && fechaFin) {
                        periodText.textContent = `${fechaIni.value || '—'} — ${fechaFin.value || '—'}`;
                    }
                }

                sync(false);
                syncDates();

                tipo.addEventListener('change', () => sync(true));
                fechaIni?.addEventListener('change', syncDates);
                fechaFin?.addEventListener('change', syncDates);

                const form = document.getElementById('form-reportes');
                let clickedFormat = '';
                let isSubmitting = false;

                btnPdf.addEventListener('click', function() {
                    clickedFormat = 'pdf';
                });

                btnExcel.addEventListener('click', function() {
                    clickedFormat = 'excel';
                });

                if (form) {
                    form.addEventListener('submit', function(ev) {
                        if (isSubmitting) {
                            ev.preventDefault();
                            return;
                        }

                        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                            return;
                        }

                        const formatoValue = clickedFormat || (document.activeElement && document.activeElement.value) || 'pdf';

                        let hiddenFormato = form.querySelector('input[name="formato"][type="hidden"]');
                        if (!hiddenFormato) {
                            hiddenFormato = document.createElement('input');
                            hiddenFormato.type = 'hidden';
                            hiddenFormato.name = 'formato';
                            form.appendChild(hiddenFormato);
                        }
                        hiddenFormato.value = formatoValue;

                        isSubmitting = true;

                        if (formatoValue === 'excel') {
                            btnExcel.textContent = 'Exportando a Excel...';
                            btnPdf.textContent = 'Generar Reporte PDF';
                        } else {
                            btnPdf.textContent = 'Generando reporte...';
                            btnExcel.textContent = 'Exportar a Excel';
                        }

                        btnPdf.disabled = true;
                        btnExcel.disabled = true;
                        btnPdf.style.opacity = '0.75';
                        btnExcel.style.opacity = '0.75';
                        btnPdf.style.cursor = 'not-allowed';
                        btnExcel.style.cursor = 'not-allowed';
                    });
                }

                window.addEventListener('pageshow', function() {
                    isSubmitting = false;
                    clickedFormat = '';
                    const hasType = Boolean(tipo && tipo.value);
                    btnPdf.textContent = 'Generar Reporte PDF';
                    btnExcel.textContent = 'Exportar a Excel';
                    btnPdf.disabled = !hasType;
                    btnExcel.disabled = !hasType;
                    btnPdf.style.opacity = '';
                    btnExcel.style.opacity = '';
                    btnPdf.style.cursor = '';
                    btnExcel.style.cursor = '';
                });
            }

            document.addEventListener('DOMContentLoaded', setupReportsView);
            document.addEventListener('livewire:navigated', () => {
                const c = document.querySelector('.rep-stack');
                if (c) c.dataset.repInit = '0';
                setupReportsView();
            });
        })();
    </script>
@endpush