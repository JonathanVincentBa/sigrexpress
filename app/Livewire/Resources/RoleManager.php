<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Roles y Permisos')]
class RoleManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    public string $search = '';

    public string $name = '';

    public string $guard_name = 'web';

    /** @var array<int, string|int> */
    public array $permissionIds = [];

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
        $role = Role::query()->with('permissions')->findOrFail($id);
        $this->selectedId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->permissionIds = $role->permissions->pluck('id')->all();
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
        $this->name = $this->normalizeTextValues(['name' => $this->name])['name'];

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', $this->guard_name)->ignore($this->selectedId),
            ],
            'guard_name' => ['required', 'string', 'max:255', 'in:web'],
            'permissionIds' => ['array'],
            'permissionIds.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'web')],
        ]);

        $role = $this->isEditing
            ? Role::query()->findOrFail($this->selectedId)
            : Role::query()->create(['name' => $validated['name'], 'guard_name' => $validated['guard_name']]);

        if ($this->isEditing) {
            $role->update(['name' => $validated['name'], 'guard_name' => $validated['guard_name']]);
        }

        $role->syncPermissions($validated['permissionIds'] ?? []);
        $this->showModal = false;
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $role = Role::query()->findOrFail($id);
        $this->deleteId = $id;
        $this->deleteLabel = $role->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deleteId === null) {
            $this->alertError('No se pudo eliminar', 'Selecciona un rol válido e inténtalo nuevamente.');

            return;
        }

        Role::query()->findOrFail($this->deleteId)->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteLabel = '';
        $this->alertToast('Rol eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.resources.role-manager', [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->withCount(['users', 'permissions'])
                ->when($this->search !== '', function ($query): void {
                    $search = trim($this->search);
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(10),
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'permissionIds', 'selectedId']);
        $this->guard_name = 'web';
        $this->isEditing = false;
        $this->isViewing = false;
        $this->resetValidation();
    }
}
