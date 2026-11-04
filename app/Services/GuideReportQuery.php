<?php

namespace App\Services;

use App\Models\Cabecera;
use Illuminate\Database\Eloquent\Builder;

class GuideReportQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Cabecera>
     */
    public function build(array $filters): Builder
    {
        $reportType = $filters['tipo'] ?? 'clientes';
        $dateField = $reportType === 'cierres_diarios' ? 'fecha_cancelacion' : 'fecha_emision';

        $query = Cabecera::query()
            ->with(['persona', 'sucursal.ciudad', 'usuario', 'formaPago'])
            ->withCount('detalles')
            ->whereDate($dateField, '>=', $filters['fecha_desde'])
            ->whereDate($dateField, '<=', $filters['fecha_hasta']);

        if ($reportType === 'anuladas') {
            $query->withTrashed()->where(function (Builder $query): void {
                $query->where('estado', '0')->orWhereNotNull('deleted_at');
            });
        } else {
            $query->where('estado', '1');
        }

        if (! empty($filters['cliente'])) {
            $query->where('id_persona', $filters['cliente']);
        }

        if (! empty($filters['sucursal'])) {
            $query->where('id_sucursal', $filters['sucursal']);
        }

        if (! empty($filters['usuario'])) {
            $query->where('id_users', $filters['usuario']);
        }

        if (! empty($filters['ciudad_origen'])) {
            $query->where('ciudad_origen', $filters['ciudad_origen']);
        }

        if (! empty($filters['ciudad_destino'])) {
            $query->where('ciudad_destino', $filters['ciudad_destino']);
        }

        if (in_array($reportType, ['manifiestos_origen', 'manifiestos_destino'], true)) {
            $query->whereHas('manifiestos');
        }

        match ($reportType) {
            'por_facturar' => $query->where('id_forma_pago', 3)->whereRaw('UPPER(estatus_cobro) = ?', ['PENDIENTE']),
            'por_facturar_canceladas' => $query->where('id_forma_pago', 3)->whereRaw('UPPER(estatus_cobro) = ?', ['CANCELADO']),
            'por_cobrar' => $query->where('id_forma_pago', '!=', 2)->whereRaw('UPPER(estatus_cobro) = ?', ['PENDIENTE']),
            'por_pagar' => $query->where('id_forma_pago', 2)->whereRaw('UPPER(estatus_cobro) = ?', ['PENDIENTE']),
            'cierres_diarios' => $query->where('id_forma_pago', 1)->whereNotNull('fecha_cancelacion'),
            'clientes', 'anuladas', 'manifiestos_origen', 'manifiestos_destino' => $query,
            default => $query,
        };

        return $query->orderByDesc($dateField)->orderByDesc('id_cabecera');
    }

    /**
     * @return array<string, string>
     */
    public function reportTypes(): array
    {
        return [
            'clientes' => 'Reporte de clientes',
            'por_facturar' => 'Guías por facturar',
            'por_facturar_canceladas' => 'Guías por facturar canceladas',
            'por_cobrar' => 'Guías por cobrar',
            'por_pagar' => 'Guías por pagar',
            'anuladas' => 'Guías anuladas',
            'cierres_diarios' => 'Cierres de caja diarios',
            'manifiestos_origen' => 'Manifiestos por ciudad de origen',
            'manifiestos_destino' => 'Manifiestos por ciudad de destino',
        ];
    }
}
