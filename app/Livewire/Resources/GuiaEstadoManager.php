<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use App\Models\Cabecera;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Seguimiento de Guías')]
class GuiaEstadoManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    /** @var array<int, string> */
    public const ESTADOS = ['EN BODEGA', 'EN CAMINO', 'EN DESTINO', 'ENTREGADO'];

    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $selectedId = null;

    public string $estado = 'EN BODEGA';

    public string $observacion = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $guide = Cabecera::query()->findOrFail($id);
        $this->selectedId = $guide->id_cabecera;
        $this->estado = $guide->estado_tracking;
        $this->observacion = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->observacion = $this->normalizeTextValues(['observacion' => $this->observacion])['observacion'];
        $validated = $this->validate([
            'selectedId' => ['required', 'integer', 'exists:cabecera,id_cabecera'],
            'estado' => ['required', 'in:'.implode(',', self::ESTADOS)],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'estado' => 'estado de la guía',
            'observacion' => 'observación',
        ]);

        DB::transaction(function () use ($validated): void {
            $guide = Cabecera::query()->lockForUpdate()->findOrFail($validated['selectedId']);

            if ($guide->estado_tracking !== $validated['estado']) {
                $guide->forceFill(['estado_tracking' => $validated['estado']])->save();
                $guide->historialEstados()->create([
                    'id_usuario' => auth()->id(),
                    'estado' => $validated['estado'],
                    'observacion' => trim($validated['observacion'] ?? '') ?: null,
                ]);
            }
        });

        $this->showModal = false;
        $this->alertSuccess();
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.resources.guia-estado-manager', [
            'guides' => Cabecera::query()
                ->with(['historialEstados' => fn ($query) => $query->latest()->limit(1)])
                ->where('estado', '1')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('num_guia', 'like', "%{$search}%")
                        ->orWhere('nom_remitente', 'like', "%{$search}%")
                        ->orWhere('nom_destinatario', 'like', "%{$search}%");
                }))
                ->when($this->statusFilter !== '', fn ($query) => $query->where('estado_tracking', $this->statusFilter))
                ->latest('id_cabecera')
                ->paginate(12),
            'states' => self::ESTADOS,
        ]);
    }
}
