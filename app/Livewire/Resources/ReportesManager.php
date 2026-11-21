<?php

namespace App\Livewire\Resources;

use App\Models\Ciudad;
use App\Models\Persona;
use App\Models\Scopes\AssignedBranchScope;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\GuideReportQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Reportes Gerenciales')]
class ReportesManager extends Component
{
    use WithPagination;

    public string $tipo = 'clientes';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public string $cliente = '';

    public string $cliente_nombre = '';

    public string $customerSearch = '';

    public string $ciudad_origen = '';

    /** @var list<string> */
    public array $ciudad_destino = [];

    public string $sucursal = '';

    public string $usuario = '';

    

    #[Locked]
    public bool $searched = false;

    public bool $showPreviewModal = false;

    protected GuideReportQuery $reports;

    public function boot(GuideReportQuery $reports): void
    {
        $this->reports = $reports;
    }

    public function mount(): void
    {
        $this->fecha_desde = now()->startOfMonth()->toDateString();
        $this->fecha_hasta = now()->toDateString();

        $user = $this->currentUser();
        if ($user?->isBranchRestricted() && ! empty($user->id_sucursal)) {
            $this->sucursal = (string) $user->id_sucursal;
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'tipo',
            'fecha_desde',
            'fecha_hasta',
            'cliente',
            'cliente_nombre',
            'ciudad_origen',
            'ciudad_destino',
            'sucursal',
            'usuario',
        ], true)) {
            $user = $this->currentUser();
            if ($property === 'sucursal' && $user?->isBranchRestricted() && ! empty($user->id_sucursal)) {
                $this->sucursal = (string) $user->id_sucursal;
            }

            $this->searched = false;
            $this->showPreviewModal = false;
            $this->resetPage();
        }
    }

    public function updatedCustomerSearch(): void
    {
        $this->cliente = '';
        $this->cliente_nombre = trim($this->customerSearch);
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Persona::query()
            ->where('tipo_persona', 'C')
            ->where('estado', '1')
            ->findOrFail($customerId);

        $this->cliente = (string) $customer->id_persona;
        $this->cliente_nombre = (string) $customer->nombre;
        $this->customerSearch = '';
        $this->searched = false;
    }

    public function useRemitenteName(): void
    {
        $trimmed = trim($this->customerSearch);
        if ($trimmed !== '') {
            $this->cliente = '';
            $this->cliente_nombre = $trimmed;
            $this->searched = false;
        }
    }

    public function clearCustomer(): void
    {
        $this->cliente = '';
        $this->cliente_nombre = '';
        $this->customerSearch = '';
        $this->searched = false;
    }

    public function preview(): void
    {
        if ($this->cliente === '' && trim($this->customerSearch) !== '') {
            $this->cliente_nombre = trim($this->customerSearch);
        }

        $this->validateFilters();
        $this->searched = true;
        $this->showPreviewModal = true;
        $this->resetPage();
    }

    public function openPreviewModal(): void
    {
        if ($this->searched) {
            $this->showPreviewModal = true;
        }
    }

    public function closePreviewModal(): void
    {
        $this->resetReportState();
    }

    private function resetReportState(): void
    {
        $this->searched = false;
        $this->showPreviewModal = false;
        $this->tipo = 'clientes';
        $this->fecha_desde = now()->startOfMonth()->toDateString();
        $this->fecha_hasta = now()->toDateString();
        $this->cliente = '';
        $this->cliente_nombre = '';
        $this->customerSearch = '';
        $this->ciudad_origen = '';
        $this->ciudad_destino = [];
        $this->usuario = '';

        $user = $this->currentUser();
        $this->sucursal = ($user?->isBranchRestricted() && ! empty($user->id_sucursal))
            ? (string) $user->id_sucursal
            : '';

        $this->resetValidation();
        $this->resetPage();
    }
    /**
     * @return array<string, mixed>
     */
    private function currentFilters(): array
    {
        $user = $this->currentUser();
        $sucursalFiltro = ($user?->isBranchRestricted() && ! empty($user->id_sucursal))
            ? (string) $user->id_sucursal
            : $this->sucursal;

        $destinos = array_values(array_filter(
            $this->ciudad_destino,
            fn($v) => is_string($v) && $v !== ''
        ));

        return [
            'tipo' => $this->tipo,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'cliente' => $this->cliente,
            'cliente_nombre' => $this->cliente_nombre,
            'ciudad_origen' => $this->ciudad_origen,
            'ciudad_destino' => $destinos,
            'sucursal' => $sucursalFiltro,
            'usuario' => $this->usuario,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFilters(): array
    {
        $validated = $this->validate([
            'tipo' => ['required', Rule::in(array_keys($this->reports->reportTypes()))],
            'fecha_desde' => ['required', 'date', 'before_or_equal:fecha_hasta'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'cliente' => ['nullable', 'integer', 'exists:persona,id_persona'],
            'cliente_nombre' => ['nullable', 'string', 'max:100'],
            'ciudad_origen' => ['nullable', 'string', 'exists:ciudad,descripcion'],
            'ciudad_destino' => ['nullable', 'array'],
            'ciudad_destino.*' => ['string', 'exists:ciudad,descripcion'],
            'sucursal' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'usuario' => ['nullable', 'integer', 'exists:users,id'],
        ], [], [
            'tipo' => 'reporte',
            'fecha_desde' => 'fecha inicial',
            'fecha_hasta' => 'fecha final',
            'cliente' => 'cliente',
            'cliente_nombre' => 'nombre de remitente',
            'ciudad_origen' => 'ciudad de origen',
            'ciudad_destino' => 'ciudades de destino',
            'ciudad_destino.*' => 'ciudad de destino',
            'sucursal' => 'sucursal',
            'usuario' => 'usuario',
        ]);

        if (Carbon::parse($validated['fecha_desde'])->diffInDays(Carbon::parse($validated['fecha_hasta'])) > 366) {
            throw ValidationException::withMessages([
                'fecha_hasta' => 'El rango de fechas no puede superar 366 días.',
            ]);
        }

        return $validated;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render()
    {
        $user = $this->currentUser();
        $isBranchRestricted = (bool) $user?->isBranchRestricted();
        $filters = $this->currentFilters();

        $query = $this->searched
            ? $this->reports->build($filters, $user)
            : null;

        $branchesQuery = Sucursal::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->where('estado', '1')
            ->orderBy('nombre');

        if ($isBranchRestricted && ! empty($user?->id_sucursal)) {
            $branchesQuery->where('id_sucursal', $user->id_sucursal);
        }

        $totalItems = 0;
        if ($query !== null) {
            $totalItems = (int) (clone $query)->reorder()->toBase()->sum(
                \Illuminate\Support\Facades\DB::raw('(SELECT COALESCE(SUM(d.cantidad), 0) FROM detalle d WHERE d.id_cabecera = cabecera.id_cabecera AND d.deleted_at IS NULL)')
            );
        }

        return view('livewire.resources.reportes-manager', [
            'guides' => $query?->paginate(15),
            'reportTotal' => $query === null ? 0 : (clone $query)->reorder()->sum('valor_guia'),
            'totalItems' => $totalItems,
            'reportTypes' => $this->reports->reportTypes(),
            'isBranchRestricted' => $isBranchRestricted,
            'selectedCustomer' => $this->cliente !== ''
                ? Persona::query()
                ->where('tipo_persona', 'C')
                ->find($this->cliente, ['id_persona', 'nombre', 'num_dni'])
                : null,
            'customerResults' => mb_strlen(trim($this->customerSearch)) >= 2
                && mb_strlen(trim($this->customerSearch)) <= 100
                ? Persona::query()
                ->where('tipo_persona', 'C')
                ->where('estado', '1')
                ->where(function ($query): void {
                    $search = trim($this->customerSearch);
                    $query->where('nombre', 'like', "{$search}%")
                        ->orWhere('num_dni', 'like', "{$search}%");
                })
                ->orderBy('nombre')
                ->limit(20)
                ->get(['id_persona', 'nombre', 'num_dni'])
                : collect(),
            'cities' => Ciudad::query()->where('estado', '1')->orderBy('descripcion')->pluck('descripcion'),
            'branches' => $branchesQuery->get(['id_sucursal', 'nombre']),
            'users' => User::query()->where('estado', '1')->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
