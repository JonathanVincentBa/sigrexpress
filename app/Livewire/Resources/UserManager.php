<?php

namespace App\Livewire\Resources;

use App\Concerns\NormalizesTextInput;
use App\Concerns\WithSweetAlert;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
#[Title('Usuarios del Sistema')]
class UserManager extends Component
{
    use NormalizesTextInput, WithPagination, WithSweetAlert;

    public string $search = '';

    public string $statusFilter = '';

    public string $roleFilter = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $id_persona = '';

    public string $estado = '1';

    /** @var array<int, string|int> */
    public array $roles = [];

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

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
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
        $user = User::query()->with('roles')->findOrFail($id);
        $this->selectedId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->id_persona = (string) ($user->id_persona ?? '');
        $this->estado = $user->estado;
        $this->roles = $user->roles->pluck('id')->all();
        $this->password = '';
        $this->password_confirmation = '';
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
            'name' => $this->name,
            'email' => $this->email,
            'id_persona' => $this->id_persona,
            'estado' => $this->estado,
        ]);
        $this->name = $normalized['name'];
        $this->email = $normalized['email'];
        $this->id_persona = $normalized['id_persona'];
        $this->estado = $normalized['estado'];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->selectedId)],
            'id_persona' => ['nullable', 'integer', 'exists:persona,id_persona'],
            'estado' => ['required', 'in:0,1'],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ];

        if (! $this->isEditing) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);
        $user = $this->isEditing ? User::query()->findOrFail($this->selectedId) : new User;

        unset($validated['password_confirmation']);
        if ($this->isEditing && $this->password === '') {
            unset($validated['password']);
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'id_persona' => $validated['id_persona'] ?: null,
            'estado' => $validated['estado'],
        ];

        if (array_key_exists('password', $validated)) {
            $userData['password'] = $validated['password'];
        }

        $user->fill($userData);
        $user->save();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereKey($validated['roles'] ?? [])
            ->get();

        $user->syncRoles($roles);

        $this->showModal = false;
        $this->alertSuccess();
    }

    public function prepareDelete(int $id): void
    {
        $user = User::query()->findOrFail($id);

        if ($user->is(auth()->user())) {
            $this->alertWarning('Acción no permitida', 'No puedes eliminar la cuenta con la que iniciaste sesión.');

            return;
        }

        $this->deleteId = $id;
        $this->deleteLabel = $user->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deleteId === null) {
            $this->alertError('No se pudo eliminar', 'Selecciona un usuario válido e inténtalo nuevamente.');

            return;
        }

        $user = User::query()->findOrFail($this->deleteId);

        if ($user->cabeceras()->exists()) {
            $this->alertError('No se puede eliminar', 'El usuario tiene guías asociadas.');
            $this->showDeleteModal = false;

            return;
        }

        $user->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteLabel = '';
        $this->alertToast('Usuario eliminado correctamente.');
    }

    public function render()
    {
        $users = User::query()
            ->with(['persona', 'roles'])
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('persona', fn ($personaQuery) => $personaQuery->where('num_dni', 'like', "%{$search}%"));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('estado', $this->statusFilter))
            ->when($this->roleFilter !== '', fn ($query) => $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereKey($this->roleFilter)))
            ->latest('id')
            ->paginate(10);

        return view('livewire.resources.user-manager', [
            'users' => $users,
            'personas' => Persona::query()->orderBy('nombre')->get(['id_persona', 'nombre', 'num_dni']),
            'availableRoles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'password',
            'password_confirmation',
            'id_persona',
            'selectedId',
            'roles',
        ]);
        $this->estado = '1';
        $this->isEditing = false;
        $this->isViewing = false;
        $this->resetValidation();
    }
}
