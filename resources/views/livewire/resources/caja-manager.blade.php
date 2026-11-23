<div class="space-y-6">
    <section class="panel">
        <div class="panel-toolbar">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="m-0">Listado de Guías</h2>
                <button type="button" wire:click="cambiarVista('crear')" class="button primary">
                    Crear Caja
                </button>
                <button type="button" wire:click="cambiarVista('revision')" class="button secondary">
                    Revisión Cajas
                </button>
            </div>

            <div class="search-box">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="searchText"
                    placeholder="Buscar por número de guía, remitente o destinatario..."
                    class="control"
                >
                <button type="button" wire:click="$refresh" class="button primary">Buscar</button>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Guía</th>
                        <th>Usuario Creador</th>
                        <th class="whitespace-nowrap">C. Origen</th>
                        <th class="whitespace-nowrap">C. Destino</th>
                        <th>Remitente</th>
                        <th class="whitespace-nowrap">F. Creación</th>
                        <th class="text-right whitespace-nowrap">V. Guía</th>
                        <th class="whitespace-nowrap">Estado</th>
                        <th class="text-right whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guias as $guia)
                        <tr wire:key="guia-row-{{ $guia->id_cabecera }}">
                            <td class="font-semibold tabular-nums whitespace-nowrap">{{ $guia->num_guia }}</td>
                            <td>{{ $guia->usuario?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap">{{ $guia->ciudad_origen }}</td>
                            <td class="whitespace-nowrap">{{ $guia->ciudad_destino }}</td>
                            <td>{{ $guia->nom_remitente }}</td>
                            <td class="tabular-nums whitespace-nowrap">{{ $guia->created_at }}</td>
                            <td class="text-right font-semibold tabular-nums whitespace-nowrap">${{ number_format((float) $guia->valor_guia, 2) }}</td>
                            <td class="whitespace-nowrap">
                                <span class="badge {{ ($guia->estatus_cobro ?? 'Pendiente') === 'Cancelado' ? 'active' : 'security' }}">
                                    {{ $guia->estatus_cobro ?? 'Pendiente' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="row-actions">
                                    <button type="button" wire:click="abrirEdicion({{ $guia->id_cabecera }})" class="text-button">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="solicitarPago({{ $guia->id_cabecera }})" class="text-button danger">
                                        Pagar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <strong>Sin guías encontradas</strong>
                                    <span>Intenta con otro número de guía, remitente o destinatario.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($guias->hasPages())
            <div class="pagination-container">{{ $guias->links() }}</div>
        @endif
    </section>

    {{-- Modal: Edición de Guía en Caja --}}
    @if ($editingId !== null)
        <div class="resource-modal-backdrop" role="dialog" aria-modal="true">
            <div class="resource-modal resource-modal-lg">
                <div class="resource-modal-header">
                    <div>
                        <span class="eyebrow">Edición de Guía en Caja</span>
                        <h2>Guía N.º {{ $edit_num_guia }}</h2>
                    </div>
                    <button type="button" wire:click="cerrarEdicion" class="close-btn" aria-label="Cerrar">×</button>
                </div>

                <form wire:submit="guardarEdicion">
                    <div class="resource-modal-body space-y-5">
                        <div class="form-grid form-grid-3">
                            <div class="field">
                                <label for="edit_num_guia">Número de guía</label>
                                <input id="edit_num_guia" type="number" wire:model="edit_num_guia" class="control tabular-nums" required>
                                @error('edit_num_guia') <span class="field-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="edit_id_forma_pago">Forma de pago</label>
                                <select id="edit_id_forma_pago" wire:model="edit_id_forma_pago" class="control" required>
                                    @foreach ($formasPago as $for)
                                        <option value="{{ $for->id_formas_pago }}">{{ $for->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('edit_id_forma_pago') <span class="field-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="edit_recargo">Recargo</label>
                                <input id="edit_recargo" type="number" step="0.001" wire:model.live.debounce.200ms="edit_recargo" wire:change="recalcularTotalesEdicion" class="control tabular-nums">
                                @error('edit_recargo') <span class="field-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="edit_ciudad_origen_search">Ciudad origen (buscador predictivo)</label>
                                <input
                                    id="edit_ciudad_origen_search"
                                    type="search"
                                    wire:model.live.debounce.150ms="origenSearch"
                                    placeholder="Escribe para buscar ciudad origen..."
                                    class="control"
                                    autocomplete="off"
                                >
                                <div class="mt-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/70 divide-y divide-slate-100">
                                    @forelse ($filteredOrigenes as $ciu)
                                        <button
                                            type="button"
                                            wire:key="origen-opt-{{ $ciu->id_ciudad }}"
                                            wire:click="seleccionarCiudadOrigen('{{ addslashes($ciu->descripcion) }}')"
                                            class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between hover:bg-blue-50 {{ $edit_ciudad_origen === $ciu->descripcion ? 'bg-blue-50 font-bold text-blue-800' : 'text-slate-700' }}"
                                        >
                                            <span>{{ $ciu->descripcion }}</span>
                                            @if ($edit_ciudad_origen === $ciu->descripcion)
                                                <span class="badge active">Seleccionada</span>
                                            @endif
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-xs text-slate-400">Sin coincidencias</div>
                                    @endforelse
                                </div>
                                @error('edit_ciudad_origen') <span class="field-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="edit_ciudad_destino_search">Ciudad destino (buscador predictivo)</label>
                                <input
                                    id="edit_ciudad_destino_search"
                                    type="search"
                                    wire:model.live.debounce.150ms="destinoSearch"
                                    placeholder="Escribe para buscar ciudad destino..."
                                    class="control"
                                    autocomplete="off"
                                >
                                <div class="mt-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/70 divide-y divide-slate-100">
                                    @forelse ($filteredDestinos as $ciu)
                                        <button
                                            type="button"
                                            wire:key="destino-opt-{{ $ciu->id_ciudad }}"
                                            wire:click="seleccionarCiudadDestino('{{ addslashes($ciu->descripcion) }}')"
                                            class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between hover:bg-blue-50 {{ $edit_ciudad_destino === $ciu->descripcion ? 'bg-blue-50 font-bold text-blue-800' : 'text-slate-700' }}"
                                        >
                                            <span>{{ $ciu->descripcion }}</span>
                                            @if ($edit_ciudad_destino === $ciu->descripcion)
                                                <span class="badge active">Seleccionada</span>
                                            @endif
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-xs text-slate-400">Sin coincidencias</div>
                                    @endforelse
                                </div>
                                @error('edit_ciudad_destino') <span class="field-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label for="edit_remitente_search">Remitente (buscador predictivo)</label>
                                <input
                                    id="edit_remitente_search"
                                    type="search"
                                    wire:model.live.debounce.150ms="remitenteSearch"
                                    placeholder="Escribe para buscar remitente..."
                                    class="control"
                                    autocomplete="off"
                                >
                                <div class="mt-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/70 divide-y divide-slate-100">
                                    @forelse ($filteredPersonas as $per)
                                        <button
                                            type="button"
                                            wire:key="rem-opt-{{ $per->id_persona }}"
                                            wire:click="seleccionarRemitente({{ $per->id_persona }}, '{{ addslashes($per->nombre) }}')"
                                            class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between hover:bg-blue-50 {{ (string) $edit_id_persona === (string) $per->id_persona ? 'bg-blue-50 font-bold text-blue-800' : 'text-slate-700' }}"
                                        >
                                            <span>{{ $per->nombre }}</span>
                                            @if ((string) $edit_id_persona === (string) $per->id_persona)
                                                <span class="badge active">Seleccionado</span>
                                            @endif
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-xs text-slate-400">Sin coincidencias</div>
                                    @endforelse
                                </div>
                                @error('edit_id_persona') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="edit_nota">Nota</label>
                            <textarea id="edit_nota" wire:model="edit_nota" rows="3" class="control"></textarea>
                        </div>

                        <div class="table-wrap rounded-xl border border-slate-200">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center">Cantidad</th>
                                        <th>Descripción</th>
                                        <th class="text-right">V. Unitario</th>
                                        <th class="text-right">V. Parcial</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($editDetalles as $idx => $det)
                                        <tr wire:key="caja-det-{{ $det['id_detalle'] }}">
                                            <td class="text-center">
                                                <input type="number" wire:model.live.debounce.200ms="editDetalles.{{ $idx }}.cantidad" wire:change="recalcularTotalesEdicion" class="control tabular-nums w-24 text-center">
                                            </td>
                                            <td>
                                                <input type="text" wire:model="editDetalles.{{ $idx }}.descripcion" class="control" readonly>
                                            </td>
                                            <td class="text-right">
                                                <input type="number" step="0.01" wire:model.live.debounce.200ms="editDetalles.{{ $idx }}.v_unitario" wire:change="recalcularTotalesEdicion" class="control tabular-nums w-28 text-right ml-auto">
                                            </td>
                                            <td class="text-right font-semibold tabular-nums">
                                                ${{ number_format((float) ($det['v_parcial'] ?? 0), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="text-right font-bold">Flete</td>
                                        <td class="text-right font-bold tabular-nums">${{ $edit_flete }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="text-right font-bold">Prima</td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" wire:model.live.debounce.200ms="edit_prima" wire:change="recalcularTotalesEdicion" class="control tabular-nums w-28 text-right ml-auto">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="text-right font-bold">Valor guía</td>
                                        <td class="text-right font-bold text-emerald-700 tabular-nums">${{ $edit_valor_guia }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="resource-modal-footer">
                        <button type="button" wire:click="cerrarEdicion" class="button secondary">Cancelar</button>
                        <button type="submit" class="button primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Confirmar Pago / Cancelar Guía --}}
    @if ($confirmingPayId !== null)
        <div class="resource-modal-backdrop" role="dialog" aria-modal="true">
            <div class="resource-modal" style="max-width: 480px;">
                <div class="resource-modal-header">
                    <h2>Cancelar Guía</h2>
                    <button type="button" wire:click="cancelarConfirmacionPago" class="close-btn" aria-label="Cerrar">×</button>
                </div>
                <div class="resource-modal-body">
                    <p class="m-0 text-sm text-slate-700">Confirme si se pagó la guía</p>
                </div>
                <div class="resource-modal-footer">
                    <button type="button" wire:click="cancelarConfirmacionPago" class="button secondary">Cerrar</button>
                    <button type="button" wire:click="confirmarPagoGuia" class="button primary">Confirmar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Creación de Cajas (Usuario) --}}
    @if ($vistaActiva === 'crear')
        <div class="resource-modal-backdrop" role="dialog" aria-modal="true">
            <div class="resource-modal resource-modal-lg">
                <div class="resource-modal-header">
                    <div>
                        <span class="eyebrow">Cierre Diario de Caja</span>
                        <h2>Creación de Cajas</h2>
                    </div>
                    <button type="button" wire:click="cambiarVista('listado')" class="close-btn" aria-label="Cerrar">×</button>
                </div>

                <form action="{{ route('caja.reportes.usuario') }}" method="GET">
                    <div class="resource-modal-body space-y-5">
                        <div class="form-grid">
                            <div class="field">
                                <label for="caja_usuario_nombre">Usuario</label>
                                <input id="caja_usuario_nombre" type="text" readonly name="usuario" value="{{ strtoupper(auth()->user()?->name ?? '') }}" class="control bg-slate-50">
                            </div>
                            <div class="field">
                                <label for="caja_fecha_dia">Fecha</label>
                                <input id="caja_fecha_dia" type="date" name="fecha" wire:model.live="fechaCaja" class="control tabular-nums">
                            </div>
                        </div>

                        <div class="table-wrap rounded-xl border border-slate-200">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Num. Guía</th>
                                        <th>Ciudad Origen</th>
                                        <th>Ciudad Destino</th>
                                        <th>Remitente</th>
                                        <th>Fecha de Creación</th>
                                        <th class="text-right">Valor de la Guía</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($guiasCajaDia as $guia)
                                        <tr wire:key="caja-dia-{{ $guia->id_cabecera }}">
                                            <td class="font-semibold tabular-nums">{{ $guia->num_guia }}</td>
                                            <td>{{ $guia->ciudad_origen }}</td>
                                            <td>{{ $guia->ciudad_destino }}</td>
                                            <td>{{ $guia->nom_remitente }}</td>
                                            <td class="tabular-nums">{{ $guia->created_at }}</td>
                                            <td class="text-right font-semibold tabular-nums">${{ number_format((float) $guia->valor_guia, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <strong>Sin guías cobradas en la fecha</strong>
                                                    <span>Las guías pagadas por el usuario en la fecha seleccionada aparecerán aquí.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="font-bold text-slate-800">Total de dinero de las Guías</td>
                                        <td class="text-right font-bold text-emerald-700 tabular-nums">${{ number_format((float) $totalCajaDia, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="resource-modal-footer">
                        <button type="button" wire:click="cambiarVista('listado')" class="button secondary">Cerrar</button>
                        <button type="submit" class="button primary">Generar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Búsqueda / Revisión de Cajas de Usuarios --}}
    @if ($vistaActiva === 'revision')
        <div class="resource-modal-backdrop" role="dialog" aria-modal="true">
            <div class="resource-modal">
                <div class="resource-modal-header">
                    <div>
                        <span class="eyebrow">Control de Cajas</span>
                        <h2>Búsqueda de Cajas de Usuarios</h2>
                    </div>
                    <button type="button" wire:click="cambiarVista('listado')" class="close-btn" aria-label="Cerrar">×</button>
                </div>

                <form action="{{ route('caja.reportes.admin') }}" method="GET">
                    <div class="resource-modal-body space-y-4">
                        <div class="form-grid">
                            <div class="field">
                                <label for="admin_usuario_search">Usuario</label>
                                @if ($isPrivilegedCajaUser)
                                    <div class="flex items-center gap-2">
                                        <input
                                            id="admin_usuario_search"
                                            type="search"
                                            wire:model.live.debounce.150ms="usuarioAdminSearch"
                                            placeholder="Escribe para buscar y seleccionar usuario..."
                                            class="control"
                                            autocomplete="off"
                                        >
                                        @if ($usuarioAdmin !== '')
                                            <button type="button" wire:click="limpiarUsuarioAdmin" class="button secondary text-xs">Limpiar</button>
                                        @endif
                                    </div>
                                    <input type="hidden" name="usuario" value="{{ $usuarioAdmin }}">
                                    <div class="mt-1.5 max-h-36 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/70 divide-y divide-slate-100">
                                        @forelse ($filteredUsers as $usu)
                                            <button
                                                type="button"
                                                wire:key="admin-user-opt-{{ $usu->id }}"
                                                wire:click="seleccionarUsuarioAdmin({{ $usu->id }}, '{{ addslashes($usu->name) }}')"
                                                class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between hover:bg-blue-50 {{ (string) $usuarioAdmin === (string) $usu->id ? 'bg-blue-50 font-bold text-blue-800' : 'text-slate-700' }}"
                                            >
                                                <span>{{ $usu->name }}</span>
                                                @if ((string) $usuarioAdmin === (string) $usu->id)
                                                    <span class="badge active">Seleccionado</span>
                                                @endif
                                            </button>
                                        @empty
                                            <div class="px-3 py-2 text-xs text-slate-400">Sin usuarios coincidentes</div>
                                        @endforelse
                                    </div>
                                @else
                                    <input
                                        id="admin_usuario_search"
                                        type="text"
                                        readonly
                                        value="{{ auth()->user()?->name ?? '' }}"
                                        class="control bg-slate-50 font-semibold text-slate-800"
                                    >
                                    <input type="hidden" name="usuario" value="{{ $usuarioAdmin }}">
                                @endif
                            </div>

                            <div class="field">
                                <label for="admin_fecha">Seleccione una fecha</label>
                                <input
                                    id="admin_fecha"
                                    type="date"
                                    name="fecha"
                                    wire:model.live="fechaAdmin"
                                    max="{{ now('America/Guayaquil')->toDateString() }}"
                                    class="control tabular-nums"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="resource-modal-footer">
                        <button type="button" wire:click="cambiarVista('listado')" class="button secondary">Cerrar</button>
                        <button type="submit" class="button primary">Generar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>