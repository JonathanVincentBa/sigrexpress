<?php

namespace App\Livewire\Resources;

use App\Http\Controllers\ReportesController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('components.layouts.app')]
#[Title('Reportes Gerenciales')]
class ReportesManager extends Component
{
    public int|string|null $sucursalSecretariaId = null;

    public string $ciudadSecretaria = '';

    public bool $isSecretaria = false;

    public bool $generandoReporte = false;

    public string $reportCategoryTab = 'todos';

    public string $branchSearch = '';

    public string $userSearch = '';

    public string $tipo_reporte = '';

    public string $fecha_inicio = '';

    public string $fecha_final = '';

    public string $nom_remitente = '';

    public string $customerSearch = '';

    public string $ciudad_origen = '';

    /** @var list<string> */
    public array $ciudad_destino = [];

    public string $destinationCitySearch = '';

    public string $id_users = '';

    /** @var array<string, array{label: string, bloque: string}> */
    public array $reportMeta = [
        'cliente_xfacturar' => ['label' => 'Guías por facturar (por cliente)', 'bloque' => 'cliente'],
        'cliente_xfacturar_canceladas' => ['label' => 'Guías por facturar canceladas (por cliente)', 'bloque' => 'cliente'],
        'cliente_xfacturar_xpagar' => ['label' => 'Guías por facturar pendientes de pago (por cliente)', 'bloque' => 'cliente'],
        'cliente_xfacturar_xpagar_todos' => ['label' => 'Guías por facturar pendientes (Todos los clientes)', 'bloque' => 'ninguno'],
        'cliente_todas_formas_pago' => ['label' => 'Historial completo (Todas las formas de pago)', 'bloque' => 'cliente'],
        'manifiesto_destino' => ['label' => 'Manifiesto por ciudades de destino', 'bloque' => 'destino'],
        'manifiesto_origen' => ['label' => 'Manifiesto por sucursal de origen', 'bloque' => 'origen'],
        'cobro_pendiente' => ['label' => 'Guías pendientes de pago (Al cobro)', 'bloque' => 'destino'],
        'cobro_pagado' => ['label' => 'Guías pagadas (Al cobro)', 'bloque' => 'destino'],
        'cierres_diarios' => ['label' => 'Cierre de caja diario por usuario', 'bloque' => 'usuario'],
        'sucursal_xfacturar_canceladas' => ['label' => 'Guías por facturar canceladas (por sucursal origen)', 'bloque' => 'origen'],
        'sucursal_xfacturar_xpagar' => ['label' => 'Guías por facturar pendientes (por sucursal origen)', 'bloque' => 'origen'],
        'anuladas' => ['label' => 'Reporte de guías anuladas', 'bloque' => 'ninguno'],
    ];

    public function mount(): void
    {
        $this->fecha_inicio = now()->startOfMonth()->toDateString();
        $this->fecha_final = now()->toDateString();

        $this->resolverRestriccionSecretaria();
    }

    protected function resolverRestriccionSecretaria(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $tieneRolSecretaria = false;
        if (method_exists($user, 'hasAnyRole')) {
            $tieneRolSecretaria = $user->hasAnyRole(['secretaria', 'Secretaria', 'SECRETARIA']);
        } elseif (method_exists($user, 'hasRole')) {
            $tieneRolSecretaria = $user->hasRole(['secretaria', 'Secretaria', 'SECRETARIA']);
        }

        $restringidoPorSucursal = method_exists($user, 'isBranchRestricted')
            ? $user->isBranchRestricted()
            : (! empty($user->id_sucursal));

        if (! ($tieneRolSecretaria || $restringidoPorSucursal) || empty($user->id_sucursal)) {
            $this->isSecretaria = false;
            $this->ciudadSecretaria = '';
            $this->sucursalSecretariaId = null;

            return;
        }

        $sucursalData = DB::table('sucursal as s')
            ->leftJoin('ciudad as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.id_sucursal', $user->id_sucursal)
            ->select('s.id_sucursal', 's.nombre as sucursal_nombre', 'c.descripcion as ciudad_nombre')
            ->first();

        $ciudadNombre = trim((string) ($sucursalData->ciudad_nombre ?? ''));
        $sucursalNombre = trim((string) ($sucursalData->sucursal_nombre ?? ''));
        $valorRestringido = $ciudadNombre !== '' ? $ciudadNombre : $sucursalNombre;

        if ($valorRestringido !== '') {
            $this->isSecretaria = true;
            $this->ciudadSecretaria = $valorRestringido;
            $this->sucursalSecretariaId = $sucursalData->id_sucursal ?? $user->id_sucursal;
            $this->aplicarCiudadFijaSecretaria();
        }
    }

    protected function aplicarCiudadFijaSecretaria(): void
    {
        if (! $this->isSecretaria || $this->ciudadSecretaria === '') {
            return;
        }

        $bloque = $this->activeFilterType();

        if ($bloque === 'origen') {
            $this->ciudad_origen = $this->ciudadSecretaria;
            $this->branchSearch = '';
        }

        if ($bloque === 'destino') {
            $this->ciudad_destino = [$this->ciudadSecretaria];
            $this->destinationCitySearch = '';
        }
    }

    public function updatedTipoReporte(): void
    {
        $bloque = $this->activeFilterType();

        if ($bloque !== 'cliente') {
            $this->nom_remitente = '';
            $this->customerSearch = '';
        }

        if ($bloque !== 'origen') {
            $this->ciudad_origen = '';
            $this->branchSearch = '';
        }

        if ($bloque !== 'destino') {
            $this->ciudad_destino = [];
            $this->destinationCitySearch = '';
        }

        if ($bloque !== 'usuario') {
            $this->id_users = '';
            $this->userSearch = '';
        }

        $this->aplicarCiudadFijaSecretaria();
    }

    public function updatedFechaInicio(): void
    {
        if ($this->fecha_inicio !== '' && $this->fecha_final !== '' && $this->fecha_final < $this->fecha_inicio) {
            $this->fecha_final = $this->fecha_inicio;
        }
    }

    public function selectCliente(string $nombre): void
    {
        $this->nom_remitente = trim($nombre);
        $this->customerSearch = '';
    }

    public function clearCliente(): void
    {
        $this->nom_remitente = '';
        $this->customerSearch = '';
    }

    public function toggleCiudadDestino(string $ciudad): void
    {
        if ($this->isSecretaria && $this->ciudadSecretaria !== '') {
            $this->ciudad_destino = [$this->ciudadSecretaria];

            return;
        }

        $ciudad = trim($ciudad);
        if ($ciudad === '') {
            return;
        }

        if (in_array($ciudad, $this->ciudad_destino, true)) {
            $this->ciudad_destino = array_values(array_diff($this->ciudad_destino, [$ciudad]));
        } else {
            $this->ciudad_destino[] = $ciudad;
        }
    }

    public function removeCiudadDestino(string $ciudad): void
    {
        if ($this->isSecretaria && $this->ciudadSecretaria !== '') {
            $this->ciudad_destino = [$this->ciudadSecretaria];

            return;
        }

        $this->ciudad_destino = array_values(array_diff($this->ciudad_destino, [trim($ciudad)]));
    }

    public function clearCiudadesDestino(): void
    {
        if ($this->isSecretaria && $this->ciudadSecretaria !== '') {
            $this->ciudad_destino = [$this->ciudadSecretaria];

            return;
        }

        $this->ciudad_destino = [];
        $this->destinationCitySearch = '';
    }

    public function activeFilterType(): string
    {
        return $this->reportMeta[$this->tipo_reporte]['bloque'] ?? 'ninguno';
    }

    private function extraerNombreArchivo(\Symfony\Component\HttpFoundation\Response $response, string $fallback): string
    {
        $disposition = (string) $response->headers->get('content-disposition', '');
        if ($disposition !== '' && preg_match("/filename\\*=UTF-8''([^;]+)|filename=\"?([^\";]+)\"?/i", $disposition, $m)) {
            $raw = $m[1] !== '' ? urldecode($m[1]) : $m[2];
            $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($raw));
            if ($clean !== '') {
                return $clean;
            }
        }

        return $fallback;
    }

    public function generar(string $formato)
    {
        if ($this->generandoReporte) {
            return null;
        }
        $this->generandoReporte = true;

        try {
            $bloque = $this->activeFilterType();

            $rules = [
                'tipo_reporte' => ['required', 'string'],
                'fecha_inicio' => ['required', 'date'],
                'fecha_final' => ['required', 'date', 'after_or_equal:fecha_inicio'],
                'formato' => ['required', 'in:pdf,excel'],
            ];

            $payload = [
                'tipo_reporte' => $this->tipo_reporte,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_final' => $this->fecha_final,
                'formato' => $formato,
            ];

            if ($this->isSecretaria && $this->ciudadSecretaria !== '') {
                if ($bloque === 'origen') {
                    $this->ciudad_origen = $this->ciudadSecretaria;
                } elseif ($bloque === 'destino') {
                    $this->ciudad_destino = [$this->ciudadSecretaria];
                }
            }

            if ($bloque === 'cliente') {
                $rules['nom_remitente'] = ['required', 'string', 'max:150'];
                $payload['nom_remitente'] = $this->nom_remitente;
            } elseif ($bloque === 'origen') {
                $rules['ciudad_origen'] = ['required', 'string', 'max:100'];
                $payload['ciudad_origen'] = $this->ciudad_origen;
            } elseif ($bloque === 'destino') {
                $rules['ciudad_destino'] = ['required', 'array', 'min:1'];
                $payload['ciudad_destino'] = $this->ciudad_destino;
            } elseif ($bloque === 'usuario') {
                $rules['id_users'] = ['required'];
                $payload['id_users'] = $this->id_users;
            }

            $validated = validator($payload, $rules)->validate();

            $request = request()->merge($validated);
            $response = app(ReportesController::class)->generar($request);

            $ext = $formato === 'excel' ? 'xlsx' : 'pdf';
            $fallbackName = ($payload['tipo_reporte'] ?? 'reporte') . '_' . now()->format('Ymd_His') . '.' . $ext;
            $filename = $this->extraerNombreArchivo($response, $fallbackName);

            if ($response instanceof BinaryFileResponse) {
                $mime = $formato === 'excel'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'application/pdf';

                return response()->download($response->getFile()->getPathname(), $filename, [
                    'Content-Type' => $mime,
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }

            $content = $response->getContent();
            $contentType = $formato === 'excel'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : $response->headers->get('content-type', 'application/pdf');

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, [
                'Content-Type' => $contentType,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } finally {
            $this->generandoReporte = false;
        }
    }

    public function render()
    {
        $personas = Cache::remember('reportes.remitentes.list', now()->addMinutes(10), function () {
            return DB::table('cabecera')
                ->select('nom_remitente')
                ->whereNotNull('nom_remitente')
                ->where('nom_remitente', '!=', '')
                ->distinct()
                ->orderBy('nom_remitente', 'asc')
                ->limit(1000)
                ->pluck('nom_remitente')
                ->toArray();
        });

        $sucursales = Cache::remember('reportes.sucursales.nombres.v1', now()->addMinutes(10), function () {
            return DB::table('sucursal')
                ->where('estado', '1')
                ->whereNull('deleted_at')
                ->whereNotNull('nombre')
                ->where('nombre', '!=', '')
                ->orderBy('nombre', 'asc')
                ->pluck('nombre')
                ->toArray();
        });

        $ciudades = Cache::remember('reportes.ciudades.list', now()->addMinutes(10), function () {
            return DB::table('ciudad')
                ->where('estado', '1')
                ->orderBy('descripcion', 'asc')
                ->pluck('descripcion')
                ->toArray();
        });

        $usuarios = Cache::remember('reportes.usuarios.v2', now()->addMinutes(10), function () {
            return DB::table('users')
                ->where('estado', '1')
                ->orderBy('name', 'asc')
                ->select('id', 'name')
                ->get()
                ->map(fn($u) => ['id' => (int) $u->id, 'name' => (string) $u->name])
                ->toArray();
        });

        $filteredClientes = [];
        $terminoCliente = trim($this->customerSearch);

        if ($this->activeFilterType() === 'cliente' && mb_strlen($terminoCliente) >= 1) {
            $q = mb_strtolower($terminoCliente);

            $filteredClientes = DB::table('cabecera')
                ->whereNotNull('nom_remitente')
                ->where('nom_remitente', '!=', '')
                ->whereRaw('LOWER(nom_remitente) LIKE ?', ['%' . $q . '%'])
                ->select('nom_remitente')
                ->distinct()
                ->orderBy('nom_remitente', 'asc')
                ->limit(35)
                ->pluck('nom_remitente')
                ->map(fn($nombre) => (string) $nombre)
                ->values()
                ->toArray();
        }

        $filteredCiudades = collect($ciudades)->filter(function ($city) {
            $q = trim($this->destinationCitySearch);
            if ($q === '') {
                return true;
            }

            return str_contains(mb_strtolower((string) $city), mb_strtolower($q));
        })->values();

        return view('livewire.resources.reportes-manager', [
            'isSecretaria' => $this->isSecretaria,
            'ciudadSecretaria' => $this->ciudadSecretaria,
            'reportCategoryTab' => $this->reportCategoryTab,
            'branchSearch' => $this->branchSearch,
            'userSearch' => $this->userSearch,
            'tipo_reporte' => $this->tipo_reporte,
            'reportMeta' => $this->reportMeta,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_final' => $this->fecha_final,
            'nom_remitente' => $this->nom_remitente,
            'customerSearch' => $this->customerSearch,
            'ciudad_origen' => $this->ciudad_origen,
            'ciudad_destino' => $this->ciudad_destino,
            'destinationCitySearch' => $this->destinationCitySearch,
            'id_users' => $this->id_users,
            'activeFilter' => $this->activeFilterType(),
            'sucursales' => $sucursales,
            'usuarios' => $usuarios,
            'filteredClientes' => $filteredClientes,
            'filteredCiudades' => $filteredCiudades,
        ]);
    }
}
