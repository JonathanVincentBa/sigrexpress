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

#[Layout('components.layouts.app')]
#[Title('Panel de Control')]
class DashboardManager extends Component
{
    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function render(): View
    {
        $user = auth()->user();
        $isRestricted = $user?->isBranchRestricted() ?? false;
        $userBranchId = $user?->id_sucursal;

        $monthStart = CarbonImmutable::now()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $todayStart = CarbonImmutable::now()->startOfDay();
        $todayEnd = CarbonImmutable::now()->endOfDay();

        // 1. VISTA EXCLUSIVA PARA SECRETARÍA / AGENCIA
        if ($isRestricted) {
            $branchGuiasHoy = Cabecera::query()
                ->where('id_sucursal', $userBranchId)
                ->whereBetween('fecha_emision', [$todayStart->toDateTimeString(), $todayEnd->toDateTimeString()])
                ->where('estado', '1')
                ->count();

            $branchGuiasMes = Cabecera::query()
                ->where('id_sucursal', $userBranchId)
                ->whereBetween('fecha_emision', [$monthStart->toDateTimeString(), $monthEnd->toDateTimeString()])
                ->where('estado', '1')
                ->count();

            $branchPendientesCobro = Cabecera::query()
                ->where('id_sucursal', $userBranchId)
                ->where('estado', '1')
                ->whereRaw('UPPER(estatus_cobro) = ?', ['PENDIENTE'])
                ->count();

            $recentBranchGuides = Cabecera::query()
                ->where('id_sucursal', $userBranchId)
                ->with(['persona'])
                ->latest('id_cabecera')
                ->limit(8)
                ->get();

            return view('livewire.dashboard-manager', [
                'isRestricted' => true,
                'userBranchName' => $user?->sucursal?->nombre ?? 'Mi Agencia',
                'metrics' => [
                    'guias_hoy' => $branchGuiasHoy,
                    'guias_mes' => $branchGuiasMes,
                    'pendientes_cobro' => $branchPendientesCobro,
                    'clientes_activos' => Persona::query()->where('tipo_persona', 'C')->where('estado', '1')->count(),
                ],
                'recentGuides' => $recentBranchGuides,
            ]);
        }

        // 2. VISTA GLOBAL GERENCIAL (ADMIN / SUPER ADMIN)
        $monthlyExpressions = [];
        $monthlyBindings = [];
        $monthlyPeriods = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $monthStart->subMonths($offset);
            $localizedMonth = $month->locale('es');
            $monthlyPeriods[] = [
                'label' => $localizedMonth->translatedFormat('M'),
                'countAlias' => "count_{$offset}",
                'revenueAlias' => "revenue_{$offset}",
            ];
            $monthlyExpressions[] = "SUM(CASE WHEN fecha_emision BETWEEN ? AND ? THEN 1 ELSE 0 END) as count_{$offset}";
            $monthlyExpressions[] = "SUM(CASE WHEN fecha_emision BETWEEN ? AND ? THEN valor_guia ELSE 0 END) as revenue_{$offset}";
            $monthlyBindings[] = $month->startOfMonth()->toDateTimeString();
            $monthlyBindings[] = $month->endOfMonth()->toDateTimeString();
            $monthlyBindings[] = $month->startOfMonth()->toDateTimeString();
            $monthlyBindings[] = $month->endOfMonth()->toDateTimeString();
        }

        $monthlyTotals = Cabecera::query()
            ->where('estado', '1')
            ->whereBetween('fecha_emision', [
                $monthStart->subMonths(5)->toDateTimeString(),
                $monthEnd->toDateTimeString(),
            ])
            ->selectRaw(implode(', ', $monthlyExpressions), $monthlyBindings)
            ->first();

        $monthly = array_map(
            fn (array $period): array => [
                'label' => $period['label'],
                'count' => (int) $monthlyTotals->{$period['countAlias']},
                'revenue' => (float) $monthlyTotals->{$period['revenueAlias']},
            ],
            $monthlyPeriods,
        );

        $maximumMonthlyCount = max(1, max(array_column($monthly, 'count')));

        $branchAggregates = Cabecera::query()
            ->selectRaw(
                "id_sucursal,
                SUM(CASE WHEN fecha_emision BETWEEN ? AND ? AND estado = '1' THEN valor_guia ELSE 0 END) as monthly_total,
                SUM(CASE WHEN estado = '1' THEN valor_guia ELSE 0 END) as historical_total,
                SUM(CASE WHEN estado = '1' AND UPPER(estatus_cobro) = 'PENDIENTE' THEN valor_guia ELSE 0 END) as pending_total",
                [$monthStart->toDateTimeString(), $monthEnd->toDateTimeString()],
            )
            ->with('sucursal:id_sucursal,nombre')
            ->groupBy('id_sucursal')
            ->get();

        $branchCosts = $branchAggregates
            ->filter(fn (Cabecera $branch): bool => (float) $branch->monthly_total > 0)
            ->sortByDesc('monthly_total')
            ->map(fn (Cabecera $branch): object => (object) [
                'id_sucursal' => $branch->id_sucursal,
                'total' => $branch->monthly_total,
                'sucursal' => $branch->sucursal,
            ])
            ->values();

        $historicalBranchValues = $branchAggregates
            ->filter(fn (Cabecera $branch): bool => (float) $branch->historical_total > 0)
            ->sortByDesc('historical_total')
            ->map(fn (Cabecera $branch): object => (object) [
                'id_sucursal' => $branch->id_sucursal,
                'total' => $branch->historical_total,
                'sucursal' => $branch->sucursal,
            ])
            ->values();

        return view('livewire.dashboard-manager', [
            'isRestricted' => false,
            'metrics' => [
                'guias' => (int) $monthlyTotals->count_0,
                'ingresos' => (float) $monthlyTotals->revenue_0,
                'pendiente' => (float) $branchAggregates->sum('pending_total'),
                'flota' => Vehiculo::query()->where('estado', '1')->count(),
            ],
            'monthly' => $monthly,
            'maximumMonthlyCount' => $maximumMonthlyCount,
            'branchCosts' => $branchCosts,
            'historicalBranchValues' => $historicalBranchValues,
        ]);
    }
}