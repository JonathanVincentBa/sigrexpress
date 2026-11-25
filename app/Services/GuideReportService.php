<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuideReportService
{
    /**
     * Obtiene los datos exactos según el tipo de reporte solicitado.
     */
    public function obtenerDatos(string $tipo, array$filtros): array
    {
        $desde =$filtros['fecha_inicio'];
        $hasta =$filtros['fecha_final'];
        $remitente =$filtros['nom_remitente'] ?? null;
        $sucursalFiltro = $filtros['id_sucursal'] ?? $filtros['ciudad_origen'] ?? null;
        $ciudadesDestino =$filtros['ciudad_destino'] ?? [];
        $usuarioId =$filtros['id_users'] ?? null;

        // Base Query segura con relación a sucursal y ciudad de origen
        $query = DB::table('cabecera as c')
            ->join('formas_pago as f', 'c.id_forma_pago', '=', 'f.id_formas_pago')
            ->leftJoin('sucursal as s', 'c.id_sucursal', '=', 's.id_sucursal')
            ->leftJoin('ciudad as cs', 's.id_ciudad', '=', 'cs.id_ciudad')
            ->leftJoin('detalle as d', function ($join) {$join->on('c.id_cabecera', '=', 'd.id_cabecera')
                    ->whereNull('d.deleted_at');
            })
            ->leftJoin('users as u', 'c.id_users', '=', 'u.id')
            ->whereBetween(DB::raw('DATE(c.fecha_emision)'), [$desde,$hasta]);

        // Helper para filtrar por sucursal / agencia registrada en el sistema
        $applySucursalFilter = function ($q) use ($sucursalFiltro) {
            if ($sucursalFiltro === null || $sucursalFiltro === '') {
                return;
            }

            if (is_numeric($sucursalFiltro)) {
                $q->where('c.id_sucursal', (int)$sucursalFiltro);
            } else {
                $q->where(function ($sub) use ($sucursalFiltro) {
                    $sub->where('s.nombre',$sucursalFiltro)
                        ->orWhere('c.ciudad_origen', $sucursalFiltro)
                        ->orWhere('cs.descripcion', $sucursalFiltro);
                });
            }
        };

        // Filtro de estado (solo anuladas usa estado = 0)
        if ($tipo === 'anuladas') {
            $query->where('c.estado', '0'); } else {$query->where('c.estado', '1');
        }

        // Aplicar reglas específicas de cada reporte
        switch ($tipo) {
            // --- BLOQUE CLIENTES ---
            case 'cliente_xfacturar':
                $query->where('c.nom_remitente',$remitente)
                    ->where('c.id_forma_pago', 3);
                break;

            case 'cliente_xfacturar_canceladas':
                $query->where('c.nom_remitente',$remitente)
                    ->where('c.id_forma_pago', 3)
                    ->where('c.estatus_cobro', 'Cancelado');
                break;

            case 'cliente_xfacturar_xpagar':
                $query->where('c.nom_remitente',$remitente)
                    ->where('c.id_forma_pago', 3)
                    ->where('c.estatus_cobro', 'Pendiente');
                break;

            case 'cliente_xfacturar_xpagar_todos':
                $query->where('c.id_forma_pago', 3)
                    ->where('c.estatus_cobro', 'Pendiente');
                break;

            case 'cliente_todas_formas_pago':
                $query->where('c.nom_remitente',$remitente);
                break;

            // --- BLOQUE MANIFIESTOS Y VARIOS ---
            case 'manifiesto_destino':
                $query->whereIn('c.ciudad_destino', (array)$ciudadesDestino);
                break;

            case 'manifiesto_origen':
                $applySucursalFilter($query);
                break;

            case 'cobro_pendiente':
                $query->whereIn('c.ciudad_destino', (array)$ciudadesDestino)
                    ->where('c.id_forma_pago', 2)
                    ->where('c.estatus_cobro', 'Pendiente');
                break;

            case 'cobro_pagado':
                $query->whereIn('c.ciudad_destino', (array)$ciudadesDestino)
                    ->where('c.id_forma_pago', 2)
                    ->where('c.estatus_cobro', 'Cancelado');
                break;

            case 'cierres_diarios':
                $query->where('c.id_users',$usuarioId)
                    ->where('c.id_forma_pago', 1);
                break;

            case 'sucursal_xfacturar_canceladas':
                $applySucursalFilter($query);$query->where('c.id_forma_pago', 3)
                    ->where('c.estatus_cobro', 'Cancelado');
                break;

            case 'sucursal_xfacturar_xpagar':
                $applySucursalFilter($query);$query->where('c.id_forma_pago', 3)
                    ->where('c.estatus_cobro', 'Pendiente');
                break;

            case 'anuladas':
                // Ya filtró estado = '0' arriba
                break;
        }

        // Selección agrupada compatible con MySQL 8+ (ONLY_FULL_GROUP_BY)
        $guias =$query->select([
            'c.id_cabecera',
            'c.num_guia',
            'c.fecha_emision',
            'c.ciudad_origen',
            'c.ciudad_destino',
            'c.nom_remitente',
            'c.nom_destinatario',
            'c.valor_guia',
            'c.estatus_cobro',
            'c.nota',
            'c.estado',
            'f.descripcion as forma_pago',
            'u.name as usuario_nombre',
            DB::raw('COALESCE(SUM(d.cantidad), 0) as cantidad'),
        ])
            ->groupBy([
                'c.id_cabecera',
                'c.num_guia',
                'c.fecha_emision',
                'c.ciudad_origen',
                'c.ciudad_destino',
                'c.nom_remitente',
                'c.nom_destinatario',
                'c.valor_guia',
                'c.estatus_cobro',
                'c.nota',
                'c.estado',
                'f.descripcion',
                'u.name',
            ])
            ->orderBy('c.fecha_emision', 'asc')
            ->orderBy('c.id_cabecera', 'asc')
            ->get();

        // Resumen agrupado por ruta (para manifiestos)
        $resumenRutas = $guias->groupBy(fn ($item) => $item->ciudad_origen . '\vert{}' .$item->ciudad_destino)
            ->map(function (Collection $grupo) {
                return (object) [
                    'ciudad_origen'  => $grupo->first()->ciudad_origen,
                    'ciudad_destino' => $grupo->first()->ciudad_destino,
                    'total_guias'    => $grupo->count(),
                    'total_bultos'   => $grupo->sum('cantidad'),
                    'total_valor'    => $grupo->sum('valor_guia'),
                ];
            })->values();

        return [
            'guias'        => $guias,
            'total_valor'  => $guias->sum('valor_guia'),
            'total_bultos' => $guias->sum('cantidad'),
            'total_guias'  => $guias->count(),
            'resumenRutas' => $resumenRutas,
        ];
    }
}