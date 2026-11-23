<?php

namespace App\Http\Controllers;

use App\Models\Cabecera;
use App\Models\Scopes\AssignedBranchScope;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CajaReportController extends Controller
{
    public function usuarioPdf(Request $request): Response
    {
        /** @var User $actor */
        $actor = $request->user();
        $fecha = $request->input('fecha', now('America/Guayaquil')->toDateString());

        $validated = validator(['fecha' => $fecha], [
            'fecha' => ['required', 'date'],
        ])->validate();

        $query = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->with([
                'persona',
                'sucursal' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class),
                'formaPago',
            ])
            ->where('estado', '1')
            ->where('id_users_update', $actor->id)
            ->whereDate('fecha_cancelacion', $validated['fecha']);

        if ($actor->isBranchRestricted() && ! empty($actor->id_sucursal)) {
            $query->where('id_sucursal', $actor->id_sucursal);
        }

        $guias = $query->orderBy('fecha_cancelacion')->orderBy('id_cabecera')->get();
        $total = (float) $guias->sum(fn (Cabecera $g) => (float) $g->valor_guia);

        return Pdf::loadView('caja.reportes.usuario', [
            'usuario' => $actor,
            'guias' => $guias,
            'total' => $total,
            'fecha' => $validated['fecha'],
        ])->setPaper('a4', 'portrait')->download('CajaUsuario.pdf');
    }

    public function adminPdf(Request $request): Response
    {
        /** @var User $actor */
        $actor = $request->user();

        $validated = $request->validate([
            'usuario' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date', 'before_or_equal:'.now('America/Guayaquil')->toDateString()],
        ]);

        $targetUser = User::query()->findOrFail((int) $validated['usuario']);

        $query = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->with([
                'persona',
                'sucursal' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class),
                'formaPago',
            ])
            ->where('estado', '1')
            ->where('id_users_update', $targetUser->id)
            ->whereDate('fecha_cancelacion', $validated['fecha']);

        if ($actor->isBranchRestricted() && ! empty($actor->id_sucursal)) {
            $query->where('id_sucursal', $actor->id_sucursal);
        }

        $guias = $query->orderBy('fecha_cancelacion')->orderBy('id_cabecera')->get();
        $total = (float) $guias->sum(fn (Cabecera $g) => (float) $g->valor_guia);

        return Pdf::loadView('caja.reportes.admin', [
            'targetUser' => $targetUser,
            'guias' => $guias,
            'total' => $total,
            'fecha' => $validated['fecha'],
        ])->setPaper('a4', 'portrait')->download('CajaUsuario.pdf');
    }
}