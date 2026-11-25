<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; color: #1e3a8a; font-size: 16px; }
        .meta { font-size: 10px; color: #4b5563; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #1e3a8a; color: #ffffff; padding: 6px; text-align: left; font-size: 10px; }
        td { border-bottom: 1px solid #e5e7eb; padding: 5px 6px; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales { background-color: #eff6ff !important; font-weight: bold; border-top: 2px solid #1e3a8a; }
        .resumen-box { margin-top: 16px; width: 55%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SIGREXPRESS — {{ strtoupper($titulo) }}</h2>
        <div class="meta">
            <strong>Período:</strong> {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filtros['fecha_final'])->format('d/m/Y') }}
            @if(!empty($filtros['nom_remitente'])) | <strong>Cliente:</strong> {{ $filtros['nom_remitente'] }} @endif
            @if(!empty($filtros['ciudad_origen'])) | <strong>Origen:</strong> {{ $filtros['ciudad_origen'] }} @endif
            @if(!empty($filtros['ciudad_destino'])) | <strong>Destinos:</strong> {{ implode(', ', $filtros['ciudad_destino']) }} @endif
            | <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($guias->isEmpty())
        <table>
            <thead>
                <tr>
                    <th>N.º Guía</th>
                    <th>Fecha</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Remitente</th>
                    <th>Destinatario</th>
                    <th>Forma Pago</th>
                    <th>Estado</th>
                    <th>Cant.</th>
                    <th class="text-right">Valor ($)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">No se encontraron registros para los filtros seleccionados.</td>
                </tr>
            </tbody>
        </table>
    @else
        @php
            $chunks = $guias->chunk(40);
        @endphp

        @foreach($chunks as $chunk)
            <table style="page-break-inside: auto; margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>N.º Guía</th>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Forma Pago</th>
                        <th>Estado</th>
                        @if($tipo === 'anuladas')
                            <th>Usuario / Motivo</th>
                        @else
                            <th class="text-center">Cant.</th>
                        @endif
                        <th class="text-right">Valor ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $guia)
                        <tr>
                            <td><strong>{{ $guia->num_guia }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($guia->fecha_emision)->format('d/m/Y') }}</td>
                            <td>{{ $guia->ciudad_origen }}</td>
                            <td>{{ $guia->ciudad_destino }}</td>
                            <td>{{ $guia->nom_remitente }}</td>
                            <td>{{ $guia->nom_destinatario }}</td>
                            <td>{{ $guia->forma_pago }}</td>
                            <td>{{ $guia->estatus_cobro ?? '—' }}</td>
                            @if($tipo === 'anuladas')
                                <td>{{ $guia->usuario_nombre }} — {{ $guia->nota }}</td>
                            @else
                                <td class="text-center">{{ $guia->cantidad }}</td>
                            @endif
                            <td class="text-right">${{ number_format($guia->valor_guia, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($loop->last)
                    <tfoot>
                        <tr class="totales">
                            <td colspan="8" class="text-right">TOTALES ({{ $total_guias }} guías):</td>
                            <td class="text-center">{{ $tipo === 'anuladas' ? '—' : $total_bultos }}</td>
                            <td class="text-right">${{ number_format($total_valor, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @endforeach
    @endif

    @if(in_array($tipo, ['manifiesto_origen', 'manifiesto_destino']) && $resumenRutas->isNotEmpty())
        <div class="resumen-box">
            <h4>Resumen Consolidado por Ruta</h4>
            <table>
                <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th class="text-center">Guías</th>
                        <th class="text-center">Bultos</th>
                        <th class="text-right">Total ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenRutas as $ruta)
                        <tr>
                            <td>{{ $ruta->ciudad_origen }}</td>
                            <td>{{ $ruta->ciudad_destino }}</td>
                            <td class="text-center">{{ $ruta->total_guias }}</td>
                            <td class="text-center">{{ $ruta->total_bultos }}</td>
                            <td class="text-right">${{ number_format($ruta->total_valor, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>