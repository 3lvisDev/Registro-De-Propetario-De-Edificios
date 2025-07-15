<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="bg-dark text-white vh-100 d-flex flex-column justify-content-between p-3" style="width: 230px;">
            <div>
                <div class="text-center mb-4">
                    <a href="{{ route('dashboard') }}" class="d-block text-center">
                        <img src="https://remotv.pro/uploads/mz.png" alt="Martín De Zamora"
                             class="img-fluid" style="max-width: 160px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.2));">
                    </a>
                </div>

                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-2">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="{{ route('copropietarios.index') }}" class="nav-link text-white {{ request()->routeIs('copropietarios.*') ? 'active bg-primary' : '' }}">
                            <i class="fas fa-users me-2"></i>Copropietarios
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light w-100" onclick="return confirm('¿Cerrar sesión?')">
                        <i class="fas fa-sign-out-alt me-2"></i>Salir
                    </button>
                </form>
            </div>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-grow-1 p-4">
            @yield('content')
        </main>
    </div>

</body>
</html>

