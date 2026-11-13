<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use App\Models\Cabecera;
use App\Models\Manifiesto;
use App\Models\Persona;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Asignación de Manifiestos')]
class ManifiestoManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    public bool $showModal = false;

    public string $search = '';

    public string $fechaSalida = '';

    public string $observacion = '';

    public string $idVehiculo = '';

    public string $idChofer = '';

    /** @var array<int, string> */
    public array $selectedGuides = [];

    public function mount(): void
    {
        $this->fechaSalida = now()->format('Y-m-d\TH:i');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset(['observacion', 'idVehiculo', 'idChofer', 'selectedGuides']);
        $this->fechaSalida = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function updatedIdVehiculo(string $vehicleId): void
    {
        $this->idChofer = (string) Vehiculo::query()
            ->where('estado', '1')
            ->whereKey($vehicleId)
            ->value('id_chofer') ?? '';
    }

    public function save(): void
    {
        $this->observacion = $this->normalizeTextValues(['observacion' => $this->observacion])['observacion'];
        $validated = $this->validate([
            'idVehiculo' => ['required', 'integer', 'exists:vehiculos,id_vehiculo,estado,1'],
            'idChofer' => ['required', 'integer', 'exists:persona,id_persona,tipo_persona,E,estado,1'],
            'fechaSalida' => ['required', 'date'],
            'observacion' => ['nullable', 'string', 'max:2000'],
            'selectedGuides' => ['required', 'array', 'min:1', 'max:100'],
            'selectedGuides.*' => ['required', 'integer', 'distinct', 'exists:cabecera,id_cabecera'],
        ], [], [
            'idVehiculo' => 'vehículo',
            'idChofer' => 'chofer',
            'fechaSalida' => 'fecha de salida',
            'selectedGuides' => 'guías',
            'selectedGuides.*' => 'guía',
        ]);

        $guideIds = array_values(array_unique(array_map('intval', $validated['selectedGuides'])));

        DB::transaction(function () use ($validated, $guideIds): void {
            $vehicle = Vehiculo::query()
                ->where('estado', '1')
                ->lockForUpdate()
                ->findOrFail($validated['idVehiculo']);

            if ($vehicle->id_chofer !== null && (int) $vehicle->id_chofer !== (int) $validated['idChofer']) {
                throw ValidationException::withMessages([
                    'idChofer' => 'El chofer seleccionado no coincide con el asignado a este vehículo.',
                ]);
            }

            $guides = Cabecera::query()
                ->whereIn('id_cabecera', $guideIds)
                ->where('estado', '1')
                ->where('estado_tracking', 'EN BODEGA')
                ->whereDoesntHave('manifiestos')
                ->lockForUpdate()
                ->get();

            if ($guides->count() !== count($guideIds)) {
                throw ValidationException::withMessages([
                    'selectedGuides' => 'Una o más guías ya no están disponibles para asignación.',
                ]);
            }

            if (auth()->user()?->isBranchRestricted()) {
                $assignedBranch = (int) auth()->user()->id_sucursal;

                if ($vehicle->id_sucursal !== null && (int) $vehicle->id_sucursal !== $assignedBranch) {
                    abort(403, 'No tienes autorización para asignar un vehículo de otra sucursal.');
                }

                foreach ($guides as $guide) {
                    if ($guide->id_sucursal !== null && (int) $guide->id_sucursal !== $assignedBranch) {
                        abort(403, 'No tienes autorización para asignar guías de otra sucursal.');
                    }
                }
            }

            $manifestBranchId = auth()->user()?->isBranchRestricted()
                ? auth()->user()->id_sucursal
                : ($vehicle->id_sucursal ?? auth()->user()?->id_sucursal);

            $manifest = Manifiesto::query()->create([
                'codigo' => 'MNF-'.Str::upper((string) Str::ulid()),
                'id_sucursal' => $manifestBranchId,
                'id_vehiculo' => $vehicle->id_vehiculo,
                'id_chofer' => $validated['idChofer'],
                'id_usuario' => auth()->id(),
                'fecha_salida' => $validated['fechaSalida'],
                'estado' => 'EN CAMINO',
                'observacion' => trim($validated['observacion'] ?? '') ?: null,
            ]);

            $manifest->guias()->attach($guideIds);

            foreach ($guides as $guide) {
                $guide->forceFill(['estado_tracking' => 'EN CAMINO'])->save();
                $guide->historialEstados()->create([
                    'id_usuario' => auth()->id(),
                    'estado' => 'EN CAMINO',
                    'observacion' => 'Asignada al manifiesto '.$manifest->codigo,
                ]);
            }
        });

        $this->showModal = false;
        $this->reset(['observacion', 'idVehiculo', 'idChofer', 'selectedGuides']);
        $this->alertSuccess();
    }

    public function close(int $id): void
    {
        $manifest = Manifiesto::query()->with('guias')->findOrFail($id);

        if (auth()->user()?->isBranchRestricted() && $manifest->id_sucursal !== null && (int) $manifest->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para cerrar manifiestos de otra sucursal.');
        }

        if ($manifest->estado !== 'EN CAMINO') {
            $this->alertWarning('No se puede cerrar', 'El manifiesto no está en tránsito.');

            return;
        }

        if ($manifest->guias->contains(fn (Cabecera $guide): bool => $guide->estado_tracking !== 'ENTREGADO')) {
            $this->alertWarning('Faltan entregas', 'Todas las guías deben estar marcadas como entregadas antes de cerrar el manifiesto.');

            return;
        }

        $manifest->forceFill([
            'estado' => 'CERRADO',
            'fecha_cierre' => now(),
        ])->save();

        $this->alertSuccess();
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.resources.manifiesto-manager', [
            'manifests' => Manifiesto::query()
                ->with(['vehiculo', 'chofer'])
                ->withCount('guias')
                ->when($search !== '', fn ($query) => $query->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('chofer', fn ($query) => $query->where('nombre', 'like', "%{$search}%")))
                ->latest('id_manifiesto')
                ->paginate(10),
            'vehicles' => Vehiculo::query()
                ->where('estado', '1')
                ->with('chofer')
                ->orderBy('placa')
                ->get(),
            'drivers' => Persona::query()
                ->where('tipo_persona', 'E')
                ->where('estado', '1')
                ->orderBy('nombre')
                ->get(['id_persona', 'nombre']),
            'availableGuides' => $this->showModal
                ? Cabecera::query()
                    ->where('estado', '1')
                    ->where('estado_tracking', 'EN BODEGA')
                    ->whereDoesntHave('manifiestos')
                    ->orderByDesc('fecha_emision')
                    ->limit(100)
                    ->get(['id_cabecera', 'num_guia', 'nom_remitente', 'nom_destinatario', 'ciudad_origen', 'ciudad_destino'])
                : collect(),
        ]);
    }
}
