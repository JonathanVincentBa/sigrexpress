<?php

namespace App\Http\Controllers;

use App\Exports\ReportesExport;
use App\Models\User;
use App\Services\GuideReportQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function downloadPdf(Request $request, GuideReportQuery $reports): Response
    {
        $filters = $this->validatedFilters($request, $reports);
        $actor = $request->user() instanceof User ? $request->user() : null;
        $query = $reports->build($filters, $actor);

        abort_if($query->count() > 2000, 422, 'El reporte supera 2.000 filas. Reduce el rango o utiliza filtros más específicos.');

        $guides = $query->get();
        $title = $reports->reportTypes()[$filters['tipo']];

        return Pdf::loadView('reports.pdf', [
            'guides' => $guides,
            'title' => $title,
            'filters' => $filters,
            'totalItems' => (int) $guides->sum(fn ($g) => (int) ($g->detalles_sum_cantidad ?? 0)),
            'total' => $guides->sum('valor_guia'),
        ])->setPaper('a4', 'landscape')->download($filters['tipo'].'-'.now()->format('Ymd-His').'.pdf');
    }

    public function downloadExcel(Request $request, GuideReportQuery $reports): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request, $reports);
        $actor = $request->user() instanceof User ? $request->user() : null;
        $query = $reports->build($filters, $actor);

        abort_if($query->count() > 2000, 422, 'El reporte supera 2.000 filas. Reduce el rango o utiliza filtros más específicos.');

        return Excel::download(
            new ReportesExport($query),
            $filters['tipo'].'-'.now()->format('Ymd-His').'.xlsx',
            ExcelWriter::XLSX,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request, GuideReportQuery $reports): array
    {
        $filters = $request->validate([
            'tipo' => ['required', Rule::in(array_keys($reports->reportTypes()))],
            'fecha_desde' => ['required', 'date', 'before_or_equal:fecha_hasta'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'cliente' => ['nullable', 'integer', 'exists:persona,id_persona'],
            'cliente_nombre' => ['nullable', 'string', 'max:100'],
            'ciudad_origen' => ['nullable', 'string', 'exists:ciudad,descripcion'],
            'ciudad_destino' => ['nullable', 'array'],
            'ciudad_destino.*' => ['string', 'exists:ciudad,descripcion'],
            'sucursal' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'usuario' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (Carbon::parse($filters['fecha_desde'])->diffInDays(Carbon::parse($filters['fecha_hasta'])) > 366) {
            throw ValidationException::withMessages([
                'fecha_hasta' => 'El rango de fechas no puede superar 366 días.',
            ]);
        }

        return $filters;
    }
}