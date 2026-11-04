<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { color: #17243b; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        p { color: #536176; margin: 4px 0; }
        table { border-collapse: collapse; margin-top: 16px; width: 100%; }
        th, td { border: 1px solid #d9e0ea; padding: 6px; text-align: left; }
        th { background: #edf4ff; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Período: {{ $filters['fecha_desde'] }} — {{ $filters['fecha_hasta'] }}</p>
    <p>Generado: {{ now()->format('d/m/Y H:i') }} · Registros: {{ $guides->count() }}</p>
    <table>
        <thead><tr><th>Guía</th><th>Fecha</th><th>Origen</th><th>Destino</th><th>Cliente / remitente</th><th>Destinatario</th><th>Sucursal</th><th>Usuario</th><th>Cobro</th><th>Valor</th></tr></thead>
        <tbody>
            @foreach ($guides as $guide)
                <tr>
                    <td>{{ $guide->num_guia }}</td>
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
            @endforeach
        </tbody>
        <tfoot><tr><td colspan="9">Total del reporte</td><td>${{ number_format((float) $total, 2) }}</td></tr></tfoot>
    </table>
</body>
</html>
