<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

abstract class BaseCatalogManager extends Component
{
    use NormalizesTextInput, WithFileUploads, WithPagination, WithSweetAlert;

    public string $search = '';

    public string $statusFilter = '';

    /** @var array<string, string> */
    public array $filters = [];

    /** @var array<string, mixed> */
    public array $formData = [];

    /** @var array<string, mixed> */
    public array $uploads = [];

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $isEditing = false;

    public bool $isViewing = false;

    public ?int $selectedId = null;

    public ?int $deleteId = null;

    public string $deleteLabel = '';

    /**
     * @return array{
     *     model: class-string<Model>,
     *     title: string,
     *     description: string,
     *     view: string,
     *     columns: array<int, array{key: string, label: string, format?: string, map?: array<string, string>, suffix?: string}>,
     *     search: array<int, string>,
     *     fields: array<string, array{label: string, type: string, rules: string|array<int, mixed>, default?: mixed, options?: array<string|int, string>, placeholder?: string, wide?: bool}>,
     *     with?: array<int, string>,
     *     withCount?: array<int, string>,
     *     filters?: array<string, array{label: string, options: array<string, string>}>,
     *     searchPlaceholder?: string
     * }
     */
    abstract protected function definition(): array;

    public function mount(): void
    {
        $this->filters = array_fill_keys(array_keys($this->definition()['filters'] ?? []), '');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->selectedId = null;
        $this->isEditing = false;
        $this->isViewing = false;
        $this->formData = $this->defaultFormData();
        $this->reset('uploads');
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->loadRecord($id);
        $this->reset('uploads');
        $this->isEditing = true;
        $this->isViewing = false;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function view(int $id): void
    {
        $this->loadRecord($id);
        $this->reset('uploads');
        $this->isEditing = true;
        $this->isViewing = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->formData = $this->normalizeTextValues($this->formData);
        $rules = [];

        foreach ($this->definition()['fields'] as $name => $field) {
            if ($field['type'] === 'file') {
                $rules["uploads.{$name}"] = $field['rules'];

                continue;
            }

            $rules["formData.{$name}"] = $field['rules'];
        }

        $validated = $this->validate($rules);
        $modelClass = $this->definition()['model'];
        $record = $this->isEditing
            ? $modelClass::query()->findOrFail($this->selectedId)
            : new $modelClass;

        $formData = $validated['formData'] ?? [];

        foreach ($this->definition()['fields'] as $name => $field) {
            if ($field['type'] !== 'file') {
                continue;
            }

            if (isset($this->uploads[$name])) {
                $path = $this->uploads[$name]->storePublicly('logos', 'public');

                if ($path === false) {
                    throw new \RuntimeException('No se pudo almacenar el logotipo de la empresa.');
                }

                $formData[$name] = $path;
            } elseif (! $this->isEditing) {
                $formData[$name] = null;
            }
        }

        $record->fill($formData);
        $record->save();

        $this->showModal = false;
        $this->reset('uploads');
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $definition = $this->definition();
        $record = $definition['model']::query()->findOrFail($id);
        $labelKey = $definition['columns'][0]['key'];

        $this->deleteId = $id;
        $this->deleteLabel = (string) data_get($record, $labelKey, "Registro #{$id}");
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deleteId === null) {
            $this->alertError('No se pudo eliminar', 'Selecciona un registro válido e inténtalo nuevamente.');

            return;
        }

        $modelClass = $this->definition()['model'];
        $modelClass::query()->findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteLabel = '';

        $this->alertToast('Registro eliminado correctamente.');
    }

    public function render()
    {
        $definition = $this->definition();
        $query = $this->query($definition);

        return view($definition['view'], [
            'records' => $query->latest()->paginate(10),
            'catalogTitle' => $definition['title'],
            'catalogDescription' => $definition['description'],
            'columns' => $definition['columns'],
            'fields' => $definition['fields'],
            'hasStatus' => array_key_exists('estado', $definition['fields']),
            'extraFilters' => $definition['filters'] ?? [],
            'searchPlaceholder' => $definition['searchPlaceholder'] ?? 'Buscar registros...',
        ]);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function query(array $definition): Builder
    {
        $modelClass = $definition['model'];
        $query = $modelClass::query()
            ->with($definition['with'] ?? [])
            ->withCount($definition['withCount'] ?? []);

        if (trim($this->search) !== '') {
            $search = trim($this->search);
            $query->where(function (Builder $searchQuery) use ($definition, $search): void {
                foreach ($definition['search'] as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relatedColumn] = explode('.', $column, 2);
                        $searchQuery->orWhereHas($relation, function (Builder $relatedQuery) use ($relatedColumn, $search): void {
                            $relatedQuery->where($relatedColumn, 'like', "%{$search}%");
                        });

                        continue;
                    }

                    $searchQuery->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($this->statusFilter !== '' && array_key_exists('estado', $definition['fields'])) {
            $query->where('estado', $this->statusFilter);
        }

        foreach ($definition['filters'] ?? [] as $column => $filter) {
            if (($this->filters[$column] ?? '') !== '') {
                $query->where($column, $this->filters[$column]);
            }
        }

        return $query;
    }

    private function loadRecord(int $id): void
    {
        $modelClass = $this->definition()['model'];
        $record = $modelClass::query()->findOrFail($id);
        $this->selectedId = $id;
        $this->formData = [];

        foreach ($this->definition()['fields'] as $name => $field) {
            $this->formData[$name] = $record->getAttribute($name) ?? ($field['default'] ?? null);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormData(): array
    {
        $formData = [];

        foreach ($this->definition()['fields'] as $name => $field) {
            $formData[$name] = $field['default'] ?? ($field['type'] === 'file' ? null : '');
        }

        return $formData;
    }
}
