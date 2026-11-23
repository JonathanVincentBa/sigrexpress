<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CajaUsuario</title>
    <style>
        body { color: #17243b; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 16px; margin: 6px 0; text-align: center; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        th, td { border: 1px solid #d9e0ea; padding: 6px; text-align: left; }
        th { background: #edf4ff; }
        tfoot td { font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1><b>CAJA DE {{ $targetUser->name }}</b></h1>
    <h1><b>FECHA: {{ $fecha }}</b></h1>
    <div class="datagrid">
        <table>
            <thead>
                <tr>
                    <th>N° Guia</th>
                    <th>Ciudad Origen</th>
                    <th>Ciudad destino</th>
                    <th style="width: 15%;">Nombre de Remitente</th>
                    <th style="width: 15%;">Fecha de Creacion</th>
                    <th>Valor de la Guia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($guias as $guia)
                    <tr>
                        <td>{{ $guia->num_guia }}</td>
                        <td>{{ $guia->ciudad_origen }}</td>
                        <td>{{ $guia->ciudad_destino }}</td>
                        <td style="width: 15%;">{{ $guia->nom_remitente }}</td>
                        <td style="width: 15%;">{{ $guia->created_at }}</td>
                        <td class="text-right">{{ number_format((float) $guia->valor_guia, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: left;">Total de dinero de las Guias</td>
                    <td class="text-right">{{ number_format((float) $total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>