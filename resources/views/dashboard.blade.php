<x-layouts.app :title="__('Panel Principal')">
    @php
        $guiasCount = \App\Models\Cabecera::count();
        $personasCount = \App\Models\Persona::count();
        $empresasCount = \App\Models\Empresa::count();
        $usuariosCount = \App\Models\User::count();

        $routeHref = fn(string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
    @endphp

    <!-- ENCABEZADO DE BIENVENIDA -->
    <section class="welcome">
        <div>
            <p class="eyebrow">Panel de Control General</p>
            <h1>Hola, {{ auth()->user()->name }}</h1>
            <p class="welcome-copy">
                Bienvenido al sistema SIGRExpress de Transportes Jhetro S.A. Desde este panel tienes acceso directo a la gestión operativa, catálogos administrativos y control de seguridad.
            </p>
        </div>
        <div class="date-chip">
            📅 {{ ucfirst(now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}
        </div>
    </section>

    <!-- MÉTRICAS PRINCIPALES -->
    <section class="stats" aria-label="Métricas Principales">
        <article class="stat">
            <div class="stat-label">Guías Registradas</div>
            <div class="stat-value">{{ number_format($guiasCount) }}</div>
        </article>
        <article class="stat">
            <div class="stat-label">Personas / Clientes</div>
            <div class="stat-value">{{ number_format($personasCount) }}</div>
        </article>
        <article class="stat">
            <div class="stat-label">Empresas Activas</div>
            <div class="stat-value">{{ number_format($empresasCount) }}</div>
        </article>
        <article class="stat">
            <div class="stat-label">Usuarios del Sistema</div>
            <div class="stat-value">{{ number_format($usuariosCount) }}</div>
        </article>
    </section>

    <!-- 1. SECCIÓN OPERACIÓN -->
    <section aria-labelledby="operacion-title">
        <div class="section-head" id="operacion-title">
            <div>
                <h2>Operación y Envíos</h2>
                <p>Gestión de guías de transporte, trazabilidad de carga y directorio de personas.</p>
            </div>
        </div>

        <div class="module-grid">
            <a class="module-card" href="{{ $routeHref('resources.cabecera.index') }}">
                <span class="card-top">
                    <span class="module-icon">GU</span>
                    <span class="badge active">Operativo</span>
                </span>
                <span class="module-title">Guías / Cabecera</span>
                <span class="module-description">Emisión, control y seguimiento de guías de remisión y transporte de carga pesada.</span>
                <span class="card-footer">
                    <span class="status">Tabla: cabecera</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.detalle.index') }}">
                <span class="card-top">
                    <span class="module-icon">DT</span>
                    <span class="badge active">Operativo</span>
                </span>
                <span class="module-title">Detalles de Guías</span>
                <span class="module-description">Desglose de bultos, ítems transportados, cantidades y valores parciales de flete.</span>
                <span class="card-footer">
                    <span class="status">Tabla: detalle</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.persona.index') }}">
                <span class="card-top">
                    <span class="module-icon">PS</span>
                    <span class="badge">Directorio</span>
                </span>
                <span class="module-title">Personas / Contactos</span>
                <span class="module-description">Padrón de clientes, remitentes, destinatarios y receptores de mercadería.</span>
                <span class="card-footer">
                    <span class="status">Tabla: persona</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>
        </div>
    </section>

    <!-- 2. SECCIÓN RECURSOS HUMANOS (RRHH) -->
    <section aria-labelledby="rrhh-title">
        <div class="section-head" id="rrhh-title">
            <div>
                <h2>Recursos Humanos (RRHH)</h2>
                <p>Control del personal operativo, asignación de cargos y distribución por sucursales.</p>
            </div>
        </div>

        <div class="module-grid">
            <a class="module-card" href="{{ $routeHref('resources.persona.index') }}">
                <span class="card-top">
                    <span class="module-icon">EM</span>
                    <span class="badge">Personal</span>
                </span>
                <span class="module-title">Control Personal / Empleados</span>
                <span class="module-description">Gestión integral de colaboradores, choferes, auxiliares de carga y personal en sucursales.</span>
                <span class="card-footer">
                    <span class="status">Tabla: persona / cargo</span>
                    <span class="open-link">Ver empleados →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.cargo.index') }}">
                <span class="card-top">
                    <span class="module-icon">CG</span>
                    <span class="badge">Estructura</span>
                </span>
                <span class="module-title">Cargos Laborales</span>
                <span class="module-description">Estructura de puestos de trabajo y categorías laborales de la organización.</span>
                <span class="card-footer">
                    <span class="status">Tabla: cargo</span>
                    <span class="open-link">Gestionar cargos →</span>
                </span>
            </a>
        </div>
    </section>

    <!-- 3. SECCIÓN ADMINISTRACIÓN -->
    <section aria-labelledby="admin-title">
        <div class="section-head" id="admin-title">
            <div>
                <h2>Administración y Catálogos</h2>
                <p>Estructura empresarial, agencias, destinos y parámetros operativos.</p>
            </div>
        </div>

        <div class="module-grid">
            <a class="module-card" href="{{ $routeHref('resources.empresa.index') }}">
                <span class="card-top">
                    <span class="module-icon">EP</span>
                    <span class="badge">Catálogo</span>
                </span>
                <span class="module-title">Empresas</span>
                <span class="module-description">Datos fiscales, RUC, porcentaje de impuesto y configuración de la empresa emisora.</span>
                <span class="card-footer">
                    <span class="status">Tabla: empresa</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.sucursal.index') }}">
                <span class="card-top">
                    <span class="module-icon">SC</span>
                    <span class="badge">Catálogo</span>
                </span>
                <span class="module-title">Sucursales / Agencias</span>
                <span class="module-description">Oficinas de recepción, puntos de despacho y agencias autorizadas a nivel nacional.</span>
                <span class="card-footer">
                    <span class="status">Tabla: sucursales</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.ciudad.index') }}">
                <span class="card-top">
                    <span class="module-icon">CD</span>
                    <span class="badge">Rutas</span>
                </span>
                <span class="module-title">Ciudades / Cobertura</span>
                <span class="module-description">Ciudades origen y destino para el cálculo y ruteo de envíos de transporte.</span>
                <span class="card-footer">
                    <span class="status">Tabla: ciudad</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.motivo-traslado.index') }}">
                <span class="card-top">
                    <span class="module-icon">MT</span>
                    <span class="badge">SRI / Norma</span>
                </span>
                <span class="module-title">Motivos de Traslado</span>
                <span class="module-description">Conceptos oficiales de traslado de mercadería (Venta, consignación, traslados, etc.).</span>
                <span class="card-footer">
                    <span class="status">Tabla: motivo_traslado</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.formas-pago.index') }}">
                <span class="card-top">
                    <span class="module-icon">FP</span>
                    <span class="badge">Finanzas</span>
                </span>
                <span class="module-title">Formas de Pago</span>
                <span class="module-description">Modalidades de pago disponibles (Efectivo, transferencia, cobro en destino, etc.).</span>
                <span class="card-footer">
                    <span class="status">Tabla: formas_pago</span>
                    <span class="open-link">Abrir módulo →</span>
                </span>
            </a>
        </div>
    </section>

    <!-- 4. SECCIÓN SEGURIDAD Y PERMISOS -->
    <section aria-labelledby="seguridad-title">
        <div class="section-head" id="seguridad-title">
            <div>
                <h2>Seguridad y Control de Acceso</h2>
                <p>Gestión de usuarios y políticas de roles con Spatie Laravel Permission.</p>
            </div>
        </div>

        <div class="module-grid">
            <a class="module-card" href="{{ $routeHref('resources.users.index') }}">
                <span class="card-top">
                    <span class="module-icon">US</span>
                    <span class="badge security">Seguridad</span>
                </span>
                <span class="module-title">Usuarios del Sistema</span>
                <span class="module-description">Control de cuentas, correo electrónico, contraseñas y asignación a personal.</span>
                <span class="card-footer">
                    <span class="status">Tabla: users</span>
                    <span class="open-link">Gestionar usuarios →</span>
                </span>
            </a>

            <a class="module-card" href="{{ $routeHref('resources.roles.index') }}">
                <span class="card-top">
                    <span class="module-icon">RL</span>
                    <span class="badge security">Spatie</span>
                </span>
                <span class="module-title">Roles y Permisos</span>
                <span class="module-description">Definición de roles (Admin, Usuario) y asignación granular de privilegios.</span>
                <span class="card-footer">
                    <span class="status">Spatie Permission</span>
                    <span class="open-link">Configurar roles →</span>
                </span>
            </a>
        </div>
    </section>

    <!-- NOTA INFORMATIVA INFERIOR -->
    <aside class="callout">
        <div>
            <strong>Plataforma SIGRExpress · Transporte Jhetro S.A.</strong>
            <p>Todos los módulos están vinculados a la base de datos relacional y a la arquitectura de roles y permisos.</p>
        </div>
    </aside>
</x-layouts.app>
