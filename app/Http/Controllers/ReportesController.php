<?php

namespace App\Http\Controllers;

use App\Exports\ReporteGeneralExport;
use App\Services\GuideReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    public function __construct(protected GuideReportService $reportService) {}

    public function index()
    {
        $personas = DB::table('cabecera')
            ->whereNotNull('nom_remitente')
            ->where('nom_remitente', '!=', '')
            ->select('nom_remitente')
            ->distinct()
            ->orderBy('nom_remitente', 'asc')
            ->get();

        $ciudades = DB::table('ciudad')
            ->where('estado', '1')
            ->orderBy('descripcion', 'asc')
            ->get();

        $sucursales = DB::table('sucursal')
            ->where('estado', '1')
            ->whereNull('deleted_at')
            ->whereNotNull('nombre')
            ->where('nombre', '!=', '')
            ->orderBy('nombre', 'asc')
            ->get();

        $usuarios = DB::table('users')
            ->where('estado', '1')
            ->orderBy('name', 'asc')
            ->get();

        return view('reports.index', compact('personas', 'ciudades', 'sucursales', 'usuarios'));
    }

    /**
     * Endpoint único que genera PDF o Excel según el botón presionado.
     */
    public function generar(Request $request)
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(180);

        $validated = $request->validate([
            'tipo_reporte' => 'required|string',
            'formato' => 'required|in:pdf,excel',
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date|after_or_equal:fecha_inicio',
            'nom_remitente' => 'nullable|string|max:150',
            'ciudad_origen' => 'nullable|string|max:100',
            'ciudad_destino' => 'nullable|array',
            'id_users' => 'nullable|integer',
        ], [
            'fecha_final.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $tipo = $validated['tipo_reporte'];
        $formato = $validated['formato'];
        $datos = $this->reportService->obtenerDatos($tipo, $validated);

        // Construimos el título dinámico incluyendo el cliente, ciudad, sucursal o usuario
        $tituloReporte = $this->construirTituloDinamico($tipo, $validated);

        // Generamos un nombre de archivo limpio (sin caracteres raros) para evitar bloqueos del navegador
        $baseNombre = Str::slug(
            $tituloReporte . '_' . $validated['fecha_inicio'] . '_al_' . $validated['fecha_final'],
            '_'
        );

        if ($formato === 'excel') {
            return Excel::download(
                new ReporteGeneralExport($datos['guias'], $tituloReporte, $validated, $datos),
                $baseNombre . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'X-Content-Type-Options' => 'nosniff',
                ]
            );
        }

        $pdf = Pdf::loadView('reports.pdf.plantilla_maestra', [
            'titulo' => $tituloReporte,
            'filtros' => $validated,
            'guias' => $datos['guias'],
            'total_valor' => $datos['total_valor'],
            'total_bultos' => $datos['total_bultos'],
            'total_guias' => $datos['total_guias'],
            'resumenRutas' => $datos['resumenRutas'],
            'tipo' => $tipo,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($baseNombre . '.pdf');
    }

    /**
     * Reporte rápido de cierre de caja del usuario autenticado (Cierre del día)
     */
    public function cierreCajaUsuario()
    {
        $usuario = Auth::user();
        $usuarioId = $usuario->id;
        $hoy = now()->toDateString();

        $guias = DB::table('cabecera')
            ->whereDate('updated_at', $hoy)
            ->where('id_users_update', $usuarioId)
            ->where('estado', '1')
            ->orderBy('num_guia', 'asc')
            ->get();

        $total = $guias->sum('valor_guia');

        $pdf = Pdf::loadView('reports.pdf.cierre_usuario', [
            'usuario' => $usuario,
            'fecha' => $hoy,
            'guias' => $guias,
            'total' => $total,
        ]);

        $nombreCierre = Str::slug('CierreCaja_' . ($usuario->name ?? 'Usuario') . '_' . $hoy, '_');

        return $pdf->download("{$nombreCierre}.pdf");
    }

    /**
     * Agrega el detalle específico (Cliente, Ciudad, Sucursal o Usuario) al título del reporte.
     */
    private function construirTituloDinamico(string $tipo, array $filtros): string
    {
        $titulos = $this->catalogoTitulos();
        $base = $titulos[$tipo] ?? 'Reporte Gerencial';

        // 1. Reportes por Cliente / Remitente
        if (!empty($filtros['nom_remitente'])) {
            return $base . ' - ' . mb_strtoupper(trim($filtros['nom_remitente']));
        }

        // 2. Reportes por Sucursal / Ciudad de Origen
        if (!empty($filtros['ciudad_origen'])) {
            $origen = trim($filtros['ciudad_origen']);
            if (is_numeric($origen)) {
                $sucursalNombre = DB::table('sucursal')->where('id_sucursal', (int) $origen)->value('nombre');
                $origen = $sucursalNombre ?: $origen;
            }
            return $base . ' - ' . mb_strtoupper($origen);
        }

        // 3. Reportes por Ciudades de Destino
        if (!empty($filtros['ciudad_destino']) && is_array($filtros['ciudad_destino'])) {
            $destinos = array_filter(array_map('trim', $filtros['ciudad_destino']));
            if (count($destinos) === 1) {
                return $base . ' - ' . mb_strtoupper(reset($destinos));
            } elseif (count($destinos) > 1 && count($destinos) <= 3) {
                return $base . ' - ' . mb_strtoupper(implode(', ', $destinos));
            } elseif (count($destinos) > 3) {
                return $base . ' - VARIAS CIUDADES (' . count($destinos) . ')';
            }
        }

        // 4. Reportes por Usuario de Caja
        if (!empty($filtros['id_users'])) {
            $usuarioNombre = DB::table('users')->where('id', (int) $filtros['id_users'])->value('name');
            if ($usuarioNombre) {
                return $base . ' - ' . mb_strtoupper(trim($usuarioNombre));
            }
        }

        return $base;
    }

    private function catalogoTitulos(): array
    {
        return [
            'cliente_xfacturar' => 'Guías Por Facturar por Cliente',
            'cliente_xfacturar_canceladas' => 'Guías Por Facturar Canceladas por Cliente',
            'cliente_xfacturar_xpagar' => 'Guías Por Facturar Pendientes por Cliente',
            'cliente_xfacturar_xpagar_todos' => 'Guías Por Facturar Pendientes Todos los Clientes',
            'cliente_todas_formas_pago' => 'Historial Completo por Cliente',
            'manifiesto_destino' => 'Manifiesto por Ciudades de Destino',
            'manifiesto_origen' => 'Manifiesto por Sucursal de Origen',
            'cobro_pendiente' => 'Guías Pendientes de Pago Al Cobro',
            'cobro_pagado' => 'Guías Pagadas Al Cobro',
            'cierres_diarios' => 'Cierre de Caja Diario por Usuario',
            'sucursal_xfacturar_canceladas' => 'Guías Por Facturar Canceladas por Sucursal de Origen',
            'sucursal_xfacturar_xpagar' => 'Guías Por Facturar Pendientes por Sucursal de Origen',
            'anuladas' => 'Reporte de Guías Anuladas',
        ];
    }
}