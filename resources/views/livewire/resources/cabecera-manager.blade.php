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
            <section class="resource-modal resource-modal-lg" role="dialog" aria-modal="true" aria-labelledby="guide-modal-title">
                <header class="resource-modal-header">
                    <div>
                        <p class="eyebrow">{{ $isViewing ? 'Consulta' : ($isEditing ? 'Modificación' : 'Nueva operación') }}</p>
                        <h2 id="guide-modal-title">{{ $isViewing ? 'Detalle de guía' : ($isEditing ? 'Editar guía' : 'Registrar guía') }}</h2>
                    </div>
                    <button wire:click="$set('showModal', false)" class="close-btn" type="button" aria-label="Cerrar">×</button>
                </header>

                <form wire:submit="save">
                    <div class="resource-modal-body">
                        <section>
                            <p class="eyebrow">Cabecera</p>
                            <div class="form-grid form-grid-3">
                                <div class="field">
                                    <label for="guide-number">N.º de guía *</label>
                                    <input wire:model="formData.num_guia" id="guide-number" type="number" class="control" readonly>
                                    @error('formData.num_guia') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-transport-number">N.º guía de transporte *</label>
                                    <input wire:model="formData.num_guia_trans" id="guide-transport-number" type="number" class="control" min="0" @disabled($isViewing)>
                                    @error('formData.num_guia_trans') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-date">Fecha de emisión *</label>
                                    <input wire:model="formData.fecha_emision" id="guide-date" type="datetime-local" class="control" @disabled($isViewing)>
                                    @error('formData.fecha_emision') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-branch">Sucursal *</label>
                                    <x-searchable-select wire:model="formData.id_sucursal" id="guide-branch" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una sucursal</option>
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id_sucursal }}">{{ $sucursal->nombre }} · {{ $sucursal->ciudad?->descripcion ?? 'Ciudad no disponible' }} · {{ $sucursal->empresa?->razon_social ?? 'Empresa no disponible' }}{{ $sucursal->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.id_sucursal') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-reason">Motivo de traslado *</label>
                                    <x-searchable-select wire:model="formData.id_motivo_traslado" id="guide-reason" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona un motivo</option>
                                        @foreach ($motivos as $motivo)
                                            <option value="{{ $motivo->id_motivo_traslado }}">{{ $motivo->descripcion }}{{ $motivo->estado === '1' ? '' : ' · Inactivo' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.id_motivo_traslado') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-payment">Forma de pago *</label>
                                    <x-searchable-select wire:model="formData.id_forma_pago" id="guide-payment" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una forma de pago</option>
                                        @foreach ($formasPago as $formaPago)
                                            <option value="{{ $formaPago->id_formas_pago }}">{{ $formaPago->descripcion }}{{ $formaPago->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.id_forma_pago') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-origin">Ciudad de origen *</label>
                                    <x-searchable-select wire:model="formData.ciudad_origen" id="guide-origin" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                            <option value="{{ $ciudad->descripcion }}">{{ $ciudad->descripcion }}{{ $ciudad->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.ciudad_origen') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-destination">Ciudad de destino *</label>
                                    <x-searchable-select wire:model="formData.ciudad_destino" id="guide-destination" class="control" @disabled($isViewing)>
                                        <option value="">Selecciona una ciudad</option>
                                        @foreach ($ciudades as $ciudad)
                                            <option value="{{ $ciudad->descripcion }}">{{ $ciudad->descripcion }}{{ $ciudad->estado === '1' ? '' : ' · Inactiva' }}</option>
                                        @endforeach
                                    </x-searchable-select>
                                    @error('formData.ciudad_destino') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-state">Estado *</label>
                                    <x-searchable-select wire:model="formData.estado" id="guide-state" class="control" @disabled($isViewing)>
                                        <option value="1">Activa</option>
                                        <option value="0">Anulada</option>
                                    </x-searchable-select>
                                    @error('formData.estado') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="detail-card">
                            <p class="eyebrow">Cliente / remitente</p>
                            <div class="field">
                                <label for="guide-client-search">Buscar cliente por nombres, apellidos o identificación *</label>
                                <input wire:model.live.debounce.300ms="clientSearch" id="guide-client-search" type="search" class="control" placeholder="Escribe nombre o cédula/RUC..." @disabled($isViewing)>
                                @error('formData.id_persona') <span class="field-error">{{ $message }}</span> @enderror
                            </div>
                            @if (! $isViewing && trim($clientSearch) !== '' && mb_strlen(trim($clientSearch)) >= 2 && empty($formData['id_persona']))
                                <div class="panel" style="margin-top:8px;">
                                    @forelse ($clientes as $cliente)
                                        <button wire:click="selectClient({{ $cliente->id_persona }})" type="button" class="panel-toolbar" style="width:100%; text-align:left; cursor:pointer;" wire:key="client-result-{{ $cliente->id_persona }}">
                                            <span><strong>{{ $cliente->nombre }}</strong><br><span class="muted">{{ $cliente->tipo_dni }} · {{ $cliente->num_dni }}</span></span>
                                            <span class="open-link">Seleccionar</span>
                                        </button>
                                    @empty
                                        <div class="panel-toolbar"><span>No hay clientes que coincidan con la búsqueda.</span></div>
                                        <div class="panel-toolbar">
                                            <button wire:click="openQuickCustomerModal" type="button" class="button secondary">Crear nuevo cliente</button>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                            <div class="form-grid" style="margin-top:14px;">
                                <div class="field">
                                    <label for="sender-name">Nombre del remitente *</label>
                                    <input wire:model="formData.nom_remitente" id="sender-name" type="text" class="control" maxlength="50" @disabled($isViewing)>
                                    @error('formData.nom_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="sender-id">Identificación del remitente *</label>
                                    <input wire:model="formData.dni_remitente" id="sender-id" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                    @error('formData.dni_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field field-wide">
                                    <label for="sender-address">Dirección del remitente *</label>
                                    <input wire:model="formData.direccion_remitente" id="sender-address" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                    @error('formData.direccion_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="sender-phone">Teléfono del remitente *</label>
                                    <input wire:model="formData.fono_remitente" id="sender-phone" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                    @error('formData.fono_remitente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="detail-card">
                            <p class="eyebrow">Destinatario</p>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="recipient-name">Nombre del destinatario *</label>
                                    <input wire:model="formData.nom_destinatario" id="recipient-name" type="text" class="control" maxlength="50" @disabled($isViewing)>
                                    @error('formData.nom_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="recipient-id">Identificación</label>
                                    <input wire:model="formData.dni_destinatario" id="recipient-id" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                    @error('formData.dni_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field field-wide">
                                    <label for="recipient-address">Dirección</label>
                                    <input wire:model="formData.direccion_destinatario" id="recipient-address" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                    @error('formData.direccion_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="recipient-phone">Teléfono</label>
                                    <input wire:model="formData.fono_destinatario" id="recipient-phone" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                    @error('formData.fono_destinatario') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="detail-card">
                            <div class="page-heading">
                                <div><p class="eyebrow">Detalle de la guía</p><p class="muted">Agrega los artículos, cantidades y valores unitarios.</p></div>
                                @unless ($isViewing)
                                    <button wire:click="addDetail" type="button" class="button secondary">+ Agregar línea</button>
                                @endunless
                            </div>
                            @foreach ($detalles as $index => $detail)
                                <div class="detail-line" wire:key="guide-detail-{{ $detail['id_detalle'] ?? 'new-'.$index }}">
                                    <div class="field">
                                        <label for="detail-quantity-{{ $index }}">Cantidad *</label>
                                        <input wire:model.live="detalles.{{ $index }}.cantidad" id="detail-quantity-{{ $index }}" type="number" class="control" min="1" @disabled($isViewing)>
                                    </div>
                                    <div class="field">
                                        <label for="detail-description-{{ $index }}">Artículo / descripción *</label>
                                        <input wire:model="detalles.{{ $index }}.descripcion" id="detail-description-{{ $index }}" type="text" class="control" maxlength="100" @disabled($isViewing)>
                                    </div>
                                    <div class="field">
                                        <label for="detail-price-{{ $index }}">Valor unitario *</label>
                                        <input wire:model.live="detalles.{{ $index }}.v_unitario" id="detail-price-{{ $index }}" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    </div>
                                    <div class="field">
                                        <label>Total línea</label>
                                        <div class="control">${{ number_format((int) ($detail['cantidad'] ?? 0) * (float) ($detail['v_unitario'] ?? 0), 2) }}</div>
                                    </div>
                                    @unless ($isViewing)
                                        <button wire:click="prepareRemoveDetail({{ $index }})" type="button" class="text-button danger" aria-label="Quitar línea">×</button>
                                    @endunless
                                    @error("detalles.{$index}.cantidad") <span class="field-error">{{ $message }}</span> @enderror
                                    @error("detalles.{$index}.descripcion") <span class="field-error">{{ $message }}</span> @enderror
                                    @error("detalles.{$index}.v_unitario") <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                            @error('detalles') <span class="field-error">{{ $message }}</span> @enderror
                            <div class="form-grid form-grid-3" style="margin-top:14px;">
                                <div class="field">
                                    <label for="guide-freight">Flete *</label>
                                    <input wire:model.live="formData.flete" id="guide-freight" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.flete') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-surcharge">Recargo</label>
                                    <input wire:model.live="formData.recargo" id="guide-surcharge" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.recargo') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="guide-premium">Prima</label>
                                    <input wire:model.live="formData.prima" id="guide-premium" type="number" class="control" min="0" step="0.01" @disabled($isViewing)>
                                    @error('formData.prima') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label>Valor de guía</label>
                                    <div class="control"><strong>${{ number_format($guideAmount, 2) }}</strong></div>
                                </div>
                                <div class="field">
                                    <label>Valor de artículos</label>
                                    <div class="control">${{ number_format($itemsAmount, 2) }}</div>
                                </div>
                            </div>
                        </section>

                        <section class="detail-card">
                            <p class="eyebrow">Referencias y notas</p>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="customer-guide">Guía del cliente</label>
                                    <input wire:model="formData.guia_rem_cliente" id="customer-guide" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                    @error('formData.guia_rem_cliente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="customer-invoice">Factura del cliente</label>
                                    <input wire:model="formData.factura_cliente" id="customer-invoice" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                    @error('formData.factura_cliente') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="received-date">Fecha de recepción</label>
                                    <input wire:model="formData.fecha_recepcion" id="received-date" type="datetime-local" class="control" @disabled($isViewing)>
                                    @error('formData.fecha_recepcion') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="cancelled-date">Fecha de anulación</label>
                                    <input wire:model="formData.fecha_cancelacion" id="cancelled-date" type="datetime-local" class="control" @disabled($isViewing)>
                                    @error('formData.fecha_cancelacion') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="received-by">Recibido por</label>
                                    <input wire:model="formData.nombre_recibe" id="received-by" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                    @error('formData.nombre_recibe') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="received-id">Identificación de quien recibe</label>
                                    <input wire:model="formData.dni_recibe" id="received-id" type="text" class="control" maxlength="45" @disabled($isViewing)>
                                    @error('formData.dni_recibe') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="delivery-status">Estado de entrega</label>
                                    <input wire:model="formData.estatus_entrega" id="delivery-status" type="text" class="control" maxlength="1" @disabled($isViewing)>
                                    @error('formData.estatus_entrega') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field">
                                    <label for="payment-status">Estado de cobro</label>
                                    <input wire:model="formData.estatus_cobro" id="payment-status" type="text" class="control" maxlength="20" @disabled($isViewing)>
                                    @error('formData.estatus_cobro') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="field field-wide">
                                    <label for="guide-notes">Observaciones</label>
                                    <textarea wire:model="formData.nota" id="guide-notes" class="control" rows="3" @disabled($isViewing)></textarea>
                                    @error('formData.nota') <span class="field-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>
                    </div>
                    <footer class="resource-modal-footer">
                        <button wire:click="$set('showModal', false)" type="button" class="button secondary">{{ $isViewing ? 'Cerrar' : 'Cancelar' }}</button>
                        @unless ($isViewing)
                            <button type="submit" class="button primary" wire:loading.attr="disabled">Guardar guía</button>
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
