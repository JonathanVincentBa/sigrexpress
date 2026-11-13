<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10233f">
    <title>{{ config('app.name', 'SIGRExpress') }} · Transporte Jhetro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <header class="relative z-10 border-b border-white/10 bg-[#10233f] text-white">
        <nav class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-5 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10" aria-label="Navegación principal">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Transporte Jhetro, inicio">
                <span class="grid size-12 place-items-center rounded-2xl bg-blue-600 text-lg font-black tracking-tight shadow-lg shadow-blue-950/30">JH</span>
                <span>
                    <span class="block text-lg font-extrabold tracking-wide">TRANSPORTE JHETRO</span>
                    <span class="block text-xs font-medium tracking-[0.18em] text-blue-200">LOGÍSTICA · SIGRExpress</span>
                </span>
            </a>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center lg:justify-end">
                <a href="{{ route('tracking.guia') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold text-white transition hover:border-blue-300 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25a2.25 2.25 0 0 1 2.25 2.25v7.5H5.25A2.25 2.25 0 0 1 3 15V7.5Zm13.5 4.5h2.25l2.25 2.25v3h-4.5V12ZM7.5 19.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10.5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                    </svg>
                    Consultar Guía
                </a>

                @guest
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-950/20 transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                        Iniciar Sesión
                    </a>
                @endguest

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-950/20 transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                        Ir al Sistema
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold text-blue-100 transition hover:border-white/40 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300 sm:w-auto">
                            Cerrar Sesión
                        </button>
                    </form>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <section class="relative isolate overflow-hidden bg-[#10233f] text-white">
            <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
                <div class="absolute -right-32 -top-40 size-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-48 left-1/4 size-[34rem] rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[linear-gradient(115deg,transparent_0%,rgba(255,255,255,0.035)_50%,transparent_50%)]"></div>
            </div>

            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-10 lg:py-28">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-blue-300/25 bg-blue-300/10 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-100">
                        <span class="size-2 rounded-full bg-emerald-400"></span>
                        Transporte y logística confiable
                    </p>
                    <h1 class="mt-6 max-w-3xl text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                        Conectamos tus envíos con su próximo destino.
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                        Transporte Jhetro brinda soluciones de carga y distribución con seguimiento de guías y coordinación operativa desde SIGRExpress.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('tracking.guia') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-blue-500 px-6 py-3 text-sm font-extrabold text-white shadow-xl shadow-blue-950/30 transition hover:bg-blue-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            Consultar estado de mi guía
                            <span aria-hidden="true">→</span>
                        </a>
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                Acceso al sistema
                            </a>
                        @endguest
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                Abrir SIGRExpress
                            </a>
                        @endauth
                    </div>
                    <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 border-t border-white/10 pt-6 text-sm text-slate-300">
                        <span class="inline-flex items-center gap-2"><span class="text-blue-300">✓</span> Gestión de guías</span>
                        <span class="inline-flex items-center gap-2"><span class="text-blue-300">✓</span> Coordinación de rutas</span>
                        <span class="inline-flex items-center gap-2"><span class="text-blue-300">✓</span> Seguimiento de envíos</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div class="absolute -inset-4 rounded-[2rem] bg-blue-400/10 blur-2xl" aria-hidden="true"></div>
                    <div class="relative overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/[0.07] p-5 shadow-2xl shadow-slate-950/30 backdrop-blur sm:p-7">
                        <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200">Operación conectada</p>
                                <h2 class="mt-2 text-xl font-bold sm:text-2xl">Cada envío, bajo control</h2>
                            </div>
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-500/20 text-blue-200">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25a2.25 2.25 0 0 1 2.25 2.25v7.5H5.25A2.25 2.25 0 0 1 3 15V7.5Zm13.5 4.5h2.25l2.25 2.25v3h-4.5V12ZM7.5 19.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10.5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                                </svg>
                            </span>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <article class="rounded-2xl border border-white/10 bg-slate-950/20 p-5">
                                <span class="grid size-10 place-items-center rounded-xl bg-blue-400/15 text-blue-200">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6.375 2.25a9.375 9.375 0 1 1-18.75 0 9.375 9.375 0 0 1 18.75 0Z"/>
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-bold">Control de guías</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Emisión y consulta de información operativa de tus envíos.</p>
                            </article>
                            <article class="rounded-2xl border border-white/10 bg-slate-950/20 p-5">
                                <span class="grid size-10 place-items-center rounded-xl bg-cyan-300/15 text-cyan-200">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.5 7.5-11.25a7.5 7.5 0 1 0-15 0C4.5 16.5 12 21 12 21Zm0-8.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                    </svg>
                                </span>
                                <h3 class="mt-4 font-bold">Cobertura coordinada</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Operación conectada con agencias y destinos de Ecuador.</p>
                            </article>
                        </div>

                        <a href="{{ route('tracking.guia') }}" class="mt-4 flex items-center justify-between gap-4 rounded-2xl border border-blue-300/20 bg-blue-500/10 px-5 py-4 transition hover:bg-blue-500/20">
                            <span>
                                <span class="block text-sm font-bold">¿Ya tienes tu número de guía?</span>
                                <span class="mt-1 block text-xs text-blue-100">Consulta el estado de tu envío en línea.</span>
                            </span>
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-400 text-lg font-bold text-white" aria-hidden="true">→</span>
                        </a>
                    </div>
                    <p class="relative mt-4 text-center text-xs text-slate-400">SIGRExpress · Plataforma de gestión de Transporte Jhetro</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-16 lg:px-10">
            <div class="max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-blue-700">Servicios</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Logística clara en cada etapa</h2>
                <p class="mt-4 text-base leading-7 text-slate-600">Una operación organizada ayuda a mantener la información del envío accesible para el equipo y para quienes esperan su carga.</p>
            </div>

            <div class="mt-9 grid gap-5 md:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-extrabold text-blue-700">01 · DOCUMENTACIÓN</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-900">Guías de remisión</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Registro y administración de la información relacionada con cada envío.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-extrabold text-blue-700">02 · DISTRIBUCIÓN</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-900">Coordinación operativa</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Herramientas para organizar sucursales, rutas, vehículos y manifiestos.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-extrabold text-blue-700">03 · VISIBILIDAD</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-900">Consulta de envíos</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Acceso al seguimiento público con los datos requeridos para proteger la consulta.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="bg-[#10233f] text-slate-300">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-8 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10">
            <div>
                <p class="font-bold text-white">Transporte Jhetro</p>
                <p class="mt-1 text-sm">SIGRExpress · Gestión de transporte y logística</p>
            </div>
            <div class="flex flex-col gap-3 text-sm sm:flex-row sm:flex-wrap sm:items-center sm:gap-6">
                <a href="tel:+593995789977" class="transition hover:text-white">Soporte: +593 99 578 9977</a>
                <a href="https://wa.me/593987723116" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">WhatsApp: +593 98 772 3116</a>
                <span class="text-slate-400">© {{ date('Y') }} Transporte Jhetro S.A.</span>
            </div>
        </div>
    </footer>
</body>
</html>
