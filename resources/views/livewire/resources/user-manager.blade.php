<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Seguridad</p>
            <h1>Usuarios</h1>
            <p class="muted">Administra las cuentas, su estado, el personal vinculado, su sucursal y los roles asignados.</p>
        </div>
        <button wire:click="create" class="button primary" type="button">+ Nuevo usuario</button>
    </div>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" type="search" class="control" placeholder="Buscar por nombre, correo o identificación..." aria-label="Buscar usuarios">
            </div>
            <div class="heading-actions">
                <x-searchable-select wire:model.live="statusFilter" class="control" aria-label="Filtrar usuarios por estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </x-searchable-select>
                <x-searchable-select wire:model.live="roleFilter" class="control" aria-label="Filtrar usuarios por rol">
                    <option value="">Todos los roles</option>
                    @foreach ($availableRoles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </x-searchable-select>
                <x-searchable-select wire:model.live="branchFilter" class="control" aria-label="Filtrar usuarios por sucursal">
                    <option value="">Todas las sucursales</option>
                    @foreach ($availableSucursales as $sucursal)
                        <option value="{{ $sucursal->id_sucursal }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </x-searchable-select>
                <span>Total: <strong>{{ $users->total() }}</strong></span>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Personal vinculado</th>
                        <th>Sucursal</th>
                        <th>Roles</th>
                        <th>Estado</th>
                        <th class="actions-heading">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td><strong>{{ $user->name }}</strong><span style="display:block; color:#77849a;">{{ $user->email }}</span></td>
                            <td>{{ $user->persona?->nombre ?? '—' }}</td>
                            <td>{{ $user->sucursal?->nombre ?? 'Sin sucursal' }}</td>
                            <td>{{ $user->roles->pluck('name')->join(', ') ?: 'Sin roles' }}</td>
                            <td><span class="badge {{ $user->estado === '1' ? 'active' : '' }}">{{ $user->estado === '1' ? 'Activo' : 'Inactivo' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="view({{ $user->id }})" class="text-button" type="button">Ver</button>
                                    <button wire:click="edit({{ $user->id }})" class="text-button" type="button">Editar</button>
                                    <button wire:click="prepareDelete({{ $user->id }})" class="text-button danger" type="button">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><strong>No se encontraron usuarios</strong><span>Ajusta los filtros o crea una cuenta nueva.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="pagination-container">{{ $users->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">{{ $isViewing ? 'Consulta' : ($isEditing ? 'Modificación' : 'Nueva cuenta') }}</p>
                        <h2 id="user-modal-title">{{ $isViewing ? 'Detalle del usuario' : ($isEditing ? 'Editar usuario' : 'Crear usuario') }}</h2>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="user-name">Nombre *</label>
                                <input wire:model="name" id="user-name" type="text" class="control" autocomplete="name" @disabled($isViewing)>
                                @error('name') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="user-email">Correo electrónico *</label>
                                <input wire:model="email" id="user-email" type="email" class="control" autocomplete="email" @disabled($isViewing)>
                                @error('email') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="user-persona">Persona vinculada</label>
                                @if ($selectedPersona)
                                    <div class="control flex items-center justify-between gap-3">
                                        <span>{{ $selectedPersona->nombre }} · {{ $selectedPersona->num_dni }}</span>
                                        @unless ($isViewing)
                                            <button wire:click="clearPersona" type="button" class="text-button">Quitar</button>
                                        @endunless
                                    </div>
                                @elseif ($isViewing)
                                    <div class="control">Sin persona vinculada</div>
                                @else
                                    <input
                                        wire:model.live.debounce.300ms="personaSearch"
                                        id="user-persona"
                                        type="search"
                                        class="control"
                                        maxlength="100"
                                        placeholder="Buscar por nombre o identificación"
                                        autocomplete="off"
                                    >
                                    @if (mb_strlen(trim($personaSearch)) >= 2)
                                        <div class="mt-2 grid gap-1">
                                            @forelse ($personaResults as $persona)
                                                <button
                                                    wire:key="persona-result-{{ $persona->id_persona }}"
                                                    wire:click="selectPersona({{ $persona->id_persona }})"
                                                    type="button"
                                                    class="control text-left"
                                                >
                                                    {{ $persona->nombre }} · {{ $persona->num_dni }}
                                                </button>
                                            @empty
                                                <span class="muted">No se encontraron personas.</span>
                                            @endforelse
                                        </div>
                                    @else
                                        <span class="muted">Escribe al menos dos caracteres para buscar; este campo es opcional.</span>
                                    @endif
                                    <button wire:click="clearPersona" type="button" class="text-button">Sin persona vinculada</button>
                                @endif
                                @error('id_persona') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="user-state">Estado *</label>
                                <x-searchable-select wire:model="estado" id="user-state" class="control" @disabled($isViewing)>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </x-searchable-select>
                                @error('estado') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="user-branch">Sucursal asignada</label>
                                <x-searchable-select wire:model="id_sucursal" id="user-branch" class="control" @disabled($isViewing)>
                                    <option value="">Seleccione una sucursal</option>
                                    @foreach ($availableSucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}">{{ $sucursal->nombre }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('id_sucursal') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            @unless ($isViewing)
                                <div class="field">
                                    <label for="user-password">Contraseña {{ $isEditing ? '(dejar en blanco para conservar)' : '*' }}</label>
                                    <input wire:model="password" id="user-password" type="password" class="control" autocomplete="new-password">
                                    @error('password') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="user-password-confirmation">Confirmar contraseña</label>
                                    <input wire:model="password_confirmation" id="user-password-confirmation" type="password" class="control" autocomplete="new-password">
                                </div>
                            @endunless
                            <div class="field field-wide">
                                <label>Roles</label>
                                <div class="form-grid">
                                    @forelse ($availableRoles as $role)
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" wire:model="roles" value="{{ $role->id }}" @disabled($isViewing)>
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @empty
                                        <span class="muted">No hay roles creados.</span>
                                    @endforelse
                                </div>
                                @error('roles.*') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar usuario</button>
                        @endunless
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="alertdialog" aria-modal="true" style="max-width:480px;">
                <header class="resource-modal-header"><div><p class="eyebrow">Confirmar acción</p><h2>Eliminar usuario</h2></div></header>
                <div class="resource-modal-body"><p>¿Deseas eliminar la cuenta <strong>{{ $deleteLabel }}</strong>?</p></div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmDelete" type="button" class="button danger">Eliminar</button>
                </footer>
            </section>
        </div>
    @endif
</div>