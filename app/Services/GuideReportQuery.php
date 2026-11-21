<?php

namespace App\Services;

use App\Models\Cabecera;
use App\Models\Persona;
use App\Models\Scopes\AssignedBranchScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GuideReportQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Cabecera>
     */
    public function build(array $filters, ?User $actor = null): Builder
    {
        $reportType = $filters['tipo'] ?? 'clientes';
        $dateField = 'fecha_emision';

        $query = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->with([
                'persona',
                'sucursal' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class)->with('ciudad'),
                'usuario',
                'formaPago',
            ])
            ->withCount('detalles')
            ->withSum([
                'detalles' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class),
            ], 'cantidad')
            ->whereDate($dateField, '>=', $filters['fecha_desde'])
            ->whereDate($dateField, '<=', $filters['fecha_hasta']);

        if ($reportType === 'anuladas') {
            $query->withTrashed()->where(function (Builder $query): void {
                $query->where('estado', '0')->orWhereNotNull('deleted_at');
            });
        } else {
            $query->where('estado', '1');
        }

        if ($actor?->isBranchRestricted()) {
            $query->where('id_sucursal', $actor->id_sucursal);
        } elseif (! empty($filters['sucursal'])) {
            $query->where('id_sucursal', $filters['sucursal']);
        }

        $clienteId = ! empty($filters['cliente']) ? (int) $filters['cliente'] : null;
        $clienteTexto = trim((string) ($filters['cliente_nombre'] ?? ''));

        if ($clienteId !== null || $clienteTexto !== '') {
            $persona = null;
            if ($clienteId !== null) {
                $persona = Persona::query()
                    ->where('tipo_persona', 'C')
                    ->find($clienteId, ['id_persona', 'nombre', 'num_dni']);
            }

            $query->where(function (Builder $sub) use ($clienteId, $clienteTexto, $persona): void {
                if ($clienteId !== null) {
                    $sub->orWhere('id_persona', $clienteId);
                }

                $nombres = array_values(array_unique(array_filter([
                    $clienteTexto !== '' ? $clienteTexto : null,
                    $persona?->nombre,
                ])));

                foreach ($nombres as $termino) {
                    $like = "%{$termino}%";
                    $sub->orWhere('nom_remitente', 'like', $like)
                        ->orWhere('nom_destinatario', 'like', $like)
                        ->orWhere('dni_remitente', 'like', $like)
                        ->orWhere('dni_destinatario', 'like', $like);
                }
            });
        }

        if (! empty($filters['usuario'])) {
            $query->where('id_users', $filters['usuario']);
        }

        if (! empty($filters['ciudad_origen'])) {
            $query->where('ciudad_origen', $filters['ciudad_origen']);
        }

        if (! empty($filters['ciudad_destino'])) {
            if (is_array($filters['ciudad_destino'])) {
                $destinos = array_values(array_filter(
                    $filters['ciudad_destino'],
                    fn ($v) => is_string($v) && $v !== ''
                ));
                if ($destinos !== []) {
                    $query->whereIn('ciudad_destino', $destinos);
                }
            } elseif (is_string($filters['ciudad_destino']) && $filters['ciudad_destino'] !== '') {
                $query->where('ciudad_destino', $filters['ciudad_destino']);
            }
        }

        match ($reportType) {
            'por_facturar' => $query->where('id_forma_pago', 3),
            'por_facturar_canceladas' => $query->where('id_forma_pago', 3)
                ->whereRaw('UPPER(TRIM(COALESCE(estatus_cobro, ""))) = ?', ['CANCELADO']),
            'por_facturar_por_pagar' => $query->where('id_forma_pago', 3)
                ->whereRaw('UPPER(TRIM(COALESCE(estatus_cobro, ""))) = ?', ['PENDIENTE']),
            'por_pagar', 'por_cobrar' => $query->where('id_forma_pago', 2)
                ->whereRaw('UPPER(TRIM(COALESCE(estatus_cobro, ""))) = ?', ['PENDIENTE']),
            'pagadas_cobro' => $query->where('id_forma_pago', 2)
                ->whereRaw('UPPER(TRIM(COALESCE(estatus_cobro, ""))) = ?', ['CANCELADO']),
            'cierres_diarios' => $query->where('id_forma_pago', 1),
            'clientes', 'anuladas', 'manifiestos_origen', 'manifiestos_destino' => $query,
            default => $query,
        };

        return $query->orderBy($dateField)->orderBy('id_cabecera');
    }

    /**
     * @return array<string, string>
     */
    public function reportTypes(): array
    {
        return [
            'clientes' => 'Reporte de clientes (todas las formas de pago)',
            'por_facturar' => 'Guías por facturar',
            'por_facturar_canceladas' => 'Guías por facturar canceladas',
            'por_facturar_por_pagar' => 'Guías por facturar por pagar',
            'por_pagar' => 'Guías pendientes de pago al cobro',
            'pagadas_cobro' => 'Guías pagadas al cobro',
            'anuladas' => 'Guías anuladas',
            'cierres_diarios' => 'Cierres de caja diarios',
            'manifiestos_origen' => 'Manifiestos por ciudad de origen',
            'manifiestos_destino' => 'Manifiestos por ciudad de destino',
        ];
    }
}