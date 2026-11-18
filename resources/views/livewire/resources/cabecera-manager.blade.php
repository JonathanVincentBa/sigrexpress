<div>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Operación y envíos</p>
            <h1>Guías de remisión</h1>
            <p class="muted">Emite y administra guías con sus datos de transporte, cliente y líneas de detalle.</p>
        </div>
        <button wire:click="create" class="button primary" type="button">+ Nueva guía</button>
    </div>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="search-box">
                <input wire:model.live.debounce.300ms="search" type="search" class="control" placeholder="Buscar por número, cliente o remitente..." aria-label="Buscar guías">
            </div>
            <span>Total: <strong>{{ $guides->total() }}</strong></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>N.º guía</th><th>Cliente / remitente</th><th>Destinatario</th><th>Origen</th><th>Destino</th><th>Emisión</th><th>Valor</th><th class="actions-heading">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($guides as $guide)
                        <tr wire:key="guide-{{ $guide->id_cabecera }}">
                            <td><strong>{{ $guide->num_guia }}</strong></td>
                            <td>{{ $guide->persona?->nombre ?? $guide->nom_remitente }}</td>
                            <td>{{ $guide->nom_destinatario }}</td>
                            <td>{{ $guide->ciudad_origen }}</td>
                            <td>{{ $guide->ciudad_destino }}</td>
                            <td>{{ $guide->fecha_emision?->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format((float) $guide->valor_guia, 2) }}</td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="view({{ $guide->id_cabecera }})" class="text-button" type="button">Ver</button>
                                    <button wire:click="edit({{ $guide->id_cabecera }})" class="text-button" type="button">Editar</button>
                                    <button wire:click="prepareDelete({{ $guide->id_cabecera }})" class="text-button danger" type="button">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state"><strong>No se encontraron guías</strong><span>Ajusta la búsqueda o crea una guía nueva.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($guides->hasPages())
            <div class="pagination-container">{{ $guides->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        @php
            $guideAmount = (float) ($formData['flete'] ?? 0)
                + (float) ($formData['recargo'] ?? 0)
                + (float) ($formData['prima'] ?? 0);
            $itemsAmount = collect($detalles)->sum(fn ($detail) => (int) ($detail['cantidad'] ?? 0) * (float) ($detail['v_unitario'] ?? 0));
        @endphp
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="guide-modal-title" style="width: min(96vw, 1350px); height: 92vh; max-height: 95vh; display: flex; flex-direction: column; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                
                <!-- HEADER MODERNO -->
                <header class="resource-modal-header" style="background: #f8fafc; padding: 20px 28px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center; font-weight: 700; font-size: 18px;">
                            📄
                        </div>
                        <div>
                            <span class="eyebrow" style="color: #0284c7; font-weight: 700; letter-spacing: 0.05em; font-size: 11px; text-transform: uppercase;">{{ $isViewing ? 'Consulta de Operación' : ($isEditing ? 'Modificación de Registro' : 'Nueva Emisión') }}</span>
                            <h2 id="guide-modal-title" style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">{{ $isViewing ? 'Detalle de Guía de Remisión' : ($isEditing ? 'Editar Guía de Remisión' : 'Registrar Nueva Guía de Remisión') }}</h2>
                        </div>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 20px; cursor: pointer; display: grid; place-items: center; transition: all 0.2s;">×</button>
                </header>

                <form wire:submit="save" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    
                    <!-- CUERPO CON SCROLL ORGANIZADO EN GRID / TARJETAS -->
                    <div class="resource-modal-body" style="flex: 1; overflow-y: auto; padding: 28px; background: #f1f5f9; display: grid; gap: 24px;">
                        
                       <!-- SECCIÓN 1: DATOS GENERALES DE CABECERA -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                <span style="font-size: 16px;">📌</span>
                                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em;">1. Información General de la Guía</h3>
                            </div>
                            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                                <div class="field">
                                    <label for="guide-number" style="font-weight: 600; color: #475569;">N.º de guía *</label>
                                    <input wire:model="formData.num_guia" id="guide-number" type="number" class="control" readonly style="background: #f8fafc; font-weight: 700; color: #0f172a;">
                                    @error('formData.num_guia') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-transport-number" style="font-weight: 600; color: #475569;">N.º guía de transporte *</label>
                                    <input wire:model="formData.num_guia_trans" id="guide-transport-number" type="number" class="control" min="0" @disabled($isViewing)>
                                    @error('formData.num_guia_trans') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-date" style="font-weight: 600; color: #475569;">Fecha de emisión *</label>
                                    <input wire:model="formData.fecha_emision" id="guide-date" type="datetime-local" class="control" @disabled($isViewing)>
                                    @error('formData.fecha_emision') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-branch" style="font-weight: 600; color: #475569;">Sucursal / Agencia *</label>
                                    @if (auth()->user()?->isBranchRestricted())
                                        <input type="text" class="control" value="{{ auth()->user()->sucursal?->nombre ?? 'Sucursal asignada' }}" disabled readonly style="background: #f8fafc;">
                                    @else
                                        <x-searchable-select wire:model="formData.id_sucursal" id="guide-branch" class="control" @disabled($isViewing)>
                                            <option value="">Selecciona una sucursal</option>
                                            @foreach ($sucursales as $sucursal)
                                                <option value="{{ $sucursal->id_sucursal }}">{{ $sucursal->nombre }} · {{ $sucursal->ciudad?->descripcion ?? 'Ciudad no disponible' }}{{ $sucursal->estado === '1' ? '' : ' · Inactiva' }}</option>
                                            @endforeach
                                        </x-searchable-select>
                                    @endif
                                    @error('formData.id_sucursal') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-reason" style="font-weight: 600; color: #475569;">Motivo de traslado *</label>
                                    <x-searchable-select wire:model="formData.id_motivo_traslado" id="guide-reason" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona un motivo</option>
                                        @foreach ($motivos as $motivo)
                                            <option value="{{ $motivo->id_motivo_traslado }}">{{ $motivo->descripcion }}{{ $motivo->estado === '1' ? '' : ' · Inactivo' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.id_motivo_traslado') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-payment" style="font-weight: 600; color: #475569;">Forma de pago *</label>
                                    <x-searchable-select wire:model="formData.id_forma_pago" id="guide-payment" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una forma de pago</option>
                                        @foreach ($formasPago as $formaPago)
                                            <option value="{{ $formaPago->id_formas_pago }}">{{ $formaPago->descripcion }}{{ $formaPago->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.id_forma_pago') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-origin" style="font-weight: 600; color: #475569;">Ciudad de origen *</label>
                                    <x-searchable-select wire:model="formData.ciudad_origen" id="guide-origin" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                            <option value="{{ $ciudad->descripcion }}">{{ $ciudad->descripcion }}{{ $ciudad->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.ciudad_origen') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-destination" style="font-weight: 600; color: #475569;">Ciudad de destino *</label>
                                    <x-searchable-select wire:model="formData.ciudad_destino" id="guide-destination" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                            <option value="{{ $ciudad->descripcion }}">{{ $ciudad->descripcion }}{{ $ciudad->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.ciudad_destino') <span class="field-error">{{ $message }}</span> @enderror
                                </div>

                                <!-- CAMPO ESTADO: SOLO VISIBLE PARA ADMINISTRADORES / SUPER ADMINS -->
                                @if (auth()->user()?->isAdministrator())
                                    <div class="field">
                                        <label for="guide-state" style="font-weight: 600; color: #475569;">Estado *</label>
                                        <x-searchable-select wire:model="formData.estado" id="guide-state" class="control" @disabled($isViewing)>
                                            <option value="1">Activa</option>
                                            <option value="0">Anulada</option>
                                        </x-searchable-select>
                                        @error('formData.estado') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- SECCIÓN 2: REMITENTE Y DESTINATARIO (2 COLUMNAS) -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px;">
                            
                            <!-- Remitente -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                    <span style="font-size: 16px;">👤</span>
                                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em;">2. Cliente / Remitente</h3>
                                </div>
                                <div class="field" style="margin-bottom: 14px;">
                                    <label for="guide-client-search" style="font-weight: 600; color: #475569;">Buscar cliente (Nombre o DNI) *</label>
                                    <input wire:model.live.debounce.300ms="clientSearch" id="guide-client-search" type="search" class="control" placeholder="Escribe nombre o cédula/RUC..." @disabled($isViewing)>
                                    @error('formData.id_persona') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                @if (! $isViewing && trim($clientSearch) !== '' && mb_strlen(trim($clientSearch)) >= 2 && empty($formData['id_persona']))
                                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; max-height: 160px; overflow-y: auto; margin-bottom: 14px;">
                                        @forelse ($clientes as $cliente)
                                            <button wire:click="selectClient({{ $cliente->id_persona }})" type="button" style="width:100%; text-align:left; padding: 10px 14px; background: none; border: none; border-bottom: 1px solid #e2e8f0; cursor:pointer; display: flex; justify-content: space-between; align-items: center;" wire:key="client-result-{{ $cliente->id_persona }}">
                                                <span><strong style="color: #0f172a;">{{ $cliente->nombre }}</strong><br><span style="font-size: 11px; color: #64748b;">{{ $cliente->tipo_dni }} · {{ $cliente->num_dni }}</span></span>
                                                <span style="color: #0284c7; font-weight: 700; font-size: 12px;">Seleccionar</span>
                                            </button>
                                        @empty
                                            <div style="padding: 12px; text-align: center; color: #64748b; font-size: 12px;">No hay clientes que coincidan.</div>
                                            <div style="padding: 8px; text-align: center;">
                                                <button wire:click="openQuickCustomerModal" type="button" class="button secondary" style="font-size: 11px;">+ Crear nuevo cliente</button>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Nombre remitente *</label>
                                        <input wire:model="formData.nom_remitente" type="text" class="control" maxlength="50" @disabled($isViewing)>
                                        @error('formData.nom_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Identificación *</label>
                                        <input wire:model="formData.dni_remitente" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                        @error('formData.dni_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field field-wide" style="grid-column: 1 / -1;">
                                        <label style="font-weight: 600; color: #475569;">Dirección *</label>
                                        <input wire:model="formData.direccion_remitente" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                        @error('formData.direccion_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Teléfono *</label>
                                        <input wire:model="formData.fono_remitente" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                        @error('formData.fono_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Destinatario -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                    <span style="font-size: 16px;">📦</span>
                                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em;">3. Destinatario</h3>
                                </div>
                                <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 36px;">
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Nombre destinatario *</label>
                                        <input wire:model="formData.nom_destinatario" type="text" class="control" maxlength="50" @disabled($isViewing)>
                                        @error('formData.nom_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Identificación</label>
                                        <input wire:model="formData.dni_destinatario" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                        @error('formData.dni_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field field-wide" style="grid-column: 1 / -1;">
                                        <label style="font-weight: 600; color: #475569;">Dirección</label>
                                        <input wire:model="formData.direccion_destinatario" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                        @error('formData.direccion_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="field">
                                        <label style="font-weight: 600; color: #475569;">Teléfono</label>
                                        <input wire:model="formData.fono_destinatario" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                        @error('formData.fono_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: DETALLE DE ARTÍCULOS Y VALORES -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">📋</span>
                                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em;">4. Líneas de Detalle y Costos</h3>
                                </div>
                                @unless ($isViewing)
                                    <button wire:click="addDetail" type="button" class="button secondary" style="font-size: 12px; padding: 6px 12px;">+ Agregar línea</button>
                                @endunless
                            </div>

                            <div style="display: grid; gap: 10px;">
                                @foreach ($detalles as $index => $detail)
                                    <div style="display: grid; grid-template-columns: 90px 1fr 130px 130px 40px; align-items: center; gap: 12px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;" wire:key="guide-detail-{{ $detail['id_detalle'] ?? 'new-'.$index }}">
                                        <div class="field">
                                            <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Cantidad *</label>
                                            <input wire:model.live="detalles.{{ $index }}.cantidad" type="number" class="control" min="1" @disabled($isViewing)>
                                        </div>
                                        <div class="field">
                                            <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Artículo / descripción *</label>
                                            <input wire:model="detalles.{{ $index }}.descripcion" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                        </div>
                                        <div class="field">
                                            <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">V. Unitario *</label>
                                            <input wire:model.live="detalles.{{ $index }}.v_unitario" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                        </div>
                                        <div class="field">
                                            <label style="font-size: 10.5px; font-weight: 600; color: #64748b;">Total línea</label>
                                            <div class="control" style="background: #fff; font-weight: 700; display: flex; align-items: center;">${{ number_format((int) ($detail['cantidad'] ?? 0) * (float) ($detail['v_unitario'] ?? 0), 2) }}</div>
                                        </div>
                                        @unless ($isViewing)
                                            <button wire:click="prepareRemoveDetail({{ $index }})" type="button" class="text-button danger" title="Quitar línea" style="font-size: 20px; font-weight: 700; text-align: center;">×</button>
                                        @endunless
                                    </div>
                                    @error("detalles.{$index}.cantidad") <span class="field-error">{{ $message }}</span> @enderror
                                    @error("detalles.{$index}.descripcion") <span class="field-error">{{ $message }}</span> @enderror
                                    @error("detalles.{$index}.v_unitario") <span class="field-error">{{ $message }}</span> @enderror
                                @endforeach
                            </div>

                            <!-- Totales de Flete / Recargo / Prima -->
                            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin-top: 20px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Flete *</label>
                                    <input wire:model.live="formData.flete" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.flete') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Recargo</label>
                                    <input wire:model.live="formData.recargo" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.recargo') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Prima</label>
                                    <input wire:model.live="formData.prima" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.prima') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field" style="background: #e0f2fe; padding: 8px; border-radius: 6px;">
                                    <label style="font-weight: 700; color: #0369a1;">Valor Total Guía</label>
                                    <div style="font-size: 16px; font-weight: 800; color: #0369a1; margin-top: 4px;">${{ number_format($guideAmount, 2) }}</div>
                                </div>
                                <div class="field" style="background: #f1f5f9; padding: 8px; border-radius: 6px;">
                                    <label style="font-weight: 700; color: #475569;">Valor Artículos</label>
                                    <div style="font-size: 16px; font-weight: 800; color: #334155; margin-top: 4px;">${{ number_format($itemsAmount, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 4: REFERENCIAS Y OBSERVACIONES -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                <span style="font-size: 16px;">📝</span>
                                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em;">5. Referencias y Notas Adicionales</h3>
                            </div>
                            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Guía del cliente</label>
                                    <input wire:model="formData.guia_rem_cliente" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                </div>
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Factura del cliente</label>
                                    <input wire:model="formData.factura_cliente" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                </div>
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Fecha de recepción</label>
                                    <input wire:model="formData.fecha_recepcion" type="datetime-local" class="control" @disabled($isViewing)>
                                </div>
                                <div class="field">
                                    <label style="font-weight: 600; color: #475569;">Nombre quien recibe</label>
                                    <input wire:model="formData.nombre_recibe" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                </div>
                                <div class="field field-wide" style="grid-column: 1 / -1;">
                                    <label style="font-weight: 600; color: #475569;">Observaciones</label>
                                    <textarea wire:model="formData.nota" class="control" rows="2" @disabled($isViewing)></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER FIJO ABAJO -->
                    <footer class="resource-modal-footer" style="background: #f8fafc; padding: 16px 28px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary" style="padding: 10px 20px;">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled" style="padding: 10px 24px; font-size: 13.5px;">Guardar Guía</button>
                        @endunless
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showQuickCustomerModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="dialog" aria-modal="true" aria-labelledby="quick-customer-title" style="max-width:640px;">
                <header class="resource-modal-header">
                    <div><p class="eyebrow">Registro rápido</p><h2 id="quick-customer-title">Crear nuevo cliente</h2></div>
                    <button wire:click="$set('showQuickCustomerModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>
                <form wire:submit="saveQuickCustomer">
                    <div class="resource-modal-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="quick-customer-company">Empresa *</label>
                                <x-searchable-select wire:model="quickCustomer.id_empresa" id="quick-customer-company" class="control">
                                    <option value="">Selecciona una empresa</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id_empresa }}">{{ $empresa->razon_social }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('quickCustomer.id_empresa') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="quick-customer-position">Cargo *</label>
                                <x-searchable-select wire:model="quickCustomer.id_cargo" id="quick-customer-position" class="control">
                                    <option value="">Selecciona un cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{ $cargo->id_cargo }}">{{ $cargo->descripcion }}</option>
                                    @endforeach
                                </x-searchable-select>
                                @error('quickCustomer.id_cargo') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="quick-customer-name">Nombres y apellidos *</label>
                                <input wire:model="quickCustomer.nombre" id="quick-customer-name" type="text" class="control" maxlength="100" autocomplete="name">
                                @error('quickCustomer.nombre') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="quick-customer-id-type">Tipo de identificación *</label>
                                <x-searchable-select wire:model="quickCustomer.tipo_dni" id="quick-customer-id-type" class="control">
                                    <option value="C">Cédula</option>
                                    <option value="R">RUC</option>
                                    <option value="P">Pasaporte</option>
                                </x-searchable-select>
                                @error('quickCustomer.tipo_dni') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="quick-customer-id">Número de identificación *</label>
                                <input wire:model="quickCustomer.num_dni" id="quick-customer-id" type="text" class="control" maxlength="20">
                                @error('quickCustomer.num_dni') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field field-wide">
                                <label for="quick-customer-address">Dirección *</label>
                                <input wire:model="quickCustomer.direccion" id="quick-customer-address" type="text" class="control" maxlength="500" autocomplete="street-address">
                                @error('quickCustomer.direccion') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="field">
                                <label for="quick-customer-phone">Teléfono *</label>
                                <input wire:model="quickCustomer.telefono" id="quick-customer-phone" type="text" class="control" maxlength="20" autocomplete="tel">
                                @error('quickCustomer.telefono') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showQuickCustomerModal', false)" type="button" class="button secondary">Cancelar</button>
                        <button type="submit" class="button primary" wire:loading.attr="disabled">Crear y seleccionar</button>
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($showDetailDeleteModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="alertdialog" aria-modal="true" style="max-width:480px;">
                <header class="resource-modal-header"><div><p class="eyebrow">Confirmar acción</p><h2>Quitar línea de detalle</h2></div></header>
                <div class="resource-modal-body"><p>¿Deseas quitar esta línea de la guía? El cambio se aplicará al guardar la guía.</p></div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDetailDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmRemoveDetail" type="button" class="button danger">Quitar línea</button>
                </footer>
            </section>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="resource-modal-backdrop">
            <section class="resource-modal" role="alertdialog" aria-modal="true" style="max-width:480px;">
                <header class="resource-modal-header"><div><p class="eyebrow">Confirmar acción</p><h2>Eliminar guía</h2></div></header>
                <div class="resource-modal-body"><p>¿Deseas eliminar la guía <strong>{{ $deleteLabel }}</strong>?</p></div>
                <footer class="resource-modal-footer">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="button secondary">Cancelar</button>
                    <button wire:click="confirmDelete" type="button" class="button danger">Eliminar</button>
                </footer>
            </section>
        </div>
    @endif
</div>
