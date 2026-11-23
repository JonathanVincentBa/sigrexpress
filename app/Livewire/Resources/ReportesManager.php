<?php

namespace App\Livewire\Resources;

use App\Models\Cabecera;
use App\Models\Ciudad;
use App\Models\Persona;
use App\Models\Scopes\AssignedBranchScope;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\GuideReportQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public string $tipo = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public string $cliente = '';

    public string $cliente_nombre = '';

    public string $cliente_identificacion = '';

    public string $cliente_tipo = '';

    public string $customerSearch = '';

    public string $sucursal_origen = '';

    /** @var list<string> */
    public array $ciudad_destino = [];

    public string $destinationCitySearch = '';

    public string $sucursal = '';

    public string $usuario = '';

    #[Locked]
    public bool $searched = false;

    public bool $showPreviewModal = false;

    #[Locked]
    public float $cachedReportTotal = 0.0;

    #[Locked]
    public int $cachedTotalItems = 0;

    #[Locked]
    public int $cachedTotalGuides = 0;

    protected GuideReportQuery $reports;

    public function boot(GuideReportQuery $reports): void
    {
        $this->reports = $reports;
    }

    public function mount(): void
    {
        $this->tipo = '';
        $this->fecha_desde = now()->startOfMonth()->toDateString();
        $this->fecha_hasta = now()->toDateString();

        $user = $this->currentUser();
        if ($user?->isBranchRestricted() && ! empty($user->id_sucursal)) {
            $this->sucursal = (string) $user->id_sucursal;
        }

        $this->limpiarCamposBloquesInhabilitados();
    }

    public function updatedTipo(): void
    {
        $this->limpiarCamposBloquesInhabilitados();
        $this->searched = false;
        $this->showPreviewModal = false;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (
            $property === 'tipo'
            || $property === 'paginators.page'
            || $property === 'page'
            || str_starts_with($property, 'paginators.')
        ) {
            return;
        }

        if (in_array($property, [
            'fecha_desde',
            'fecha_hasta',
            'cliente',
            'cliente_nombre',
            'sucursal_origen',
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

    private function limpiarCamposBloquesInhabilitados(): void
    {
        $blocks = $this->reports->enabledBlocksFor($this->tipo);

        if (! $blocks['bloque_02']) {
            $this->cliente = '';
            $this->cliente_nombre = '';
            $this->cliente_identificacion = '';
            $this->cliente_tipo = '';
            $this->customerSearch = '';
        }

        if (! $blocks['bloque_03']) {
            $this->sucursal_origen = '';
            $this->ciudad_destino = [];
            $this->destinationCitySearch = '';
        }

        if (! $blocks['bloque_04']) {
            $user = $this->currentUser();
            $this->sucursal = ($user?->isBranchRestricted() && ! empty($user->id_sucursal))
                ? (string) $user->id_sucursal
                : '';
            $this->usuario = '';
        }
    }

    public function updatedCustomerSearch(): void
    {
        $this->cliente = '';
        $this->cliente_nombre = trim($this->customerSearch);
        $this->cliente_identificacion = '';
        $this->cliente_tipo = '';
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function selectSuggestion(string $nombre, ?int $idPersona = null, ?string $identificacion = null, string $tipo = 'Cliente'): void
    {
        $this->cliente = $idPersona !== null ? (string) $idPersona : '';
        $this->cliente_nombre = trim($nombre);
        $this->cliente_identificacion = trim((string) ($identificacion ?? ''));
        $this->cliente_tipo = $tipo;
        $this->customerSearch = '';
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Persona::query()
            ->where('tipo_persona', 'C')
            ->where('estado', '1')
            ->findOrFail($customerId);

        $this->selectSuggestion(
            nombre: (string) $customer->nombre,
            idPersona: (int) $customer->id_persona,
            identificacion: (string) $customer->num_dni,
            tipo: 'Cliente'
        );
    }

    public function useRemitenteName(): void
    {
        $trimmed = trim($this->customerSearch);
        if ($trimmed !== '') {
            $this->selectSuggestion(
                nombre: $trimmed,
                idPersona: null,
                identificacion: null,
                tipo: 'Texto libre'
            );
        }
    }

    public function editCustomer(): void
    {
        $this->customerSearch = $this->cliente_nombre;
        $this->cliente = '';
        $this->cliente_nombre = '';
        $this->cliente_identificacion = '';
        $this->cliente_tipo = '';
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function clearCustomer(): void
    {
        $this->cliente = '';
        $this->cliente_nombre = '';
        $this->cliente_identificacion = '';
        $this->cliente_tipo = '';
        $this->customerSearch = '';
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function toggleCiudadDestino(string $city): void
    {
        $trimmed = trim($city);
        if ($trimmed === '') {
            return;
        }

        if (in_array($trimmed, $this->ciudad_destino, true)) {
            $this->ciudad_destino = array_values(array_filter($this->ciudad_destino, fn ($c) => $c !== $trimmed));
        } else {
            $this->ciudad_destino[] = $trimmed;
        }

        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function removeCiudadDestino(string $city): void
    {
        $this->ciudad_destino = array_values(array_filter($this->ciudad_destino, fn ($c) => $c !== $city));
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function clearCiudadesDestino(): void
    {
        $this->ciudad_destino = [];
        $this->destinationCitySearch = '';
        $this->searched = false;
        $this->showPreviewModal = false;
    }

    /**
     * @param  list<string>  $filteredCities
     */
    public function selectAllFilteredCities(array $filteredCities): void
    {
        foreach ($filteredCities as $city) {
            $trimmed = trim($city);
            if ($trimmed !== '' && ! in_array($trimmed, $this->ciudad_destino, true)) {
                $this->ciudad_destino[] = $trimmed;
            }
        }

        $this->searched = false;
        $this->showPreviewModal = false;
    }

    public function preview(): void
    {
        $this->limpiarCamposBloquesInhabilitados();

        if ($this->cliente === '' && trim($this->customerSearch) !== '') {
            $this->cliente_nombre = trim($this->customerSearch);
        }

        $validated = $this->validateFilters();
        $user = $this->currentUser();
        $filters = $this->currentFilters($validated);

        $baseQuery = $this->reports->build($filters, $user);

        $this->cachedTotalGuides = (int) (clone $baseQuery)->reorder()->count();
        $this->cachedReportTotal = (float) (clone $baseQuery)->reorder()->sum('valor_guia');
        $this->cachedTotalItems = (int) (clone $baseQuery)->reorder()->toBase()->sum(
            DB::raw('(SELECT COALESCE(SUM(d.cantidad), 0) FROM detalle d WHERE d.id_cabecera = cabecera.id_cabecera AND d.deleted_at IS NULL)')
        );

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
        $this->showPreviewModal = false;
        $this->resetValidation();
    }

    /**
     * @param  array<string, mixed>|null  $validated
     * @return array<string, mixed>
     */
    private function currentFilters(?array $validated = null): array
    {
        $user = $this->currentUser();
        $sucursalFiltro = ($user?->isBranchRestricted() && ! empty($user->id_sucursal))
            ? (string) $user->id_sucursal
            : ($validated['sucursal'] ?? $this->sucursal);

        $destinos = array_values(array_filter(
            $validated['ciudad_destino'] ?? $this->ciudad_destino,
            fn ($v) => is_string($v) && $v !== ''
        ));

        $clienteNombre = $validated['cliente_nombre'] ?? ($this->cliente_nombre !== '' ? $this->cliente_nombre : trim($this->customerSearch));

        return $this->reports->sanitizeFiltersForType([
            'tipo' => $validated['tipo'] ?? $this->tipo,
            'fecha_desde' => $validated['fecha_desde'] ?? $this->fecha_desde,
            'fecha_hasta' => $validated['fecha_hasta'] ?? $this->fecha_hasta,
            'cliente' => $validated['cliente'] ?? $this->cliente,
            'cliente_nombre' => $clienteNombre,
            'sucursal_origen' => $validated['sucursal_origen'] ?? $this->sucursal_origen,
            'ciudad_destino' => $destinos,
            'sucursal' => $sucursalFiltro,
            'usuario' => $validated['usuario'] ?? $this->usuario,
        ]);
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
            'sucursal_origen' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'ciudad_destino' => ['nullable', 'array'],
            'ciudad_destino.*' => ['string', 'exists:ciudad,descripcion'],
            'sucursal' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'usuario' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'tipo.required' => 'Selecciona tu reporte antes de continuar.',
        ], [
            'tipo' => 'reporte',
            'fecha_desde' => 'fecha inicial',
            'fecha_hasta' => 'fecha final',
            'cliente' => 'cliente',
            'cliente_nombre' => 'nombre de remitente',
            'sucursal_origen' => 'sucursal de origen',
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
        $enabledBlocks = $this->reports->enabledBlocksFor($this->tipo);
        $filters = $this->currentFilters();

        $query = ($this->searched && $this->tipo !== '')
            ? $this->reports->build($filters, $user)
            : null;

        $allBranches = Sucursal::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->where('estado', '1')
            ->orderBy('nombre')
            ->get(['id_sucursal', 'nombre']);

        $branches = ($isBranchRestricted && ! empty($user?->id_sucursal))
            ? $allBranches->where('id_sucursal', (int) $user->id_sucursal)->values()
            : $allBranches;

        $predictiveSuggestions = collect();
        $trimmedSearch = $enabledBlocks['bloque_02'] ? trim($this->customerSearch) : '';

        if (mb_strlen($trimmedSearch) >= 2 && mb_strlen($trimmedSearch) <= 100) {
            $personas = Persona::query()
                ->where('tipo_persona', 'C')
                ->where('estado', '1')
                ->where(function ($query) use ($trimmedSearch): void {
                    $query->where('nombre', 'like', "%{$trimmedSearch}%")
                        ->orWhere('num_dni', 'like', "{$trimmedSearch}%");
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get(['id_persona', 'nombre', 'num_dni']);

            foreach ($personas as $p) {
                $predictiveSuggestions->push([
                    'id' => (int) $p->id_persona,
                    'nombre' => (string) $p->nombre,
                    'identificacion' => (string) ($p->num_dni ?? ''),
                    'tipo' => 'Cliente',
                ]);
            }

            $seenNames = $predictiveSuggestions->pluck('nombre')->map(fn ($n) => mb_strtoupper(trim((string) $n)))->all();

            $remitentes = Cabecera::query()
                ->where(function ($query) use ($trimmedSearch): void {
                    $query->where('nom_remitente', 'like', "%{$trimmedSearch}%")
                        ->orWhere('dni_remitente', 'like', "{$trimmedSearch}%");
                })
                ->whereNotNull('nom_remitente')
                ->where('nom_remitente', '!=', '')
                ->select(['nom_remitente', 'dni_remitente'])
                ->distinct()
                ->limit(8)
                ->get();

            foreach ($remitentes as $r) {
                $norm = mb_strtoupper(trim((string) $r->nom_remitente));
                if (! in_array($norm, $seenNames, true)) {
                    $seenNames[] = $norm;
                    $predictiveSuggestions->push([
                        'id' => null,
                        'nombre' => (string) $r->nom_remitente,
                        'identificacion' => (string) ($r->dni_remitente ?? ''),
                        'tipo' => 'Remitente',
                    ]);
                }
            }

            $destinatarios = Cabecera::query()
                ->where(function ($query) use ($trimmedSearch): void {
                    $query->where('nom_destinatario', 'like', "%{$trimmedSearch}%")
                        ->orWhere('dni_destinatario', 'like', "{$trimmedSearch}%");
                })
                ->whereNotNull('nom_destinatario')
                ->where('nom_destinatario', '!=', '')
                ->select(['nom_destinatario', 'dni_destinatario'])
                ->distinct()
                ->limit(8)
                ->get();

            foreach ($destinatarios as $d) {
                $norm = mb_strtoupper(trim((string) $d->nom_destinatario));
                if (! in_array($norm, $seenNames, true)) {
                    $seenNames[] = $norm;
                    $predictiveSuggestions->push([
                        'id' => null,
                        'nombre' => (string) $d->nom_destinatario,
                        'identificacion' => (string) ($d->dni_destinatario ?? ''),
                        'tipo' => 'Destinatario',
                    ]);
                }
            }
        }

        if ($enabledBlocks['bloque_02'] && $this->cliente !== '' && $this->cliente_nombre === '') {
            $foundCustomer = Persona::query()
                ->where('tipo_persona', 'C')
                ->find($this->cliente, ['id_persona', 'nombre', 'num_dni']);

            if ($foundCustomer) {
                $this->cliente_nombre = (string) $foundCustomer->nombre;
                $this->cliente_identificacion = (string) ($foundCustomer->num_dni ?? '');
                $this->cliente_tipo = 'Cliente';
            }
        }

        return view('livewire.resources.reportes-manager', [
            'guides' => $query?->paginate(15),
            'reportTotal' => $this->cachedReportTotal,
            'totalItems' => $this->cachedTotalItems,
            'totalGuidesCount' => $this->cachedTotalGuides,
            'reportTypes' => $this->reports->reportTypes(),
            'enabledBlocks' => $enabledBlocks,
            'isBranchRestricted' => $isBranchRestricted,
            'selectedCustomer' => ($enabledBlocks['bloque_02'] && $this->cliente !== '')
                ? Persona::query()
                    ->where('tipo_persona', 'C')
                    ->find($this->cliente, ['id_persona', 'nombre', 'num_dni'])
                : null,
            'predictiveSuggestions' => $predictiveSuggestions,
            'customerResults' => $predictiveSuggestions,
            'cities' => Ciudad::query()->where('estado', '1')->orderBy('descripcion')->pluck('descripcion'),
            'originBranches' => $allBranches,
            'branches' => $branches,
            'users' => User::query()->where('estado', '1')->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}