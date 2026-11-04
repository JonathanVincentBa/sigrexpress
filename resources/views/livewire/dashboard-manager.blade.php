<div>
    <section class="welcome">
        <div>
            <p class="eyebrow">Dashboard gerencial</p>
            <h1>Hola, {{ auth()->user()->name }}</h1>
            <p class="welcome-copy">Indicadores operativos y financieros del período actual.</p>
        </div>
        <div class="date-chip">{{ ucfirst(now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}</div>
    </section>

    <section class="stats" aria-label="Indicadores del mes">
        <article class="stat"><div class="stat-label">Guías emitidas este mes</div><div class="stat-value">{{ number_format($metrics['guias']) }}</div></article>
        <article class="stat"><div class="stat-label">Valor de guías emitidas</div><div class="stat-value">${{ number_format($metrics['ingresos'], 2) }}</div></article>
        <article class="stat"><div class="stat-label">Valor pendiente de cobro</div><div class="stat-value">${{ number_format($metrics['pendiente'], 2) }}</div></article>
        <article class="stat"><div class="stat-label">Vehículos activos</div><div class="stat-value">{{ number_format($metrics['flota']) }}</div></article>
    </section>

    <div class="metric-grid">
        <section class="panel" style="padding:20px;">
            <div class="section-head" style="margin-top:0;">
                <div><h2>Volumen de guías · últimos 6 meses</h2><p>Guías activas según fecha de emisión.</p></div>
            </div>
            <div style="display:grid;gap:14px;">
                @foreach ($monthly as $month)
                    <div wire:key="month-{{ $loop->index }}" style="display:grid;grid-template-columns:42px 1fr 54px;align-items:center;gap:10px;font-size:12px;">
                        <span style="color:#77849a;text-transform:capitalize;">{{ $month['label'] }}</span>
                        <div style="height:10px;overflow:hidden;border-radius:20px;background:#edf1f7;">
                            <div style="width:{{ min(100, ($month['count'] / $maximumMonthlyCount) * 100) }}%;height:100%;border-radius:20px;background:#2671dc;"></div>
                        </div>
                        <strong style="text-align:right;">{{ number_format($month['count']) }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel" style="padding:20px;">
            <div class="section-head" style="margin-top:0;">
                <div><h2>Valor emitido por sucursal este mes</h2><p>Importe de guías activas registradas durante el mes actual.</p></div>
            </div>
            @forelse ($branchCosts as $branch)
                <div wire:key="branch-value-{{ $branch->id_sucursal }}" style="display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #edf0f5;font-size:12px;">
                    <span>{{ $branch->sucursal?->nombre ?? 'Sin sucursal asignada' }}</span>
                    <strong>${{ number_format((float) $branch->total, 2) }}</strong>
                </div>
            @empty
                <div class="empty-state"><strong>Sin datos para este mes</strong><span>Cuando existan guías, el resumen aparecerá aquí.</span></div>
            @endforelse
        </section>
    </div>

    <section class="panel" style="padding:20px;margin-top:16px;">
        <div class="section-head" style="margin-top:0;">
            <div>
                <h2>Valor histórico emitido por sucursal</h2>
                <p>Acumulado de guías activas de todos los períodos. Los registros sin sucursal quedan separados para no atribuirlos a una agencia incorrectamente.</p>
            </div>
        </div>
        @forelse ($historicalBranchValues as $branch)
            <div wire:key="historical-branch-value-{{ $branch->id_sucursal ?? 'unassigned' }}" style="display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #edf0f5;font-size:12px;">
                <span>{{ $branch->sucursal?->nombre ?? 'Sin sucursal asignada' }}</span>
                <strong>${{ number_format((float) $branch->total, 2) }}</strong>
            </div>
        @empty
            <div class="empty-state"><strong>Sin valores históricos</strong><span>Cuando existan guías activas, el resumen aparecerá aquí.</span></div>
        @endforelse
    </section>

    <p class="muted" style="margin-top:12px;">Los costos operativos y descuentos no se separan actualmente en el modelo de guías; estos indicadores muestran importes emitidos y pendientes de cobro, no utilidad neta.</p>

    <div class="section-head">
        <div><h2>Accesos rápidos</h2><p>Operación, flota, reportes y mantenimiento.</p></div>
    </div>
    <div class="module-grid">
        <a class="module-card" href="{{ route('resources.cabecera.index') }}"><span class="module-icon">GU</span><span class="module-title">Guías</span><span class="module-description">Emisión y consulta de guías.</span><span class="card-footer"><span class="open-link">Abrir módulo →</span></span></a>
        <a class="module-card" href="{{ route('resources.guia-estado.index') }}"><span class="module-icon">ST</span><span class="module-title">Estados de guía</span><span class="module-description">Trazabilidad e historial de estados.</span><span class="card-footer"><span class="open-link">Actualizar →</span></span></a>
        <a class="module-card" href="{{ route('resources.manifiesto.index') }}"><span class="module-icon">MF</span><span class="module-title">Manifiestos</span><span class="module-description">Asignación de guías, vehículos y choferes.</span><span class="card-footer"><span class="open-link">Gestionar →</span></span></a>
        <a class="module-card" href="{{ route('reports.index') }}"><span class="module-icon">RP</span><span class="module-title">Reportes</span><span class="module-description">Consulta por período, sucursal y usuario.</span><span class="card-footer"><span class="open-link">Ver reportes →</span></span></a>
        <a class="module-card" href="{{ route('resources.vehiculo.index') }}"><span class="module-icon">VH</span><span class="module-title">Vehículos</span><span class="module-description">Mantenimiento de flota.</span><span class="card-footer"><span class="open-link">Administrar →</span></span></a>
        <a class="module-card" href="{{ route('manual.index') }}"><span class="module-icon">?</span><span class="module-title">Manual de ayuda</span><span class="module-description">Consultar el documento de usuario.</span><span class="card-footer"><span class="open-link">Abrir manual →</span></span></a>
    </div>
</div>
