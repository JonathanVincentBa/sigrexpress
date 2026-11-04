<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Operación logística</p>
            <h1>Seguimiento de guías</h1>
            <p class="muted">Actualiza el estado de transporte y conserva el historial de cada cambio.</p>
        </div>
    </div>

    <section class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" class="control" type="search" placeholder="Buscar por número, remitente o destinatario..." aria-label="Buscar guía">
            </div>
            <x-searchable-select wire:model.live="statusFilter" class="control" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                @foreach ($states as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </x-searchable-select>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>N.º guía</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estado actual</th>
                        <th>Última actualización</th>
                        <th class="actions-heading">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guides as $guide)
                        <tr wire:key="guide-status-{{ $guide->id_cabecera }}">
                            <td><strong>{{ $guide->num_guia }}</strong></td>
                            <td>{{ $guide->nom_remitente }}</td>
                            <td>{{ $guide->nom_destinatario }}</td>
                            <td>{{ $guide->ciudad_origen }}</td>
                            <td>{{ $guide->ciudad_destino }}</td>
                            <td><span class="badge active">{{ $guide->estado_tracking }}</span></td>
                            <td>{{ $guide->historialEstados->first()?->created_at?->format('d/m/Y H:i') ?? 'Sin cambios registrados' }}</td>
                            <td><button wire:click="edit({{ $guide->id_cabecera }})" type="button" class="text-button">Actualizar</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state"><strong>No hay guías disponibles</strong><span>Prueba con otros filtros.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($guides->hasPages())
            <div class="pagination-container">{{ $guides->links() }}</div>
        @endif
    </section>

    @if ($showModal)
        <div class="resource-modal-backdrop" role="presentation">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="guide-status-title">
                <header class="resource-modal-header">
                    <div><p class="eyebrow">Trazabilidad</p><h2 id="guide-status-title">Actualizar guía</h2></div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field field-wide">
                                <label for="tracking-status">Estado de entrega *</label>
                                <x-searchable-select wire:model="estado" id="tracking-status" class="control">
                                    @foreach ($states as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('estado') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="tracking-observation">Observación</label>
                                <textarea wire:model="observacion" id="tracking-observation" class="control" rows="3" maxlength="1000"></textarea>
                                @error('observacion') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">Cancelar</button>
                        <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar estado</button>
                    </footer>
                </form>
            </section>
        </div>
    @endif
</div>
