<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Mantenimiento de catálogos</p>
            <h1>{{ $catalogTitle }}</h1>
            <p class="muted">{{ $catalogDescription }}</p>
        </div>
        <div class="heading-actions">
            <button wire:click="create" class="button primary" type="button">+ Nuevo registro</button>
        </div>
    </div>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" type="search" class="control" placeholder="{{ $searchPlaceholder }}" aria-label="Buscar registros">
            </div>
            <div class="heading-actions">
                @if ($hasStatus)
                    <x-searchable-select wire:model.live="statusFilter" class="control" aria-label="Filtrar por estado">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </x-searchable-select>
                @endif
                @foreach ($extraFilters as $name => $filter)
                    <x-searchable-select wire:model.live="filters.{{ $name }}" class="control" aria-label="{{ $filter['label'] }}">
                        <option value="">Todos: {{ $filter['label'] }}</option>
                        @foreach ($filter['options'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-searchable-select>
                @endforeach
                <span class="text-sm text-slate-500">Total: <strong>{{ $records->total() }}</strong></span>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th class="actions-heading">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr wire:key="catalog-record-{{ $record->getKey() }}">
                            @foreach ($columns as $column)
                                @php($value = data_get($record, $column['key']))
                                <td>
                                    @if (($column['format'] ?? null) === 'status')
                                        <span class="badge {{ $value === '1' || $value === 1 ? 'active' : '' }}">
                                            {{ $value === '1' || $value === 1 ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    @elseif (isset($column['map']))
                                        {{ $column['map'][(string) $value] ?? $value ?? '—' }}
                                    @else
                                        {{ $value ?? '—' }}{{ $value !== null ? ($column['suffix'] ?? '') : '' }}
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="row-actions">
                                    <button wire:click="view({{ $record->getKey() }})" class="text-button" type="button">Ver</button>
                                    <button wire:click="edit({{ $record->getKey() }})" class="text-button" type="button">Editar</button>
                                    <button wire:click="prepareDelete({{ $record->getKey() }})" class="text-button danger" type="button">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 1 }}">
                                <div class="empty-state">
                                    <strong>No se encontraron registros</strong>
                                    <span>Ajusta los filtros o crea un nuevo registro.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="pagination-container">{{ $records->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="resource-modal-backdrop" role="presentation">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="catalog-modal-title">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">{{ $isViewing ? 'Consulta' : ($isEditing ? 'Modificación' : 'Nuevo registro') }}</p>
                        <h2 id="catalog-modal-title">
                            {{ $isViewing ? 'Detalle de '.$catalogTitle : ($isEditing ? 'Editar '.$catalogTitle : 'Crear '.$catalogTitle) }}
                        </h2>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>

                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            @foreach ($fields as $name => $field)
                                <div class="field {{ ($field['wide'] ?? false) ? 'field-wide' : '' }}">
                                    <label for="catalog-{{ $name }}">{{ $field['label'] }}{{ collect(\Illuminate\Support\Arr::wrap($field['rules']))->contains(fn ($rule) => is_string($rule) && str_starts_with($rule, 'required')) ? ' *' : '' }}</label>
                                    @if ($field['type'] === 'select')
                                        <x-searchable-select wire:model="formData.{{ $name }}" id="catalog-{{ $name }}" class="control" @disabled($isViewing)>
                                            <option value="">Selecciona una opción</option>
                                            @foreach ($field['options'] ?? [] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </x-searchable-select>
                                    @elseif ($field['type'] === 'textarea')
                                        <textarea wire:model="formData.{{ $name }}" id="catalog-{{ $name }}" class="control" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}" @disabled($isViewing)></textarea>
                                    @elseif ($field['type'] === 'file')
                                        <input wire:model="uploads.{{ $name }}" id="catalog-{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp" class="control" @disabled($isViewing)>
                                        @if (($uploads[$name] ?? null) && $uploads[$name]->isPreviewable())
                                            <img src="{{ $uploads[$name]->temporaryUrl() }}" alt="Vista previa del logotipo" style="display:block;max-height:120px;margin-top:10px;">
                                        @elseif (! empty($formData[$name]))
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($formData[$name]) }}" alt="Logotipo actual de la empresa" style="display:block;max-height:120px;margin-top:10px;">
                                        @endif
                                    @else
                                        <input wire:model="formData.{{ $name }}" id="catalog-{{ $name }}" type="{{ $field['type'] }}" class="control" placeholder="{{ $field['placeholder'] ?? '' }}" @disabled($isViewing) @if ($field['type'] === 'number') step="0.01" min="0" @endif>
                                    @endif
                                    @error('formData.'.$name) <span class="field-error">{{ $message }}</span> @enderror
                                    @error('uploads.'.$name) <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar</button>
                        @endunless
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="resource-modal-backdrop" role="presentation">
            <section class="resource-modal" role="alertdialog" aria-modal="true" aria-labelledby="catalog-delete-title" style="max-width: 480px;">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">Confirmar acción</p>
                        <h2 id="catalog-delete-title">Eliminar registro</h2>
                    </div>
                    <button wire:click="$set('showDeleteModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <div class="resource-modal-body">
                    <p>¿Deseas eliminar <strong>{{ $deleteLabel }}</strong>? Esta acción no se puede deshacer.</p>
                </div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmDelete" type="button" class="button danger">Eliminar</button>
                </footer>
            </section>
        </div>
    @endif
</div>
