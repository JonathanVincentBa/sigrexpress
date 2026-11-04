<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Transportes Jhetro') }} - Sistema de Guías de Remisión</title>
    
    <!-- Fuentes Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#0b1a3e',   // Azul oscuro de Jhetro
                            primary: '#174ea6',// Azul corporativo
                            light: '#2563eb',  // Azul de acento
                            bg: '#f0f4f8'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-900 min-h-screen flex flex-col justify-between">

    <div class="min-h-screen grid lg:grid-cols-12 w-full">
        
        <!-- SECCIÓN IZQUIERDA: HERO / IMAGEN Y MARCA (7 Columnas en escritorio) -->
        <div class="lg:col-span-7 relative bg-brand-dark flex flex-col justify-between p-8 lg:p-12 overflow-hidden text-white min-h-[400px] lg:min-h-screen">
            
            <!-- Imagen de Fondo con Overlay -->
            <div class="absolute inset-0 z-0 opacity-25 bg-cover bg-center" 
                 style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=1600&auto=format&fit=crop');">
            </div>
            
            <!-- Degradado sobre la imagen para legibilidad -->
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/80 to-transparent z-0"></div>

            <!-- Cabecera / Marca -->
            <div class="relative z-10 flex items-center gap-4">
                <!-- Logotipo Ilustrado Jhetro -->
                <div class="bg-white p-3 rounded-xl shadow-lg border border-white/20">
                    <svg class="w-10 h-10 text-brand-dark" viewBox="0 0 100 100" fill="currentColor">
                        <path d="M 10 35 L 55 35 L 55 20 L 90 30 L 90 65 L 80 65 A 10 10 0 0 1 60 65 L 40 65 A 10 10 0 0 1 20 65 L 10 65 Z" fill="#0b1a3e"/>
                        <rect x="18" y="42" width="28" height="15" fill="#ffffff" rx="2"/>
                        <circle cx="30" cy="65" r="7" fill="#ffffff"/>
                        <circle cx="70" cy="65" r="7" fill="#ffffff"/>
                        <text x="65" y="52" font-family="Arial, sans-serif" font-weight="900" font-size="22" fill="#ffffff">H</text>
                        <path d="M 5 78 Q 50 72 95 85" stroke="#0b1a3e" stroke-width="4" fill="none"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black tracking-wider uppercase leading-none">JHETRO S.A.</h2>
                    <p class="text-xs text-blue-300 font-semibold tracking-widest uppercase mt-1">Transporte de Carga Pesada</p>
                </div>
            </div>

            <!-- Contenido Principal Hero -->
            <div class="relative z-10 my-auto py-12">
                <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-semibold rounded-full uppercase tracking-wider mb-4">
                    Plataforma Operativa Interna
                </span>
                <h1 class="text-3xl lg:text-5xl font-extrabold leading-tight text-white mb-4">
                    Gestión de Guías de Remisión y Logística
                </h1>
                <p class="text-slate-300 text-base lg:text-lg max-w-xl leading-relaxed mb-8">
                    Sistema integral para el control de rutas, emisión de documentos electrónicos y seguimiento de flota a nivel nacional.
                </p>

                <!-- Tarjetas de Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-white/10">
                    <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10">
                        <p class="text-2xl lg:text-3xl font-extrabold text-blue-400">+350.000</p>
                        <p class="text-xs text-slate-300 font-medium">Paquetes entregados</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10">
                        <p class="text-2xl lg:text-3xl font-extrabold text-blue-400">2.9 - 10 Ton</p>
                        <p class="text-xs text-slate-300 font-medium">Capacidad de flota</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-md p-4 rounded-xl border border-white/10 col-span-2 md:col-span-1">
                        <p class="text-2xl lg:text-3xl font-extrabold text-blue-400">+7 Años</p>
                        <p class="text-xs text-slate-300 font-medium">De trayectoria</p>
                    </div>
                </div>
            </div>

            <!-- Pie de la Columna Izquierda -->
            <div class="relative z-10 text-xs text-slate-400 border-t border-white/10 pt-4 flex flex-wrap justify-between gap-2">
                <p>📍 Quito, Ibarra, Latacunga, Ambato, Riobamba, Cuenca y más.</p>
                <p>© {{ date('Y') }} Transporte Jhetro S.A.</p>
            </div>
        </div>

        <!-- SECCIÓN DERECHA: FORMULARIO DE ACCESO (5 Columnas en escritorio) -->
        <div class="lg:col-span-5 bg-white flex flex-col justify-between p-8 lg:p-12">
            
            <div class="w-full max-w-md mx-auto my-auto">
                
                @auth
                    <!-- Vista para usuario ya autenticado -->
                    <div class="p-6 bg-blue-50 border border-blue-200 rounded-2xl text-center shadow-sm">
                        <div class="w-14 h-14 bg-brand-primary/10 text-brand-primary rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Sesión Activa</p>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">{{ auth()->user()->name }}</h4>
                        <p class="text-slate-500 text-sm mb-6">{{ auth()->user()->email }}</p>

                        <a href="{{ route('dashboard') }}" 
                           class="w-full bg-brand-primary hover:bg-brand-dark text-white font-bold py-3.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 group">
                            <span>Ir al Panel de Control</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                @else
                    <!-- Encabezado del Formulario -->
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-slate-900">Iniciar Sesión</h3>
                        <p class="text-slate-500 text-sm mt-1">Ingresa tus credenciales para acceder al sistema de guías.</p>
                    </div>

                    <!-- Alerta si hay un mensaje de estado -->
                    @if (session('status'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Formulario de Login -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                                Correo electrónico
                            </label>
                            <div class="relative">
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all text-slate-800 text-sm outline-none pl-10"
                                       placeholder="usuario@transportejhetro.com">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                            </div>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-sm font-semibold text-slate-700">
                                    Contraseña
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-primary hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" type="password" name="password" required
                                       class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all text-slate-800 text-sm outline-none pl-10"
                                       placeholder="••••••••">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Recordarme -->
                        <div class="flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" 
                                   class="h-4 w-4 text-brand-primary focus:ring-brand-primary border-slate-300 rounded cursor-pointer">
                            <label for="remember_me" class="ml-2 block text-sm text-slate-600 cursor-pointer">
                                Recordar mi sesión
                            </label>
                        </div>

                        <!-- Botón de Ingreso -->
                        <button type="submit" 
                                class="w-full bg-brand-primary hover:bg-brand-dark text-white font-bold py-3.5 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 group">
                            <span>Ingresar al Sistema</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>

                    <!-- Nota Informativa de Seguridad -->
                    <div class="mt-8 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p>Acceso restringido únicamente para personal y choferes autorizados de Transporte Jhetro S.A.</p>
                    </div>
                @endauth

                <a href="{{ route('tracking.guia') }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-brand-primary transition hover:border-blue-300 hover:bg-blue-100">
                    <span>Consultar estado de mi guía</span>
                    <span aria-hidden="true">→</span>
                </a>

            </div>

            <!-- Pie de página con contacto directo (Soporte Técnico y WhatsApp) -->
            <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-500 space-y-2">
                <p>¿Problemas de acceso o soporte del sistema?</p>
                <div class="flex justify-center items-center gap-4 text-slate-700 font-medium flex-wrap">
                    <!-- Soporte Técnico / Creador -->
                    <a href="tel:+593995789977" class="hover:text-brand-primary flex items-center gap-1">
                        <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Soporte Técnico: +593 99 578 9977
                    </a>
                    <span>•</span>
                    <!-- WhatsApp Comercial -->
                    <a href="https://wa.me/593987723116" target="_blank" class="hover:text-green-600 flex items-center gap-1">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        WhatsApp: +593 98 772 3116
                    </a>
                </div>
            </div>

        </div>

    </div>

    @include('sweetalert::alert')
</body>
</html>
