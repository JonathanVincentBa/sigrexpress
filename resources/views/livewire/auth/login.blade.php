<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10233f">
    <title>Iniciar sesión · {{ config('app.name', 'SIGRExpress') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden min-h-screen overflow-hidden bg-[#10233f] text-white lg:flex lg:flex-col lg:justify-between lg:px-12 lg:py-10 xl:px-20" aria-label="Presentación de SIGRExpress">
            <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -left-36 -top-32 size-[28rem] rounded-full bg-blue-500/15 blur-3xl"></div>
                <div class="absolute -bottom-40 -right-36 size-[32rem] rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[linear-gradient(135deg,transparent_0%,rgba(255,255,255,0.04)_50%,transparent_50%)]"></div>
            </div>

            <a href="{{ route('home') }}" class="relative flex w-fit items-center gap-3" aria-label="Transporte Jhetro, inicio">
                <span class="grid size-12 place-items-center rounded-2xl bg-blue-600 text-lg font-black tracking-tight shadow-lg shadow-blue-950/30">JH</span>
                <span>
                    <span class="block text-base font-extrabold tracking-wide">TRANSPORTE JHETRO</span>
                    <span class="block text-xs font-medium tracking-[0.18em] text-blue-200">LOGÍSTICA · SIGRExpress</span>
                </span>
            </a>

            <div class="relative mx-auto w-full max-w-xl py-12">
                <div class="mb-9 inline-flex items-center gap-2 rounded-full border border-blue-300/25 bg-blue-300/10 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-100">
                    <span class="size-2 rounded-full bg-emerald-400" aria-hidden="true"></span>
                    Operación conectada
                </div>

                <h1 class="max-w-lg text-4xl font-black leading-tight tracking-tight xl:text-5xl">
                    Tu operación logística, en un solo lugar.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-slate-300">
                    SIGRExpress es el sistema integral de transporte y logística de Transporte Jhetro. Gestiona guías, rutas y envíos con claridad y control.
                </p>

                <div class="relative mt-12 overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/[0.07] p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                    <svg class="absolute -right-5 -top-8 size-52 text-blue-300/10" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                        <path d="M20 155C62 155 59 42 110 42s33 105 81 105" stroke="currentColor" stroke-width="2" stroke-dasharray="6 7"/>
                        <circle cx="20" cy="155" r="7" fill="currentColor"/>
                        <circle cx="110" cy="42" r="7" fill="currentColor"/>
                        <circle cx="191" cy="147" r="7" fill="currentColor"/>
                    </svg>
                    <div class="relative flex items-center justify-between gap-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200">Gestión de envíos</p>
                            <p class="mt-2 text-lg font-bold">Seguimiento de cada guía</p>
                            <p class="mt-1 text-sm text-slate-300">Información y operación centralizadas.</p>
                        </div>
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-500/20 text-blue-200">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25a2.25 2.25 0 0 1 2.25 2.25v7.5H5.25A2.25 2.25 0 0 1 3 15V7.5Zm13.5 4.5h2.25l2.25 2.25v3h-4.5V12ZM7.5 19.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10.5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            <p class="relative text-xs text-slate-400">Transporte Jhetro · Sistema Integral de Transporte y Logística</p>
        </section>

        <section class="flex min-h-screen flex-col bg-[#f8fafc] px-5 py-2 sm:px-8 lg:px-12 lg:py-10" aria-label="Acceso al sistema">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-slate-600 transition hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 19.5-7.5-7.5 7.5-7.5M3 12h18"/>
                    </svg>
                    Volver a la portada
                </a>
                <span class="hidden text-xs font-semibold tracking-wide text-slate-400 sm:inline">ACCESO SEGURO</span>
            </div>

            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-3 lg:py-10">
                <a href="{{ route('home') }}" class="mb-4 flex w-fit items-center gap-3 lg:hidden" aria-label="Transporte Jhetro, inicio">
                    <span class="grid size-11 place-items-center rounded-xl bg-[#10233f] text-base font-black text-white">JH</span>
                    <span>
                        <span class="block text-sm font-extrabold tracking-wide text-[#10233f]">TRANSPORTE JHETRO</span>
                        <span class="block text-[10px] font-semibold tracking-[0.16em] text-slate-500">LOGÍSTICA · SIGRExpress</span>
                    </span>
                </a>

                <div class="mb-6">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-blue-700">Bienvenido</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Ingresa al sistema</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Ingresa tus credenciales para continuar con la gestión de tu operación.</p>
                </div>

                @if (session('status'))
                    <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form id="login-form" method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Correo electrónico</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V6.75Zm0 0L12 13.5l8.25-6.75"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                inputmode="email"
                                placeholder="nombre@empresa.com"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                @if ($errors->has('email')) aria-describedby="email-error" @endif
                                class="min-h-12 w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                        </div>
                        @error('email')
                            <p id="email-error" role="alert" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-bold text-slate-700">Contraseña</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-700 transition hover:text-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5V7.875a4.5 4.5 0 1 1 9 0V10.5m-10.125 0h11.25c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.375a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125ZM12 14.25v1.5"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                @if ($errors->has('password')) aria-describedby="password-error" @endif
                                class="min-h-12 w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                        </div>
                        @error('password')
                            <p id="password-error" role="alert" class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                            class="size-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                        >
                        Recordarme en este dispositivo
                    </label>

                    <button
                        id="login-submit"
                        type="submit"
                        data-test="login-button"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2.5 rounded-xl bg-blue-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-900/15 transition hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-wait disabled:opacity-75"
                    >
                        <svg id="login-spinner" class="hidden size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-90" fill="currentColor" d="M12 2a10 10 0 0 1 10 10h-4a6 6 0 0 0-6-6V2Z"/>
                        </svg>
                        <span id="login-submit-label">Ingresar al sistema</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-xs leading-5 text-slate-500">
                    Sistema Integral de Transporte y Logística
                </p>
            </div>

            <p class="hidden text-center text-xs text-slate-400 lg:block">© {{ date('Y') }} Transporte Jhetro · SIGRExpress</p>
        </section>
    </main>

    <script>
        document.getElementById('login-form').addEventListener('submit', () => {
            const button = document.getElementById('login-submit');
            const spinner = document.getElementById('login-spinner');

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            spinner.classList.remove('hidden');
            document.getElementById('login-submit-label').textContent = 'Validando acceso...';
        });
    </script>
</body>
</html>
