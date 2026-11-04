<?php

namespace App\Livewire;

use App\Models\Cabecera;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\Vehiculo;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use LogicException;

#[Layout('components.layouts.app')]
#[Title('Panel Gerencial')]
class DashboardManager extends Component
{
    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function render(): View
    {
        $monthStart = CarbonImmutable::now()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $monthly = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = CarbonImmutable::parse($monthStart->subMonths($offset));
            $localizedMonth = $month->locale('es');

            if (! $localizedMonth instanceof CarbonImmutable) {
                throw new LogicException('El mes del panel debe conservar una fecha inmutable.');
            }

            $monthly[] = [
                'label' => $localizedMonth->translatedFormat('M'),
                'count' => Cabecera::query()
                    ->whereBetween('fecha_emision', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->where('estado', '1')
                    ->count(),
                'revenue' => (float) Cabecera::query()
                    ->whereBetween('fecha_emision', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->where('estado', '1')
                    ->sum('valor_guia'),
            ];
        }

        $maximumMonthlyCount = max(1, max(array_column($monthly, 'count')));

        return view('livewire.dashboard-manager', [
            'metrics' => [
                'guias' => Cabecera::query()->whereBetween('fecha_emision', [$monthStart, $monthEnd])->where('estado', '1')->count(),
                'ingresos' => (float) Cabecera::query()->whereBetween('fecha_emision', [$monthStart, $monthEnd])->where('estado', '1')->sum('valor_guia'),
                'pendiente' => (float) Cabecera::query()->where('estado', '1')->whereRaw('UPPER(estatus_cobro) = ?', ['PENDIENTE'])->sum('valor_guia'),
                'flota' => Vehiculo::query()->where('estado', '1')->count(),
            ],
            'monthly' => $monthly,
            'maximumMonthlyCount' => $maximumMonthlyCount,
            'branchCosts' => Cabecera::query()
                ->selectRaw('id_sucursal, SUM(valor_guia) as total')
                ->with('sucursal:id_sucursal,nombre')
                ->whereBetween('fecha_emision', [$monthStart, $monthEnd])
                ->where('estado', '1')
                ->groupBy('id_sucursal')
                ->orderByDesc('total')
                ->get(),
            'historicalBranchValues' => Cabecera::query()
                ->selectRaw('id_sucursal, SUM(valor_guia) as total')
                ->with('sucursal:id_sucursal,nombre')
                ->where('estado', '1')
                ->groupBy('id_sucursal')
                ->havingRaw('SUM(valor_guia) > 0')
                ->orderByDesc('total')
                ->get(),
            'catalogCounts' => [
                'clientes' => Persona::query()->where('tipo_persona', 'C')->where('estado', '1')->count(),
                'sucursales' => Sucursal::query()->where('estado', '1')->count(),
                'empresas' => Empresa::query()->where('estado', '1')->count(),
            ],
        ]);
    }
}
