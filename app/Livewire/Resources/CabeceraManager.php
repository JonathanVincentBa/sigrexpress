<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use App\Models\Cabecera;
use App\Models\Cargo;
use App\Models\Ciudad;
use App\Models\Empresa;
use App\Models\FormasPago;
use App\Models\MotivoTraslado;
use App\Models\Persona;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Guías de Remisión')]
class CabeceraManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    public string $search = '';

    /** @var array<string, mixed> */
    public array $formData = [];

    /** @var array<int, array{id_detalle: int|string|null, cantidad: int|string, descripcion: string, v_unitario: int|float|string}> */
    public array $detalles = [];

    public string $clientSearch = '';

    /** @var array<string, mixed> */
    public array $quickCustomer = [];

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showDetailDeleteModal = false;

    public bool $showQuickCustomerModal = false;

    public bool $isEditing = false;

    public bool $isViewing = false;

    public ?int $selectedId = null;

    public ?int $deleteId = null;

    public string $deleteLabel = '';

    public ?int $detailDeleteIndex = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClientSearch(): void
    {
        if ($this->clientSearch !== '') {
            $this->formData['id_persona'] = '';
        }
    }

    public function create(): void
    {
        $this->selectedId = null;
        $this->isEditing = false;
        $this->isViewing = false;
        $this->formData = $this->defaultFormData();
        $this->detalles = [$this->emptyDetail()];
        $this->clientSearch = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->loadGuide($id);
        $this->isEditing = true;
        $this->isViewing = false;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function view(int $id): void
    {
        $this->loadGuide($id);
        $this->isEditing = true;
        $this->isViewing = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function addDetail(): void
    {
        $this->detalles[] = $this->emptyDetail();
    }

    public function prepareRemoveDetail(int $index): void
    {
        if (array_key_exists($index, $this->detalles)) {
            $this->detailDeleteIndex = $index;
            $this->showDetailDeleteModal = true;
        }
    }

    public function confirmRemoveDetail(): void
    {
        if ($this->detailDeleteIndex === null || ! array_key_exists($this->detailDeleteIndex, $this->detalles)) {
            $this->alertError('No se pudo quitar la línea', 'Selecciona un detalle válido e inténtalo nuevamente.');

            return;
        }

        unset($this->detalles[$this->detailDeleteIndex]);
        $this->detalles = array_values($this->detalles);
        $this->detailDeleteIndex = null;
        $this->showDetailDeleteModal = false;
    }

    public function selectClient(int $id): void
    {
        $person = Persona::query()
            ->where('tipo_persona', 'C')
            ->findOrFail($id);
        $this->formData['id_persona'] = $person->id_persona;
        $this->clientSearch = "{$person->nombre} · {$person->num_dni}";
        $this->formData['nom_remitente'] = mb_substr($person->nombre, 0, 50);
        $this->formData['dni_remitente'] = $person->num_dni;
        $this->formData['direccion_remitente'] = mb_substr($person->direccion, 0, 100);
        $this->formData['fono_remitente'] = $person->telefono;
    }

    public function openQuickCustomerModal(): void
    {
        if (trim($this->clientSearch) === '') {
            $this->alertWarning('Falta el dato de búsqueda', 'Escribe un nombre o identificación para iniciar el registro rápido.');

            return;
        }

        $search = trim($this->clientSearch);
        $isIdentification = preg_match('/^\d+$/', $search) === 1;
        $this->quickCustomer = [
            'id_empresa' => (string) (Empresa::query()->where('estado', '1')->value('id_empresa') ?? ''),
            'id_cargo' => (string) (Cargo::query()->where('estado', '1')->value('id_cargo') ?? ''),
            'nombre' => $isIdentification ? '' : $search,
            'tipo_dni' => strlen($search) >= 13 ? 'R' : 'C',
            'num_dni' => $isIdentification ? $search : '',
            'direccion' => '',
            'telefono' => '',
        ];
        $this->resetValidation();
        $this->showQuickCustomerModal = true;
    }

    public function saveQuickCustomer(): void
    {
        $this->quickCustomer = $this->normalizeTextValues($this->quickCustomer);

        $validated = $this->validate([
            'quickCustomer.id_empresa' => ['required', 'exists:empresa,id_empresa'],
            'quickCustomer.id_cargo' => ['required', 'exists:cargo,id_cargo'],
            'quickCustomer.nombre' => ['required', 'string', 'max:100'],
            'quickCustomer.tipo_dni' => ['required', 'in:C,R,P'],
            'quickCustomer.num_dni' => ['required', 'string', 'max:20', Rule::unique('persona', 'num_dni')],
            'quickCustomer.direccion' => ['required', 'string', 'max:500'],
            'quickCustomer.telefono' => ['required', 'string', 'max:20'],
        ]);

        $customer = Persona::query()->create([
            ...$validated['quickCustomer'],
            'tipo_persona' => 'C',
            'estado' => '1',
        ]);

        $this->selectClient($customer->id_persona);
        $this->showQuickCustomerModal = false;
        $this->alertToast('Cliente creado y seleccionado en la guía.');
    }

    public function save(): void
    {
        $this->formData = $this->normalizeTextValues($this->formData);
        $this->detalles = $this->normalizeTextValues($this->detalles);

        $validated = $this->validate([
            'formData.id_sucursal' => ['required', 'exists:sucursal,id_sucursal'],
            'formData.id_motivo_traslado' => ['required', 'exists:motivo_traslado,id_motivo_traslado'],
            'formData.id_forma_pago' => ['required', 'exists:formas_pago,id_formas_pago'],
            'formData.id_persona' => [
                'required',
                Rule::exists('persona', 'id_persona')->where(fn ($query) => $query->where('tipo_persona', 'C')),
            ],
            'formData.ciudad_origen' => ['required', 'string', 'max:50'],
            'formData.ciudad_destino' => ['required', 'string', 'max:50'],
            'formData.nom_remitente' => ['required', 'string', 'max:50'],
            'formData.dni_remitente' => ['required', 'string', 'max:20'],
            'formData.direccion_remitente' => ['required', 'string', 'max:100'],
            'formData.fono_remitente' => ['required', 'string', 'max:20'],
            'formData.nom_destinatario' => ['required', 'string', 'max:50'],
            'formData.dni_destinatario' => ['nullable', 'string', 'max:20'],
            'formData.direccion_destinatario' => ['nullable', 'string', 'max:100'],
            'formData.fono_destinatario' => ['nullable', 'string', 'max:20'],
            'formData.num_guia' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('cabecera', 'num_guia')->ignore($this->selectedId, 'id_cabecera'),
            ],
            'formData.num_guia_trans' => ['required', 'integer', 'min:0'],
            'formData.fecha_emision' => ['required', 'date'],
            'formData.fecha_recepcion' => ['nullable', 'date'],
            'formData.fecha_cancelacion' => ['nullable', 'date'],
            'formData.guia_rem_cliente' => ['nullable', 'string', 'max:45'],
            'formData.factura_cliente' => ['nullable', 'string', 'max:45'],
            'formData.flete' => ['required', 'numeric', 'min:0'],
            'formData.recargo' => ['nullable', 'numeric', 'min:0'],
            'formData.prima' => ['nullable', 'numeric', 'min:0'],
            'formData.nombre_recibe' => ['nullable', 'string', 'max:45'],
            'formData.dni_recibe' => ['nullable', 'string', 'max:45'],
            'formData.estatus_entrega' => ['nullable', 'string', 'max:1'],
            'formData.estatus_cobro' => ['nullable', 'string', 'max:20'],
            'formData.estado' => ['required', 'in:0,1'],
            'formData.nota' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id_detalle' => ['nullable', 'integer'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.descripcion' => ['required', 'string', 'max:100'],
            'detalles.*.v_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated): void {
            $guideData = $validated['formData'];
            foreach (['fecha_recepcion', 'fecha_cancelacion'] as $dateField) {
                $guideData[$dateField] = $guideData[$dateField] ?: null;
            }
            $guideData['valor_guia'] = round(
                (float) $guideData['flete'] + (float) ($guideData['recargo'] ?? 0) + (float) ($guideData['prima'] ?? 0),
                2,
            );
            $guideData['id_users'] = auth()->id();
            $guideData['id_users_update'] = $this->isEditing ? auth()->id() : null;

            $guide = $this->isEditing
                ? Cabecera::query()->findOrFail($this->selectedId)
                : new Cabecera;
            $guide->fill($guideData);
            $guide->save();

            $retainedDetailIds = [];

            foreach ($validated['detalles'] as $line) {
                $detailData = [
                    'cantidad' => (int) $line['cantidad'],
                    'descripcion' => $line['descripcion'],
                    'v_unitario' => round((float) $line['v_unitario'], 2),
                    'v_parcial' => round((int) $line['cantidad'] * (float) $line['v_unitario'], 2),
                ];

                if (! empty($line['id_detalle'])) {
                    $detail = $guide->detalles()->whereKey($line['id_detalle'])->firstOrFail();
                    $detail->update($detailData);
                    $retainedDetailIds[] = $detail->id_detalle;

                    continue;
                }

                $detail = $guide->detalles()->create($detailData);
                $retainedDetailIds[] = $detail->id_detalle;
            }

            $guide->detalles()->whereNotIn('id_detalle', $retainedDetailIds)->delete();
        });

        $this->showModal = false;
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $guide = Cabecera::query()->findOrFail($id);
        $this->deleteId = $id;
        $this->deleteLabel = (string) $guide->num_guia;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deleteId === null) {
            $this->alertError('No se pudo eliminar', 'Selecciona una guía válida e inténtalo nuevamente.');

            return;
        }

        Cabecera::query()->findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteLabel = '';
        $this->alertToast('Guía eliminada correctamente.');
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.resources.cabecera-manager', [
            'guides' => Cabecera::query()
                ->with(['persona', 'sucursal.ciudad'])
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('num_guia', 'like', "%{$search}%")
                            ->orWhere('nom_remitente', 'like', "%{$search}%")
                            ->orWhere('nom_destinatario', 'like', "%{$search}%")
                            ->orWhereHas('persona', fn ($personQuery) => $personQuery->where('nombre', 'like', "%{$search}%")->orWhere('num_dni', 'like', "%{$search}%"));
                    });
                })
                ->latest('id_cabecera')
                ->paginate(10),
            'sucursales' => Sucursal::query()
                ->with([
                    'empresa' => fn ($query) => $query->withTrashed(),
                    'ciudad' => fn ($query) => $query->withTrashed(),
                ])
                ->orderBy('id_sucursal')
                ->get(),
            'motivos' => MotivoTraslado::query()->orderBy('descripcion')->get(),
            'formasPago' => FormasPago::query()->orderBy('descripcion')->get(),
            'ciudades' => Ciudad::query()->orderBy('descripcion')->get(),
            'clientes' => $this->clientSearchResults(),
            'empresas' => Empresa::query()->where('estado', '1')->orderBy('razon_social')->get(),
            'cargos' => Cargo::query()->where('estado', '1')->orderBy('descripcion')->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormData(): array
    {
        $nextNumber = ((int) Cabecera::withTrashed()->max('num_guia')) + 1;

        return [
            'id_sucursal' => '',
            'id_motivo_traslado' => '',
            'id_forma_pago' => '',
            'id_persona' => '',
            'ciudad_origen' => '',
            'ciudad_destino' => '',
            'nom_remitente' => '',
            'dni_remitente' => '',
            'direccion_remitente' => '',
            'fono_remitente' => '',
            'nom_destinatario' => '',
            'dni_destinatario' => '',
            'direccion_destinatario' => '',
            'fono_destinatario' => '',
            'num_guia' => $nextNumber,
            'num_guia_trans' => $nextNumber,
            'fecha_emision' => now()->format('Y-m-d\TH:i'),
            'fecha_recepcion' => '',
            'fecha_cancelacion' => '',
            'guia_rem_cliente' => '',
            'factura_cliente' => '',
            'flete' => '0.00',
            'recargo' => '0.00',
            'prima' => '0.00',
            'nombre_recibe' => '',
            'dni_recibe' => '',
            'estatus_entrega' => '',
            'estatus_cobro' => '',
            'estado' => '1',
            'nota' => '',
        ];
    }

    /**
     * @return array{id_detalle: null, cantidad: int, descripcion: string, v_unitario: string}
     */
    private function emptyDetail(): array
    {
        return [
            'id_detalle' => null,
            'cantidad' => 1,
            'descripcion' => '',
            'v_unitario' => '0.00',
        ];
    }

    private function loadGuide(int $id): void
    {
        $guide = Cabecera::query()
            ->with(['persona' => fn ($query) => $query->withTrashed(), 'detalles'])
            ->findOrFail($id);
        $this->selectedId = $guide->id_cabecera;
        $this->formData = $guide->only(array_keys($this->defaultFormData()));
        $this->formData['fecha_emision'] = $guide->fecha_emision->format('Y-m-d\TH:i');
        $this->formData['fecha_recepcion'] = $guide->fecha_recepcion?->format('Y-m-d\TH:i') ?? '';
        $this->formData['fecha_cancelacion'] = $guide->fecha_cancelacion?->format('Y-m-d\TH:i') ?? '';
        $this->clientSearch = "{$guide->persona->nombre} · {$guide->persona->num_dni}";
        $this->detalles = $guide->detalles->map(fn ($detail) => [
            'id_detalle' => $detail->id_detalle,
            'cantidad' => $detail->cantidad,
            'descripcion' => $detail->descripcion,
            'v_unitario' => $detail->v_unitario,
        ])->all();

        if ($this->detalles === []) {
            $this->detalles = [$this->emptyDetail()];
        }
    }

    /**
     * @return Collection<int, Persona>
     */
    private function clientSearchResults(): Collection
    {
        $search = trim($this->clientSearch);

        if (mb_strlen($search) < 2) {
            return Persona::query()->whereRaw('1 = 0')->get();
        }

        return Persona::query()
            ->where('tipo_persona', 'C')
            ->where('estado', '1')
            ->where(function ($query) use ($search): void {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('num_dni', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->limit(8)
            ->get();
    }
}
