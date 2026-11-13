<?php

namespace App\Livewire\Resources;

use App\Models\Ciudad;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\GuideReportQuery;
use Carbon\Carbon;
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

    public string $customerSearch = '';

    public string $ciudad_origen = '';

    public string $ciudad_destino = '';

    public string $sucursal = '';

    public string $usuario = '';

    #[Locked]
    public bool $searched = false;

    protected GuideReportQuery $reports;

    public function boot(GuideReportQuery $reports): void
    {
        $this->reports = $reports;
    }

    public function mount(): void
    {
        $this->fecha_desde = now()->startOfMonth()->toDateString();
        $this->fecha_hasta = now()->toDateString();
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'tipo',
            'fecha_desde',
            'fecha_hasta',
            'cliente',
            'ciudad_origen',
            'ciudad_destino',
            'sucursal',
            'usuario',
        ], true)) {
            $this->searched = false;
            $this->resetPage();
        }
    }

    public function updatedCustomerSearch(): void
    {
        if ($this->customerSearch !== '') {
            $this->cliente = '';
            $this->searched = false;
        }
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Persona::query()
            ->where('tipo_persona', 'C')
            ->where('estado', '1')
            ->findOrFail($customerId);

        $this->cliente = (string) $customer->id_persona;
        $this->customerSearch = '';
        $this->searched = false;
    }

    public function clearCustomer(): void
    {
        $this->cliente = '';
        $this->customerSearch = '';
        $this->searched = false;
    }

    public function preview(): void
    {
        $this->validateFilters();
        $this->searched = true;
        $this->resetPage();
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
            'ciudad_origen' => ['nullable', 'string', 'exists:ciudad,descripcion'],
            'ciudad_destino' => ['nullable', 'string', 'exists:ciudad,descripcion'],
            'sucursal' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'usuario' => ['nullable', 'integer', 'exists:users,id'],
        ], [], [
            'tipo' => 'reporte',
            'fecha_desde' => 'fecha inicial',
            'fecha_hasta' => 'fecha final',
            'cliente' => 'cliente',
            'ciudad_origen' => 'ciudad de origen',
            'ciudad_destino' => 'ciudad de destino',
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

    public function render()
    {
        $query = $this->searched
            ? $this->reports->build([
                'tipo' => $this->tipo,
                'fecha_desde' => $this->fecha_desde,
                'fecha_hasta' => $this->fecha_hasta,
                'cliente' => $this->cliente,
                'ciudad_origen' => $this->ciudad_origen,
                'ciudad_destino' => $this->ciudad_destino,
                'sucursal' => $this->sucursal,
                'usuario' => $this->usuario,
            ])
            : null;

        $filters = [
            'tipo' => $this->tipo,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'cliente' => $this->cliente,
            'ciudad_origen' => $this->ciudad_origen,
            'ciudad_destino' => $this->ciudad_destino,
            'sucursal' => $this->sucursal,
            'usuario' => $this->usuario,
        ];

        return view('livewire.resources.reportes-manager', [
            'guides' => $query?->paginate(15),
            'reportTotal' => $query === null ? 0 : (clone $query)->sum('valor_guia'),
            'reportTypes' => $this->reports->reportTypes(),
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
            'branches' => Sucursal::query()->where('estado', '1')->orderBy('nombre')->get(['id_sucursal', 'nombre']),
            'users' => User::query()->where('estado', '1')->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
