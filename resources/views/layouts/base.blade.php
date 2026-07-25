<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Registro de Propietarios')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="d-flex">
        <aside class="bg-dark text-white vh-100 d-flex flex-column justify-content-between p-3 position-sticky top-0"
               style="width: 240px; min-width: 240px;">
            <div>
                <a href="{{ route('dashboard') }}" class="d-block text-center text-white text-decoration-none mb-4">
                    <i class="fas fa-building fa-3x mb-2"></i>
                    <div class="fw-semibold">Registro del Edificio</div>
                </a>

                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-2">
                        <a href="{{ route('dashboard') }}"
                           class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="{{ route('copropietarios.index') }}"
                           class="nav-link text-white {{ request()->routeIs('copropietarios.*') ? 'active' : '' }}">
                            <i class="fas fa-users me-2"></i>Copropietarios
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="{{ route('personas-autorizadas.index') }}"
                           class="nav-link text-white {{ request()->routeIs('personas-autorizadas.*') ? 'active' : '' }}">
                            <i class="fas fa-id-card me-2"></i>Autorizados
                        </a>
                    </li>
                    @if (Auth::user()->isAdmin())
                        <li class="nav-item mb-2">
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield me-2"></i>Usuarios
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="{{ route('admin.backups.index') }}"
                               class="nav-link text-white {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                                <i class="fas fa-shield-halved me-2"></i>Respaldos
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div>
                <div class="small text-secondary text-center mb-2">{{ Auth::user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light w-100" type="submit">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-grow-1 p-4 overflow-auto" style="min-height: 100vh;">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
