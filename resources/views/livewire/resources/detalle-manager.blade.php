<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Operación y envíos</p>
            <h1>Detalles de guías</h1>
            <p class="muted">Administra las líneas de artículos vinculadas a una guía de remisión.</p>
        </div>
        <button wire:click="create" class="button primary" type="button">+ Nuevo detalle</button>
    </div>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" type="search" class="control" placeholder="Buscar por número de guía o descripción..." aria-label="Buscar detalles">
            </div>
            <span>Total: <strong>{{ $details->total() }}</strong></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Guía</th><th>Artículo / descripción</th><th>Cantidad</th><th>Valor unitario</th><th>Valor parcial</th><th class="actions-heading">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($details as $detail)
                        <tr wire:key="detail-{{ $detail->id_detalle }}">
                            <td>#{{ $detail->cabecera?->num_guia ?? '—' }}</td>
                            <td>{{ $detail->descripcion }}</td>
                            <td>{{ $detail->cantidad }}</td>
                            <td>${{ number_format((float) $detail->v_unitario, 2) }}</td>
                            <td>${{ number_format((float) $detail->v_parcial, 2) }}</td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="view({{ $detail->id_detalle }})" class="text-button" type="button">Ver</button>
                                    <button wire:click="edit({{ $detail->id_detalle }})" class="text-button" type="button">Editar</button>
                                    <button wire:click="prepareDelete({{ $detail->id_detalle }})" class="text-button danger" type="button">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><strong>No se encontraron detalles</strong><span>Agrega una línea o ajusta la búsqueda.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($details->hasPages())
            <div class="pagination-container">{{ $details->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="detail-modal-title" style="max-width:640px;">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">{{ $isViewing ? 'Consulta' : ($isEditing ? 'Modificación' : 'Nuevo detalle') }}</p>
                        <h2 id="detail-modal-title">{{ $isViewing ? 'Detalle de línea' : ($isEditing ? 'Editar línea' : 'Agregar línea') }}</h2>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field field-wide">
                                <label for="detail-guide">Guía de remisión *</label>
                                <x-searchable-select wire:model="id_cabecera" id="detail-guide" class="control" @disabled($isViewing)>
                                    <option value="">Selecciona una guía</option>
                                    @foreach ($guides as $guide)
                                        <option value="{{ $guide->id_cabecera }}">N.º {{ $guide->num_guia }} · {{ $guide->nom_destinatario }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('id_cabecera') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="detail-quantity">Cantidad *</label>
                                <input wire:model.live="cantidad" id="detail-quantity" type="number" class="control" min="1" @disabled($isViewing)>
                                @error('cantidad') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="detail-unit-value">Valor unitario *</label>
                                <input wire:model.live="v_unitario" id="detail-unit-value" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                @error('v_unitario') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="detail-description">Artículo / descripción *</label>
                                <input wire:model="descripcion" id="detail-description" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                @error('descripcion') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label>Valor parcial</label>
                                <div class="control">${{ number_format((int) $cantidad * (float) $v_unitario, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar detalle</button>
                        @endunless
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="alertdialog" aria-modal="true" style="max-width:480px;">
                <header class="resource-modal-header"><div><p class="eyebrow">Confirmar acción</p><h2>Eliminar detalle</h2></div></header>
                <div class="resource-modal-body"><p>¿Deseas eliminar la línea <strong>{{ $deleteLabel }}</strong>?</p></div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmDelete" type="button" class="button danger">Eliminar</button>
                </footer>
            </section>
        </div>
    @endif
</div>
