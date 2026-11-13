<?php

namespace App\Models\Scopes;

use App\Models\Cabecera;
use App\Models\Detalle;
use App\Models\HistorialEstadoGuia;
use App\Models\Manifiesto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AssignedBranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isBranchRestricted()) {
            return;
        }

        $branchId = $user->id_sucursal ?? session('id_sucursal');

        if ($branchId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        match (true) {
            $model instanceof Cabecera,
            $model instanceof Sucursal,
            $model instanceof Vehiculo,
            $model instanceof Manifiesto => $builder->where($model->qualifyColumn('id_sucursal'), $branchId),
            $model instanceof Detalle => $builder->whereHas(
                'cabecera',
                fn (Builder $query) => $query->where('id_sucursal', $branchId),
            ),
            $model instanceof HistorialEstadoGuia => $builder->whereHas(
                'guia',
                fn (Builder $query) => $query->where('id_sucursal', $branchId),
            ),
            default => null,
        };
    }
}
