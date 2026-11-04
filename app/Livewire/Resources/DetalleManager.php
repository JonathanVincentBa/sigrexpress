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

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $detail = Detalle::query()->findOrFail($id);
        $this->selectedId = $detail->id_detalle;
        $this->id_cabecera = (string) $detail->id_cabecera;
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
        $detailData = [
            'id_cabecera' => $validated['id_cabecera'],
            'cantidad' => (int) $validated['cantidad'],
            'descripcion' => $validated['descripcion'],
            'v_unitario' => round((float) $validated['v_unitario'], 2),
            'v_parcial' => round((int) $validated['cantidad'] * (float) $validated['v_unitario'], 2),
        ];

        if ($this->isEditing) {
            Detalle::query()->findOrFail($this->selectedId)->update($detailData);
        } else {
            Detalle::query()->create($detailData);
        }

        $this->showModal = false;
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $detail = Detalle::query()->findOrFail($id);
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

        Detalle::query()->findOrFail($this->deleteId)->delete();
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
                ->with('cabecera')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where('descripcion', 'like', "%{$search}%")
                        ->orWhereHas('cabecera', fn ($guideQuery) => $guideQuery->where('num_guia', 'like', "%{$search}%"));
                })
                ->latest('id_detalle')
                ->paginate(10),
            'guides' => Cabecera::query()->orderByDesc('num_guia')->get(['id_cabecera', 'num_guia', 'nom_destinatario']),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['id_cabecera', 'descripcion', 'selectedId']);
        $this->cantidad = '1';
        $this->v_unitario = '0.00';
        $this->isEditing = false;
        $this->isViewing = false;
        $this->resetValidation();
    }
}
