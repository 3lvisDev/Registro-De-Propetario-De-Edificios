<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi Panel')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            background-color: #f5f5f5;
        }
        .sidebar {
            width: 220px;
            position: fixed;
            height: 100vh;
            background: #2d2f34;
            padding-top: 1rem;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 1rem;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #1f2125;
        }
        .topbar {
            height: 60px;
            background: #ff7b00;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            margin-left: 220px;
        }
        .content {
            margin-left: 220px;
            padding: 2rem;
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="text-white text-center">Mi Panel</h4>
        <a href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a href="{{ route('copropietarios.index') }}"><i class="fas fa-users me-2"></i> Copropietarios</a>
    </div>

    <div class="topbar">
        <span>@yield('title', 'Panel')</span>
        <span>Bienvenido, {{ Auth::user()->name }}</span>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

