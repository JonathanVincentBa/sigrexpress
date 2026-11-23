<?php

namespace App\Livewire\Resources;

use App\Concerns\WithSweetAlert;
use App\Models\Cabecera;
use App\Models\Ciudad;
use App\Models\Detalle;
use App\Models\FormasPago;
use App\Models\Persona;
use App\Models\Scopes\AssignedBranchScope;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Gestión de Caja y Cierres')]
class CajaManager extends Component
{
    use WithPagination;
    use WithSweetAlert;

    public string $vistaActiva = 'listado'; // listado | crear | revision

    public string $searchText = '';

    public string $fechaCaja = '';

    public string $usuarioAdmin = '';

    public string $usuarioAdminSearch = '';

    public string $fechaAdmin = '';

    public string $remitenteSearch = '';

    public string $origenSearch = '';

    public string $destinoSearch = '';

    public ?int $confirmingPayId = null;

    public ?int $editingId = null;

    public string $edit_num_guia = '';

    public string $edit_id_forma_pago = '';

    public string $edit_recargo = '0.00';

    public string $edit_flete = '0.00';

    public string $edit_prima = '0.00';

    public string $edit_valor_guia = '0.00';

    public string $edit_ciudad_origen = '';

    public string $edit_ciudad_destino = '';

    public string $edit_id_persona = '';

    public string $edit_nota = '';

    /** @var list<array{id_detalle:int, cantidad:int, descripcion:string, v_unitario:float, v_parcial:float}> */
    public array $editDetalles = [];

    public function mount(): void
    {
        $hoy = now('America/Guayaquil')->toDateString();
        $this->fechaCaja = $hoy;
        $this->fechaAdmin = $hoy;
        $this->aplicarReglaRolUsuarioAdmin();
    }

    public function isPrivilegedCajaUser(): bool
    {
        $user = $this->currentUser();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdministrator') && $user->isAdministrator()) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole')) {
            $roles = collect($user->getRoleNames())
                ->map(fn ($role): string => mb_strtolower(trim((string) $role)));

            if ($roles->intersect(['super-admin', 'super admin', 'superadministrador', 'admin', 'administrador'])->isNotEmpty()) {
                return true;
            }
        }

        $roleField = mb_strtolower(trim((string) ($user->role ?? $user->rol ?? '')));

        return in_array($roleField, ['super-admin', 'super admin', 'superadministrador', 'admin', 'administrador'], true);
    }

    private function aplicarReglaRolUsuarioAdmin(): void
    {
        $user = $this->currentUser();
        if (! $user) {
            return;
        }

        if (! $this->isPrivilegedCajaUser()) {
            $this->usuarioAdmin = (string) $user->id;
            $this->usuarioAdminSearch = (string) $user->name;
        }
    }

    public function seleccionarUsuarioAdmin(int $userId, string $userName): void
    {
        if (! $this->isPrivilegedCajaUser()) {
            return;
        }

        $this->usuarioAdmin = (string) $userId;
        $this->usuarioAdminSearch = $userName;
    }

    public function limpiarUsuarioAdmin(): void
    {
        if (! $this->isPrivilegedCajaUser()) {
            return;
        }

        $this->usuarioAdmin = '';
        $this->usuarioAdminSearch = '';
    }

    public function seleccionarRemitente(int $personaId, string $personaNombre): void
    {
        $this->edit_id_persona = (string) $personaId;
        $this->remitenteSearch = $personaNombre;
    }

    public function seleccionarCiudadOrigen(string $ciudad): void
    {
        $this->edit_ciudad_origen = $ciudad;
        $this->origenSearch = $ciudad;
    }

    public function seleccionarCiudadDestino(string $ciudad): void
    {
        $this->edit_ciudad_destino = $ciudad;
        $this->destinoSearch = $ciudad;
    }

    public function cambiarVista(string $vista): void
    {
        if (in_array($vista, ['listado', 'crear', 'revision'], true)) {
            $this->vistaActiva = $vista;
            $this->editingId = null;
            $this->confirmingPayId = null;
            $this->aplicarReglaRolUsuarioAdmin();
            $this->resetValidation();
        }
    }

    public function updatedSearchText(): void
    {
        $this->resetPage();
    }

    public function updatedFechaAdmin(): void
    {
        $hoy = now('America/Guayaquil')->toDateString();
        if ($this->fechaAdmin !== '' && $this->fechaAdmin > $hoy) {
            $this->fechaAdmin = '';
            $this->alertError('Fecha inválida', '¡Debe seleccionar una fecha igual o menor a la fecha actual!');
        }
    }

    public function solicitarPago(int $idCabecera): void
    {
        $this->confirmingPayId = $idCabecera;
    }

    public function cancelarConfirmacionPago(): void
    {
        $this->confirmingPayId = null;
    }

    public function confirmarPagoGuia(): void
    {
        if ($this->confirmingPayId === null) {
            return;
        }

        $user = $this->currentUser();
        if (! $user) {
            return;
        }

        $cabecera = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->findOrFail($this->confirmingPayId);

        if ($user->isBranchRestricted() && (int) $cabecera->id_sucursal !== (int) $user->id_sucursal) {
            $this->confirmingPayId = null;
            $this->alertError('Sin permiso', 'La guía pertenece a otra sucursal.');

            return;
        }

        $cabecera->update([
            'id_users_update' => $user->id,
            'estatus_cobro' => 'Cancelado',
            'fecha_cancelacion' => now('America/Guayaquil'),
        ]);

        $this->confirmingPayId = null;
        $this->alertSuccess('Guía cancelada', 'La guía se marcó como cancelada y se registró en tu caja.');
    }

    public function abrirEdicion(int $idCabecera): void
    {
        $cabecera = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->with([
                'persona',
                'detalles' => fn ($q) => $q->withoutGlobalScope(AssignedBranchScope::class),
            ])
            ->findOrFail($idCabecera);

        $this->editingId = $cabecera->id_cabecera;
        $this->edit_num_guia = (string) $cabecera->num_guia;
        $this->edit_id_forma_pago = (string) $cabecera->id_forma_pago;
        $this->edit_recargo = number_format((float) ($cabecera->recargo ?? 0), 3, '.', '');
        $this->edit_flete = number_format((float) $cabecera->flete, 2, '.', '');
        $this->edit_prima = number_format((float) ($cabecera->prima ?? 0), 2, '.', '');
        $this->edit_valor_guia = number_format((float) $cabecera->valor_guia, 2, '.', '');
        $this->edit_ciudad_origen = (string) $cabecera->ciudad_origen;
        $this->edit_ciudad_destino = (string) $cabecera->ciudad_destino;
        $this->edit_id_persona = (string) $cabecera->id_persona;
        $this->edit_nota = (string) ($cabecera->nota ?? '');

        $this->origenSearch = $this->edit_ciudad_origen;
        $this->destinoSearch = $this->edit_ciudad_destino;
        $this->remitenteSearch = (string) ($cabecera->persona?->nombre ?? $cabecera->nom_remitente ?? '');

        $this->editDetalles = $cabecera->detalles->map(fn (Detalle $d): array => [
            'id_detalle' => (int) $d->id_detalle,
            'cantidad' => (int) $d->cantidad,
            'descripcion' => (string) $d->descripcion,
            'v_unitario' => (float) $d->v_unitario,
            'v_parcial' => (float) $d->v_parcial,
        ])->all();
    }

    public function cerrarEdicion(): void
    {
        $this->editingId = null;
        $this->editDetalles = [];
        $this->remitenteSearch = '';
        $this->origenSearch = '';
        $this->destinoSearch = '';
        $this->resetValidation();
    }

    public function recalcularTotalesEdicion(): void
    {
        $flete = 0.0;
        foreach ($this->editDetalles as $i => $det) {
            $parcial = round(((float) ($det['cantidad'] ?? 0)) * ((float) ($det['v_unitario'] ?? 0)), 2);
            $this->editDetalles[$i]['v_parcial'] = $parcial;
            $flete += $parcial;
        }

        $flete = round($flete, 2);
        $recargo = (float) ($this->edit_recargo !== '' ? $this->edit_recargo : 0);
        $prima = (float) ($this->edit_prima !== '' ? $this->edit_prima : 0);

        $this->edit_flete = number_format($flete, 2, '.', '');
        $this->edit_valor_guia = number_format(round($flete + $prima + $recargo, 2), 2, '.', '');
    }

    public function guardarEdicion(): void
    {
        if ($this->editingId === null) {
            return;
        }

        $this->validate([
            'edit_num_guia' => ['required', 'integer'],
            'edit_id_forma_pago' => ['required', 'integer', 'exists:formas_pago,id_formas_pago'],
            'edit_recargo' => ['nullable', 'numeric', 'min:0'],
            'edit_prima' => ['nullable', 'numeric', 'min:0'],
            'edit_ciudad_origen' => ['required', 'string'],
            'edit_ciudad_destino' => ['required', 'string'],
            'edit_id_persona' => ['required', 'integer', 'exists:persona,id_persona'],
            'edit_nota' => ['nullable', 'string'],
        ]);

        $this->recalcularTotalesEdicion();
        $user = $this->currentUser();

        try {
            DB::transaction(function () use ($user): void {
                $cabecera = Cabecera::query()
                    ->withoutGlobalScope(AssignedBranchScope::class)
                    ->findOrFail($this->editingId);

                $formaPagoId = (int) $this->edit_id_forma_pago;

                $cabecera->update([
                    'num_guia' => (int) $this->edit_num_guia,
                    'id_forma_pago' => $formaPagoId,
                    'recargo' => (float) ($this->edit_recargo !== '' ? $this->edit_recargo : 0),
                    'flete' => (float) $this->edit_flete,
                    'prima' => (float) ($this->edit_prima !== '' ? $this->edit_prima : 0),
                    'valor_guia' => (float) $this->edit_valor_guia,
                    'ciudad_origen' => $this->edit_ciudad_origen,
                    'ciudad_destino' => $this->edit_ciudad_destino,
                    'id_persona' => (int) $this->edit_id_persona,
                    'nota' => $this->edit_nota,
                    'estatus_cobro' => $formaPagoId === 1 ? 'Cancelado' : 'Pendiente',
                    'id_users_update' => $user?->id ?? $cabecera->id_users_update,
                ]);

                foreach ($this->editDetalles as $det) {
                    Detalle::query()
                        ->withoutGlobalScope(AssignedBranchScope::class)
                        ->where('id_detalle', $det['id_detalle'])
                        ->where('id_cabecera', $cabecera->id_cabecera)
                        ->update([
                            'cantidad' => (int) $det['cantidad'],
                            'descripcion' => (string) $det['descripcion'],
                            'v_unitario' => (float) $det['v_unitario'],
                            'v_parcial' => (float) $det['v_parcial'],
                        ]);
                }
            });

            $this->editingId = null;
            $this->alertSuccess('Registro actualizado', 'La guía y sus detalles se guardaron correctamente.');
        } catch (\Throwable) {
            $this->alertError('Error', 'No se pudo guardar el registro.');
        }
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render()
    {
        $this->aplicarReglaRolUsuarioAdmin();

        $user = $this->currentUser();
        $isPrivileged = $this->isPrivilegedCajaUser();
        $search = trim($this->searchText);
        $fechaActiva = $this->fechaCaja ?: now('America/Guayaquil')->toDateString();

        $guiasPendientesQuery = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->with(['usuario', 'persona'])
            ->where('estado', '1')
            ->whereNull('fecha_cancelacion');

        if (! $isPrivileged && $user) {
            $guiasPendientesQuery->where('id_users', $user->id);
        }

        if ($user?->isBranchRestricted() && ! empty($user->id_sucursal)) {
            $guiasPendientesQuery->where('id_sucursal', $user->id_sucursal);
        }

        if ($search !== '') {
            $guiasPendientesQuery->where(function ($q) use ($search): void {
                $q->where('num_guia', 'like', "%{$search}%")
                    ->orWhere('nom_remitente', 'like', "%{$search}%")
                    ->orWhere('nom_destinatario', 'like', "%{$search}%");
            });
        }

        $cajaDiaQuery = Cabecera::query()
            ->withoutGlobalScope(AssignedBranchScope::class)
            ->where('estado', '1')
            ->whereDate('fecha_cancelacion', $fechaActiva);

        if (! $isPrivileged && $user) {
            $cajaDiaQuery->where('id_users_update', $user->id);
        } elseif ($user) {
            $targetUserId = $this->usuarioAdmin !== '' ? (int) $this->usuarioAdmin : (int) $user->id;
            $cajaDiaQuery->where('id_users_update', $targetUserId);
        }

        if ($user?->isBranchRestricted() && ! empty($user->id_sucursal)) {
            $cajaDiaQuery->where('id_sucursal', $user->id_sucursal);
        }

        $guiasCajaDia = (clone $cajaDiaQuery)->orderBy('fecha_cancelacion')->get();
        $totalCajaDia = (float) $guiasCajaDia->sum(fn (Cabecera $g) => (float) $g->valor_guia);

        $allUsers = User::query()->where('estado', '1')->orderBy('name')->get(['id', 'name']);
        $userQuery = mb_strtolower(trim($this->usuarioAdminSearch));
        $filteredUsers = $allUsers->filter(function (User $u) use ($userQuery): bool {
            if ($userQuery === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) $u->name), $userQuery);
        })->take(12)->values();

        $allPersonas = Persona::query()->where('estado', '1')->orderBy('nombre')->get(['id_persona', 'nombre']);
        $remQuery = mb_strtolower(trim($this->remitenteSearch));
        $filteredPersonas = $allPersonas->filter(function (Persona $p) use ($remQuery): bool {
            if ($remQuery === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) $p->nombre), $remQuery);
        })->take(12)->values();

        $allCiudades = Ciudad::query()->where('estado', '1')->orderBy('descripcion')->get(['id_ciudad', 'descripcion']);
        $oriQuery = mb_strtolower(trim($this->origenSearch));
        $filteredOrigenes = $allCiudades->filter(function (Ciudad $c) use ($oriQuery): bool {
            if ($oriQuery === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) $c->descripcion), $oriQuery);
        })->take(12)->values();

        $desQuery = mb_strtolower(trim($this->destinoSearch));
        $filteredDestinos = $allCiudades->filter(function (Ciudad $c) use ($desQuery): bool {
            if ($desQuery === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) $c->descripcion), $desQuery);
        })->take(12)->values();

        return view('livewire.resources.caja-manager', [
            'guias' => $guiasPendientesQuery->orderBy('created_at')->paginate(7),
            'guiasCajaDia' => $guiasCajaDia,
            'totalCajaDia' => $totalCajaDia,
            'formasPago' => FormasPago::query()->where('estado', '1')->orderBy('descripcion')->get(),
            'filteredUsers' => $filteredUsers,
            'filteredPersonas' => $filteredPersonas,
            'filteredOrigenes' => $filteredOrigenes,
            'filteredDestinos' => $filteredDestinos,
            'isPrivilegedCajaUser' => $isPrivileged,
        ]);
    }
}