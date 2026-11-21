<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel principal' }} · {{ config('app.name', 'SIGRExpress') }}</title>
    
    <!-- Fuentes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #17243b;
            background: #f5f7fb;
            --brand-primary: #185abc;
            --brand-dark: #14243b;
        }
        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; background: #f5f7fb; color: #17243b; }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        .shell { min-height: 100vh; }
        .side { position: fixed; inset: 0 auto 0 0; z-index: 5; display: flex; width: 250px; flex-direction: column; overflow-y: auto; padding: 22px 15px; background: #14243b; color: #fff; }
        .brand { display: flex; align-items: center; gap: 11px; padding: 4px 9px 24px; }
        .brand-mark { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 11px; background: #2671dc; font-size: 13px; font-weight: 800; color: #fff; }
        .brand-name { font-size: 15px; font-weight: 750; letter-spacing: -.02em; }
        .brand-caption { font-size: 10px; color: #8495ad; letter-spacing: .02em; }
        .nav-title { margin: 18px 9px 7px; color: #8495ad; font-size: 10px; font-weight: 750; letter-spacing: .12em; text-transform: uppercase; }
        .nav-item { display: flex; align-items: center; gap: 9px; margin: 2px 0; padding: 9px 11px; border-radius: 8px; color: #aab8cc; font-size: 12px; transition: background .15s, color .15s; }
        .nav-item:hover, .nav-item.active { background: #223957; color: #fff; }
        .nav-bullet { width: 6px; height: 6px; border-radius: 50%; background: #4a6385; }
        .nav-item:hover .nav-bullet, .nav-item.active .nav-bullet { background: #388bfd; }
        .side-bottom { margin-top: auto; padding: 16px 9px 0; border-top: 1px solid #2b3c54; color: #aab8cc; font-size: 11px; line-height: 1.55; }
        .main { min-height: 100vh; margin-left: 250px; }
        .top { position: sticky; top: 0; z-index: 4; display: flex; min-height: 68px; align-items: center; justify-content: space-between; padding: 0 32px; border-bottom: 1px solid #e8edf4; background: rgb(255 255 255 / 96%); backdrop-filter: blur(10px); }
        .crumb { color: #77849a; font-size: 12px; }
        .crumb strong { color: #17243b; font-weight: 650; }
        .account { display: flex; align-items: center; gap: 12px; }
        .account-copy { text-align: right; }
        .account-name { font-size: 12px; font-weight: 700; color: #17243b; }
        .account-email { margin-top: 2px; color: #77849a; font-size: 10px; }
        .avatar { display: grid; width: 35px; height: 35px; place-items: center; border-radius: 50%; background: #e8f0fe; color: #185abc; font-size: 12px; font-weight: 800; border: 1px solid #cce0ff; }
        .logout { padding: 7px 12px; border: 1px solid #e8edf4; border-radius: 8px; background: #fff; color: #536176; cursor: pointer; font-size: 11px; font-weight: 650; transition: all .15s; }
        .logout:hover { border-color: #cbd6e5; background: #f8fafc; color: #b42318; }
        .content { width: min(100%, 1500px); margin: 0 auto; padding: 32px; }

        /* Componentes y utilidades modernas */
        .welcome { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 25px; }
        .eyebrow { margin: 0 0 7px; color: #185abc; font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0; font-size: 28px; font-weight: 750; letter-spacing: -.04em; }
        h2 { margin: 0; font-size: 17px; font-weight: 720; letter-spacing: -.025em; }
        .welcome-copy { max-width: 680px; margin: 8px 0 0; color: #77849a; font-size: 13.5px; line-height: 1.6; }
        .date-chip { flex: 0 0 auto; padding: 9px 14px; border: 1px solid #e8edf4; border-radius: 10px; background: #fff; color: #58677e; font-size: 12px; box-shadow: 0 2px 6px rgb(27 45 74 / 3%); }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; margin-bottom: 34px; }
        .stat { min-height: 110px; padding: 18px 20px; border: 1px solid #e8edf4; border-radius: 12px; background: #fff; box-shadow: 0 3px 12px rgb(27 45 74 / 2%); }
        .stat-label { color: #77849a; font-size: 12px; font-weight: 500; }
        .stat-value { margin-top: 10px; font-size: 28px; font-weight: 760; letter-spacing: -.04em; color: #17243b; }
        .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 15px; margin: 26px 0 14px; }
        .section-head p { margin: 4px 0 0; color: #77849a; font-size: 12px; }
        .module-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; margin-bottom: 28px; }
        .metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
        .module-card { display: flex; min-height: 170px; flex-direction: column; padding: 19px; border: 1px solid #e8edf4; border-radius: 12px; background: #fff; transition: transform .15s, box-shadow .15s, border-color .15s; }
        .module-card:hover { transform: translateY(-2px); border-color: #cbd8eb; box-shadow: 0 10px 24px rgb(27 45 74 / 6%); }
        .card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .module-icon { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #eef4ff; color: #185abc; font-size: 11px; font-weight: 800; }
        .badge { padding: 4px 8px; border-radius: 20px; background: #f1f4f8; color: #68768a; font-size: 10px; font-weight: 700; }
        .badge.active { background: #eaf7ef; color: #217447; }
        .badge.security { background: #fbf2ea; color: #b25e09; }
        .module-title { margin: 14px 0 5px; font-size: 14px; font-weight: 740; color: #17243b; }
        .module-description { margin: 0; color: #77849a; font-size: 12px; line-height: 1.55; }
        .card-footer { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 14px; border-top: 1px solid #f6f8fb; }
        .status { color: #8490a2; font-size: 10px; font-weight: 650; }
        .open-link { color: #185abc; font-size: 11px; font-weight: 750; }
        .callout { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 20px 24px; border: 1px solid #dce8f8; border-radius: 12px; background: #edf4ff; margin-top: 15px; }
        .callout strong { display: block; font-size: 13px; color: #17243b; }
        .callout p { margin: 4px 0 0; color: #61738c; font-size: 12px; line-height: 1.55; }

        /* Estilos CRUD, Paneles, Tablas y Modales */
        .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
        .heading-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
        .panel { overflow: hidden; border: 1px solid #e8edf4; border-radius: 12px; background: #fff; box-shadow: 0 3px 12px rgb(27 45 74 / 2%); }
        .panel-toolbar { display: flex; min-height: 62px; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #edf0f5; flex-wrap: wrap; }
        .search-box { display: flex; width: min(100%, 360px); align-items: center; gap: 8px; color: #77849a; }
        .control { width: 100%; min-height: 38px; padding: 8px 12px; border: 1px solid #d9e0ea; border-radius: 8px; outline: none; background: #fff; color: #17243b; font-size: 12.5px; transition: border-color .15s, box-shadow .15s; }
        .control:focus { border-color: #2671dc; box-shadow: 0 0 0 3px rgb(38 113 220 / 12%); }
        .button { display: inline-flex; min-height: 38px; align-items: center; justify-content: center; gap: 7px; padding: 8px 16px; border: 1px solid transparent; border-radius: 8px; cursor: pointer; font-size: 12.5px; font-weight: 700; transition: all .15s; }
        .button.primary { background: #185abc; color: #fff; }
        .button.primary:hover { background: #124a9e; }
        .button.secondary { border-color: #d9e0ea; background: #fff; color: #536176; }
        .button.secondary:hover { border-color: #cbd6e5; background: #f8fafc; }
        .button.danger { background: #b42318; color: #fff; }
        .button.danger:hover { background: #911c13; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 12px 15px; background: #f8f9fc; color: #68768a; font-size: 10.5px; font-weight: 750; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; border-bottom: 1px solid #edf0f5; }
        td { max-width: 320px; padding: 13px 15px; border-top: 1px solid #edf0f5; color: #344158; font-size: 12.5px; overflow-wrap: anywhere; }
        tbody tr:hover { background: #fbfcfe; }
        .row-actions { display: flex; justify-content: flex-end; gap: 10px; white-space: nowrap; }
        .text-button { padding: 0; border: 0; background: none; color: #185abc; cursor: pointer; font-size: 11.5px; font-weight: 700; }
        .text-button.danger { color: #b42318; }
        .empty-state { padding: 45px 16px; color: #77849a; text-align: center; }
        .empty-state strong { display: block; margin-bottom: 6px; font-size: 14px; color: #344158; }
        .pagination-container { padding: 14px 18px; border-top: 1px solid #edf0f5; }

        /* Modales Emergentes (resource-modal) */
        .resource-modal-backdrop { position: fixed; inset: 0; z-index: 50; display: grid; place-items: center; padding: 20px; background: rgb(12 23 40 / 70%); backdrop-filter: blur(4px); overflow-y: auto; }
        .resource-modal { display: flex; width: min(100%, 780px); max-height: min(92dvh, 880px); flex-direction: column; overflow: hidden; border: 1px solid #e8edf4; border-radius: 14px; background: #fff; box-shadow: 0 24px 80px rgb(7 18 35 / 30%); margin: auto; }
        .resource-modal-lg { width: min(100%, 1050px); }
        .resource-modal-header { display: flex; flex: none; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 18px 24px; border-bottom: 1px solid #edf0f5; }
        .resource-modal-header h2 { font-size: 18px; margin: 0; font-weight: 750; }
        .resource-modal-header .close-btn { width: 32px; height: 32px; border: 1px solid #e8edf4; border-radius: 8px; background: #fff; color: #68768a; cursor: pointer; font-size: 18px; display: grid; place-items: center; }
        .resource-modal > form { display: flex; min-height: 0; flex: 1 1 auto; flex-direction: column; }
        .resource-modal-body { min-height: 0; flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; padding: 22px 24px; }
        .resource-modal-footer { position: sticky; bottom: 0; z-index: 20; display: flex; flex: none; justify-content: flex-end; gap: 10px; padding: 14px 24px; border-top: 1px solid #edf0f5; background: #fbfcfe; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 18px; }
        .form-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .field { min-width: 0; }
        .field-wide { grid-column: 1 / -1; }
        .field > label { display: block; margin: 0 0 6px; color: #344158; font-size: 11.5px; font-weight: 700; }
        .field-error { display: block; margin-top: 4px; color: #b42318; font-size: 11px; }

        /* Detalle dinámico en Guías */
        .detail-card { border: 1px solid #e8edf4; border-radius: 10px; padding: 16px; background: #fbfcfe; margin-top: 14px; }
        .detail-line { display: grid; grid-template-columns: 90px 1fr 120px 120px 40px; align-items: end; gap: 12px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #edf0f5; }

        @media (max-width: 1080px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .module-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .side { position: static; width: 100%; max-height: 280px; }
            .main { margin-left: 0; }
            .top { min-height: 60px; padding: 0 16px; }
            .account-copy { display: none; }
            .content { padding: 20px 16px; }
            .welcome { align-items: flex-start; flex-direction: column; }
            .stats { grid-template-columns: 1fr; }
            .module-grid { grid-template-columns: 1fr; }
            .metric-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <!-- BARRA LATERAL (SIDEBAR #14243b) -->
    <aside class="side">
        <a class="brand" href="{{ route('dashboard') }}" wire:navigate>
            <span class="brand-mark">SE</span>
            <div>
                <span class="brand-name">SIGRExpress</span>
                <span class="brand-caption" style="display:block">Transportes Jhetro S.A.</span>
            </div>
        </a>

        @php
            $routeHref = fn(string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
        @endphp

        <nav aria-label="Menú principal">
            <!-- PANEL PRINCIPAL -->
            <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Panel Principal</span>
            </a>

            <!-- 1. OPERACIÓN -->
            <p class="nav-title">Operación</p>
            @can('guias.ver')
            <a class="nav-item {{ request()->routeIs('resources.cabecera.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.cabecera.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Guías / Cabecera</span>
            </a>
            {{-- <a class="nav-item {{ request()->routeIs('resources.detalle.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.detalle.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Detalles de Guías</span>
            </a> --}}
            {{-- <a class="nav-item {{ request()->routeIs('resources.guia-estado.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.guia-estado.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Estados de Guías</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.manifiesto.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.manifiesto.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Manifiestos de Carga</span>
            </a> --}}
            @endcan

            @can('clientes.ver')
            <a class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
               href="{{ $routeHref('reports.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Reportes Gerenciales</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.persona.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.persona.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Personas</span>
            </a>
@endcan

            <!-- 2. RECURSOS HUMANOS (RRHH) -->
            @can('clientes.ver')
            <p class="nav-title">Recursos Humanos (RRHH)</p>
            <a class="nav-item {{ request()->routeIs('resources.persona.index') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.persona.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Control Personal / Empleados</span>
            </a>
@endcan

            <!-- 3. ADMINISTRACIÓN -->
            @can('roles.gestionar')
            <p class="nav-title">Administración</p>
            <a class="nav-item {{ request()->routeIs('resources.empresa.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.empresa.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Empresas</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.sucursal.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.sucursal.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Sucursales</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.ciudad.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.ciudad.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Ciudades</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.cargo.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.cargo.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Cargos</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.motivo-traslado.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.motivo-traslado.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Motivos de Traslado</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.formas-pago.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.formas-pago.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Formas de Pago</span>
            </a>
            <a class="nav-item {{ request()->routeIs('resources.vehiculo.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.vehiculo.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Vehículos</span>
            </a>
@endcan

            <!-- 4. SEGURIDAD -->
            <p class="nav-title">Seguridad</p>
            @can('usuarios.ver')
            <a class="nav-item {{ request()->routeIs('resources.users.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.users.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Usuarios</span>
            </a>
            @endcan
            
            @can('roles.gestionar')
            <a class="nav-item {{ request()->routeIs('resources.roles.*') ? 'active' : '' }}" 
               href="{{ $routeHref('resources.roles.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Roles y Permisos</span>
            </a>
            @endcan
            <a class="nav-item {{ request()->routeIs('manual.*') ? 'active' : '' }}" 
               href="{{ $routeHref('manual.index') }}" wire:navigate>
                <span class="nav-bullet"></span>
                <span>Manual de Ayuda</span>
            </a>
        </nav>

        <div class="side-bottom">
            <strong style="color:#fff">SIGRExpress</strong><br>
            Sistema Integral de Guías de Remisión
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <div class="main">
        <!-- TOPBAR ESTÁTICA -->
        <header class="top">
            <div class="crumb">
                SIGRExpress <span aria-hidden="true">/</span> <strong>{{ $title ?? 'Panel Principal' }}</strong>
            </div>
            
            <div class="account">
                <div class="account-copy">
                    <div class="account-name">{{ auth()->user()->name ?? 'Usuario' }}</div>
                    <div class="account-email">{{ auth()->user()->email ?? '' }}</div>
                </div>
                <div class="avatar" aria-hidden="true">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit">Salir</button>
                </form>
            </div>
        </header>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="content">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
@include('sweetalert::alert')

<script data-navigate-once>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            if (typeof Swal !== 'undefined') {
                if ((data.icon || data.type) === 'success') {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Su registro ha sido guardado exitosamente",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    return;
                }

                Swal.fire({
                    icon: data.icon || data.type || 'success',
                    title: data.title || '',
                    text: data.text || data.message || '',
                    timer: data.timer || 3500,
                    showConfirmButton: data.showConfirmButton ?? true,
                    confirmButtonColor: '#185abc',
                });
            }
        });

        Livewire.on('swal:toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: data.position || 'top-end',
                    icon: data.icon || data.type || 'success',
                    title: data.title || data.message || '',
                    showConfirmButton: false,
                    timer: data.timer || 3000,
                    timerProgressBar: true,
                });
            }
        });
    });
</script>
</body>
</html>
