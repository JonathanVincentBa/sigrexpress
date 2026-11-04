<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Despacho de carga</p>
            <h1>Asignación de manifiestos</h1>
            <p class="muted">Agrupa guías disponibles por vehículo y chofer, y genera el manifiesto imprimible.</p>
        </div>
        <button wire:click="create" type="button" class="button primary">+ Nuevo manifiesto</button>
    </div>

    <section class="panel">
        <div class="panel-toolbar">
            <div class="search-box"><input wire:model.live.debounce.300ms="search" class="control" type="search" placeholder="Buscar por código o chofer..." aria-label="Buscar manifiesto"></div>
            <span class="muted">Los manifiestos cerrados conservan su historial.</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Código</th><th>Salida</th><th>Chofer</th><th>Vehículo</th><th>Guías</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse ($manifests as $manifest)
                        <tr wire:key="manifest-{{ $manifest->id_manifiesto }}">
                            <td><strong>{{ $manifest->codigo }}</strong></td>
                            <td>{{ $manifest->fecha_salida?->format('d/m/Y H:i') }}</td>
                            <td>{{ $manifest->chofer?->nombre ?? '—' }}</td>
                            <td>{{ $manifest->vehiculo?->placa ?? '—' }}</td>
                            <td>{{ $manifest->guias_count }}</td>
                            <td><span class="badge {{ $manifest->estado === 'CERRADO' ? 'active' : '' }}">{{ $manifest->estado }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="text-button" href="{{ route('manifiestos.pdf', $manifest) }}">Imprimir PDF</a>
                                    @if ($manifest->estado === 'EN CAMINO')
                                        <button wire:click="close({{ $manifest->id_manifiesto }})" class="text-button" type="button">Cerrar</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><strong>Aún no hay manifiestos</strong><span>Asigna guías para iniciar una ruta.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($manifests->hasPages())
            <div class="pagination-container">{{ $manifests->links() }}</div>
        @endif
    </section>

    @if ($showModal)
        <div class="resource-modal-backdrop" role="presentation">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="manifest-modal-title">
                <header class="resource-modal-header">
                    <div><p class="eyebrow">Despacho</p><h2 id="manifest-modal-title">Crear manifiesto</h2></div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="manifest-vehicle">Vehículo *</label>
                                <x-searchable-select wire:model.live="idVehiculo" id="manifest-vehicle" class="control">
                                    <option value="">Selecciona un vehículo</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id_vehiculo }}">{{ $vehicle->placa }} · {{ $vehicle->marca }} {{ $vehicle->modelo }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('idVehiculo') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="manifest-driver">Chofer *</label>
                                <x-searchable-select wire:model="idChofer" id="manifest-driver" class="control">
                                    <option value="">Selecciona un chofer</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id_persona }}">{{ $driver->nombre }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('idChofer') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="manifest-departure">Fecha y hora de salida *</label>
                                <input wire:model="fechaSalida" id="manifest-departure" class="control" type="datetime-local">
                                @error('fechaSalida') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="manifest-observation">Observación</label>
                                <textarea wire:model="observacion" id="manifest-observation" class="control" rows="2" maxlength="2000"></textarea>
                                @error('observacion') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label>Guías disponibles * (máximo 100 cargadas)</label>
                                <div class="table-wrap rounded-lg border border-slate-200">
                                    <table>
                                        <thead><tr><th>Seleccionar</th><th>Guía</th><th>Remitente</th><th>Destinatario</th><th>Ruta</th></tr></thead>
                                        <tbody>
                                            @forelse ($availableGuides as $guide)
                                                <tr wire:key="available-guide-{{ $guide->id_cabecera }}">
                                                    <td><input wire:model="selectedGuides" type="checkbox" value="{{ $guide->id_cabecera }}" aria-label="Seleccionar guía {{ $guide->num_guia }}"></td>
                                                    <td>{{ $guide->num_guia }}</td>
                                                    <td>{{ $guide->nom_remitente }}</td>
                                                    <td>{{ $guide->nom_destinatario }}</td>
                                                    <td>{{ $guide->ciudad_origen }} → {{ $guide->ciudad_destino }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5"><div class="empty-state"><strong>No hay guías en bodega</strong><span>Solo se pueden asignar guías activas aún no incluidas en otro manifiesto.</span></div></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @error('selectedGuides') <span class="field-error">{{ $message }}</span> @enderror
                                @error('selectedGuides.*') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">Cancelar</button>
                        <button type="submit" class="button primary" wire:loading.attr="disabled">Asignar e iniciar ruta</button>
                    </footer>
                </form>
            </section>
        </div>
    @endif
</div>
