<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Manifiesto {{ $manifest->codigo }}</title>
    <style>
        body { color: #17243b; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin: 18px 0 7px; }
        .muted { color: #536176; }
        .meta { border-collapse: collapse; width: 100%; }
        .meta td { padding: 5px 8px; vertical-align: top; }
        table.guides { border-collapse: collapse; margin-top: 10px; width: 100%; }
        .guides th, .guides td { border: 1px solid #d9e0ea; padding: 6px; text-align: left; }
        .guides th { background: #edf4ff; }
        .signature { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature div { border-top: 1px solid #334155; padding-top: 7px; text-align: center; width: 40%; }
    </style>
</head>
<body>
    <h1>MANIFIESTO DE CARGA</h1>
    <p class="muted">SIGRExpress · Documento de despacho</p>
    <table class="meta">
        <tr><td><strong>Código:</strong> {{ $manifest->codigo }}</td><td><strong>Estado:</strong> {{ $manifest->estado }}</td></tr>
        <tr><td><strong>Fecha de salida:</strong> {{ $manifest->fecha_salida?->format('d/m/Y H:i') }}</td><td><strong>Generado por:</strong> {{ $manifest->usuario?->name ?? '—' }}</td></tr>
        <tr><td><strong>Chofer:</strong> {{ $manifest->chofer?->nombre ?? '—' }}</td><td><strong>Identificación:</strong> {{ $manifest->chofer?->num_dni ?? '—' }}</td></tr>
        <tr><td><strong>Vehículo:</strong> {{ $manifest->vehiculo?->placa }} · {{ $manifest->vehiculo?->marca }} {{ $manifest->vehiculo?->modelo }}</td><td><strong>Capacidad:</strong> {{ $manifest->vehiculo?->capacidad }} kg</td></tr>
        @if ($manifest->observacion)
            <tr><td colspan="2"><strong>Observación:</strong> {{ $manifest->observacion }}</td></tr>
        @endif
    </table>
    <h2>Guías asignadas ({{ $manifest->guias->count() }})</h2>
    <table class="guides">
        <thead><tr><th>N.º guía</th><th>Fecha</th><th>Remitente</th><th>Destinatario</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Detalle</th></tr></thead>
        <tbody>
            @foreach ($manifest->guias as $guide)
                <tr>
                    <td>{{ $guide->num_guia }}</td>
                    <td>{{ $guide->fecha_emision?->format('d/m/Y') }}</td>
                    <td>{{ $guide->nom_remitente }}</td>
                    <td>{{ $guide->nom_destinatario }}</td>
                    <td>{{ $guide->ciudad_origen }}</td>
                    <td>{{ $guide->ciudad_destino }}</td>
                    <td>{{ $guide->estado_tracking }}</td>
                    <td>{{ $guide->detalles->pluck('descripcion')->implode(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="signature"><div>Firma del chofer</div><div>Despachador / responsable</div></div>
</body>
</html>
