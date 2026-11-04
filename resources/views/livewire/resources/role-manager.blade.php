<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Seguridad</p>
            <h1>Roles y permisos</h1>
            <p class="muted">Define roles y permisos disponibles para las cuentas del sistema.</p>
        </div>
        <button wire:click="create" class="button primary" type="button">+ Nuevo rol</button>
    </div>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" type="search" class="control" placeholder="Buscar roles..." aria-label="Buscar roles">
            </div>
            <span>Total: <strong>{{ $roles->total() }}</strong></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Rol</th><th>Guard</th><th>Permisos</th><th>Usuarios</th><th class="actions-heading">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td><strong>{{ $role->name }}</strong></td>
                            <td>{{ $role->guard_name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="view({{ $role->id }})" class="text-button" type="button">Ver</button>
                                    <button wire:click="edit({{ $role->id }})" class="text-button" type="button">Editar</button>
                                    <button wire:click="prepareDelete({{ $role->id }})" class="text-button danger" type="button">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><strong>No se encontraron roles</strong><span>Crea un rol para comenzar a asignar permisos.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="pagination-container">{{ $roles->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="role-modal-title">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">{{ $isViewing ? 'Consulta' : ($isEditing ? 'Modificación' : 'Nuevo rol') }}</p>
                        <h2 id="role-modal-title">{{ $isViewing ? 'Detalle del rol' : ($isEditing ? 'Editar rol' : 'Crear rol') }}</h2>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="role-name">Nombre del rol *</label>
                                <input wire:model="name" id="role-name" type="text" class="control" @disabled($isViewing)>
                                @error('name') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="role-guard">Guard *</label>
                                <x-searchable-select wire:model="guard_name" id="role-guard" class="control" @disabled($isViewing)>
                                    <option value="web">web</option>
                                </x-searchable-select>
                                @error('guard_name') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label>Permisos</label>
                                <div class="form-grid">
                                    @forelse ($permissions as $permission)
                                        <label class="inline-flex items-center gap-2" wire:key="permission-{{ $permission->id }}">
                                            <input type="checkbox" wire:model="permissionIds" value="{{ $permission->id }}" @disabled($isViewing)>
                                            <span>{{ $permission->name }}</span>
                                        </label>
                                    @empty
                                        <span class="muted">No hay permisos registrados para el guard web.</span>
                                    @endforelse
                                </div>
                                @error('permissionIds.*') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar rol</button>
                        @endunless
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="alertdialog" aria-modal="true" style="max-width:480px;">
                <header class="resource-modal-header"><div><p class="eyebrow">Confirmar acción</p><h2>Eliminar rol</h2></div></header>
                <div class="resource-modal-body"><p>¿Deseas eliminar el rol <strong>{{ $deleteLabel }}</strong>?</p></div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmDelete" type="button" class="button danger">Eliminar</button>
                </footer>
            </section>
        </div>
    @endif
</div>
