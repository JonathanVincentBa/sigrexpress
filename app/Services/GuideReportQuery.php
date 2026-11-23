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
     * Determina qué bloques de filtros (02, 03, 04) aplica cada tipo de reporte.
     * Si no se ha seleccionado un reporte ($reportType === ''), todos quedan deshabilitados.
     *
     * @return array{bloque_02: bool, bloque_03: bool, bloque_04: bool}
     */
    public function enabledBlocksFor(string $reportType): array
    {
        if (trim($reportType) === '') {
            return [
                'bloque_02' => false,
                'bloque_03' => false,
                'bloque_04' => false,
            ];
        }

        return match ($reportType) {
            'cierres_diarios' => [
                'bloque_02' => false,
                'bloque_03' => false,
                'bloque_04' => true,
            ],
            'manifiestos_origen', 'manifiestos_destino' => [
                'bloque_02' => false,
                'bloque_03' => true,
                'bloque_04' => true,
            ],
            'por_pagar', 'por_cobrar', 'pagadas_cobro' => [
                'bloque_02' => true,
                'bloque_03' => true,
                'bloque_04' => true,
            ],
            'clientes', 'por_facturar', 'por_facturar_canceladas', 'por_facturar_por_pagar', 'anuladas' => [
                'bloque_02' => true,
                'bloque_03' => false,
                'bloque_04' => true,
            ],
            default => [
                'bloque_02' => false,
                'bloque_03' => false,
                'bloque_04' => false,
            ],
        };
    }

    /**
     * Limpia filtros que pertenecen a bloques deshabilitados para el tipo de reporte.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function sanitizeFiltersForType(array $filters): array
    {
        $reportType = (string) ($filters['tipo'] ?? '');
        $blocks = $this->enabledBlocksFor($reportType);

        if (! $blocks['bloque_02']) {
            $filters['cliente'] = '';
            $filters['cliente_nombre'] = '';
        }

        if (! $blocks['bloque_03']) {
            $filters['sucursal_origen'] = '';
            $filters['ciudad_destino'] = [];
        }

        if (! $blocks['bloque_04']) {
            $filters['sucursal'] = '';
            $filters['usuario'] = '';
        }

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Cabecera>
     */
    public function build(array $filters, ?User $actor = null): Builder
    {
        $filters = $this->sanitizeFiltersForType($filters);
        $reportType = (string) ($filters['tipo'] ?? '');
        $dateField = 'fecha_emision';

        $query = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->select([
                'id_cabecera',
                'num_guia',
                'fecha_emision',
                'ciudad_origen',
                'ciudad_destino',
                'id_persona',
                'nom_remitente',
                'dni_remitente',
                'nom_destinatario',
                'dni_destinatario',
                'id_sucursal',
                'id_users',
                'id_forma_pago',
                'estatus_cobro',
                'valor_guia',
                'estado',
                'deleted_at',
            ])
            ->with([
                'persona:id_persona,nombre,num_dni',
                'sucursal' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class)->select(['id_sucursal', 'id_ciudad', 'nombre']),
                'usuario:id,name',
            ])
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

        if (! empty($filters['sucursal_origen'])) {
            $query->where('id_sucursal', (int) $filters['sucursal_origen']);
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