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
        $titulos = $this->catalogoTitulos();
        $tituloReporte = $titulos[$tipo] ?? 'Reporte Gerencial';

        $baseNombre = Str::slug(
            $tituloReporte . '_' . $validated['fecha_inicio'] . '_al_' . $validated['fecha_final'],
            '_'
        );

        if ($formato === 'excel') {
            return Excel::download(
                new ReporteGeneralExport($datos['guias'], $tituloReporte, $validated),
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
        $usuarioId = Auth::id();
        $hoy = now()->toDateString();

        $guias = DB::table('cabecera')
            ->whereDate('updated_at', $hoy)
            ->where('id_users_update', $usuarioId)
            ->where('estado', '1')
            ->orderBy('num_guia', 'asc')
            ->get();

        $total = $guias->sum('valor_guia');

        $pdf = Pdf::loadView('reports.pdf.cierre_usuario', [
            'usuario' => Auth::user(),
            'fecha' => $hoy,
            'guias' => $guias,
            'total' => $total,
        ]);

        $nombreCierre = Str::slug('CierreCaja_' . $hoy, '_');

        return $pdf->download("{$nombreCierre}.pdf");
    }

    private function catalogoTitulos(): array
    {
        return [
            'cliente_xfacturar' => 'Guias Por Facturar por Cliente',
            'cliente_xfacturar_canceladas' => 'Guias Por Facturar Canceladas por Cliente',
            'cliente_xfacturar_xpagar' => 'Guias Por Facturar Pendientes por Cliente',
            'cliente_xfacturar_xpagar_todos' => 'Guias Por Facturar Pendientes Todos los Clientes',
            'cliente_todas_formas_pago' => 'Historial Completo por Cliente',
            'manifiesto_destino' => 'Manifiesto por Ciudades de Destino',
            'manifiesto_origen' => 'Manifiesto por Sucursal de Origen',
            'cobro_pendiente' => 'Guias Pendientes de Pago Al Cobro',
            'cobro_pagado' => 'Guias Pagadas Al Cobro',
            'cierres_diarios' => 'Cierre de Caja Diario por Usuario',
            'sucursal_xfacturar_canceladas' => 'Guias Por Facturar Canceladas por Sucursal de Origen',
            'sucursal_xfacturar_xpagar' => 'Guias Por Facturar Pendientes por Sucursal de Origen',
            'anuladas' => 'Reporte de Guias Anuladas',
        ];
    }
}