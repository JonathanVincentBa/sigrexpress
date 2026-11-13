<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use App\Models\Cabecera;
use App\Models\Detalle;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Detalles de Guías')]
class DetalleManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    public string $search = '';

    public string $guideSearch = '';

    public string $id_cabecera = '';

    public string $cantidad = '1';

    public string $descripcion = '';

    public string $v_unitario = '0.00';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $isEditing = false;

    public bool $isViewing = false;

    public ?int $selectedId = null;

    public ?int $deleteId = null;

    public string $deleteLabel = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function selectGuide(int $guideId): void
    {
        $guide = Cabecera::query()->findOrFail($guideId);

        if (auth()->user()?->isBranchRestricted() && (int) $guide->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para seleccionar una guía de otra sucursal.');
        }

        $this->id_cabecera = (string) $guide->id_cabecera;
        $this->guideSearch = '';
    }

    public function clearGuide(): void
    {
        $this->id_cabecera = '';
        $this->guideSearch = '';
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $detail = Detalle::query()->with('cabecera')->findOrFail($id);

        if (auth()->user()?->isBranchRestricted() && (int) $detail->cabecera?->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para acceder a detalles de otra sucursal.');
        }

        $this->selectedId = $detail->id_detalle;
        $this->id_cabecera = (string) $detail->id_cabecera;
        $this->guideSearch = '';
        $this->cantidad = (string) $detail->cantidad;
        $this->descripcion = $detail->descripcion;
        $this->v_unitario = (string) $detail->v_unitario;
        $this->isEditing = true;
        $this->isViewing = false;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function view(int $id): void
    {
        $this->edit($id);
        $this->isViewing = true;
    }

    public function save(): void
    {
        $normalized = $this->normalizeTextValues([
            'id_cabecera' => $this->id_cabecera,
            'cantidad' => $this->cantidad,
            'descripcion' => $this->descripcion,
            'v_unitario' => $this->v_unitario,
        ]);
        $this->id_cabecera = $normalized['id_cabecera'];
        $this->cantidad = $normalized['cantidad'];
        $this->descripcion = $normalized['descripcion'];
        $this->v_unitario = $normalized['v_unitario'];

        $validated = $this->validate([
            'id_cabecera' => ['required', 'integer', Rule::exists('cabecera', 'id_cabecera')],
            'cantidad' => ['required', 'integer', 'min:1'],
            'descripcion' => ['required', 'string', 'max:100'],
            'v_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        $guide = Cabecera::query()->findOrFail($validated['id_cabecera']);

        if (auth()->user()?->isBranchRestricted() && (int) $guide->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para operar en una guía de otra sucursal.');
        }

        $detailData = [
            'id_cabecera' => $validated['id_cabecera'],
            'cantidad' => (int) $validated['cantidad'],
            'descripcion' => $validated['descripcion'],
            'v_unitario' => round((float) $validated['v_unitario'], 2),
            'v_parcial' => round((int) $validated['cantidad'] * (float) $validated['v_unitario'], 2),
        ];

        if ($this->isEditing) {
            $existingDetail = Detalle::query()->with('cabecera')->findOrFail($this->selectedId);

            if (auth()->user()?->isBranchRestricted() && (int) $existingDetail->cabecera?->id_sucursal !== (int) auth()->user()->id_sucursal) {
                abort(403, 'No tienes autorización para modificar detalles de otra sucursal.');
            }

            $existingDetail->update($detailData);
        } else {
            Detalle::query()->create($detailData);
        }

        $this->showModal = false;
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $detail = Detalle::query()->with('cabecera')->findOrFail($id);

        if (auth()->user()?->isBranchRestricted() && (int) $detail->cabecera?->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para eliminar detalles de otra sucursal.');
        }

        $this->deleteId = $id;
        $this->deleteLabel = $detail->descripcion;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deleteId === null) {
            $this->alertError('No se pudo eliminar', 'Selecciona un detalle válido e inténtalo nuevamente.');

            return;
        }

        $detail = Detalle::query()->with('cabecera')->findOrFail($this->deleteId);

        if (auth()->user()?->isBranchRestricted() && (int) $detail->cabecera?->id_sucursal !== (int) auth()->user()->id_sucursal) {
            abort(403, 'No tienes autorización para eliminar detalles de otra sucursal.');
        }

        $detail->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteLabel = '';
        $this->alertToast('Detalle eliminado correctamente.');
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.resources.detalle-manager', [
            'details' => Detalle::query()
                ->with('cabecera:id_cabecera,num_guia')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($detailQuery) use ($search): void {
                        $detailQuery->where('descripcion', 'like', "%{$search}%")
                            ->orWhereHas('cabecera', fn ($guideQuery) => $guideQuery->where('num_guia', 'like', "%{$search}%"));
                    });
                })
                ->latest('id_detalle')
                ->simplePaginate(10),
            'selectedGuide' => $this->showModal && $this->id_cabecera !== ''
                ? Cabecera::query()->find($this->id_cabecera, ['id_cabecera', 'num_guia', 'nom_destinatario'])
                : null,
            'guideResults' => $this->showModal && strlen($this->guideSearch) <= 20 && ctype_digit($this->guideSearch)
                ? Cabecera::query()
                    ->where('num_guia', $this->guideSearch)
                    ->limit(1)
                    ->get(['id_cabecera', 'num_guia', 'nom_destinatario'])
                : collect(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['id_cabecera', 'descripcion', 'selectedId', 'guideSearch']);
        $this->cantidad = '1';
        $this->v_unitario = '0.00';
        $this->isEditing = false;
        $this->isViewing = false;
        $this->resetValidation();
    }
}
