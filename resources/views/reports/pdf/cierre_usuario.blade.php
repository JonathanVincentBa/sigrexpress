<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja Usuario</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #1e3a8a;
            color: #fff;
            padding: 6px;
            text-align: left;
        }

        td {
            border-bottom: 1px solid #ddd;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3>SIGREXPRESS — Cierre de Caja Diario por Usuario</h3>
    <p><strong>Usuario:</strong> {{ $usuario->name }} | <strong>Fecha:</strong>
        {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>N.º Guía</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Remitente</th>
                <th>Destinatario</th>
                <th class="text-right">Valor ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guias as $guia)
                <tr>
                    <td>{{ $guia->num_guia }}</td>
                    <td>{{ $guia->ciudad_origen }}</td>
                    <td>{{ $guia->ciudad_destino }}</td>
                    <td>{{ $guia->nom_remitente }}</td>
                    <td>{{ $guia->nom_destinatario }}</td>
                    <td class="text-right">${{ number_format($guia->valor_guia, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Sin movimientos registrados hoy.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL RECAUDADO:</th>
                <th class="text-right">${{ number_format($total, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
