<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Expirada - Error 419</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        .error-icon {
            font-size: 5rem;
            color: #ff7b00;
            margin-bottom: 1.5rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #2d2f34;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2d2f34;
            margin: 1rem 0;
        }
        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-primary {
            background-color: #ff7b00;
            border-color: #ff7b00;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #e66d00;
            border-color: #e66d00;
        }
        .btn-secondary {
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 500;
        }
        .suggestions {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: left;
        }
        .suggestions h5 {
            color: #2d2f34;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .suggestions ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .suggestions li {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1 class="error-code">419</h1>
        <h2 class="error-title">Sesión Expirada</h2>
        
        <p class="error-message">
            Tu sesión ha expirado por razones de seguridad. Esto puede ocurrir cuando has estado inactivo por mucho tiempo o cuando intentas enviar un formulario que ha estado abierto durante demasiado tiempo.
        </p>

        <div class="d-flex gap-3 justify-content-center mb-3">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>Recargar Página
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="suggestions">
            <h5><i class="fas fa-lightbulb me-2"></i>¿Qué puedes hacer?</h5>
            <ul>
                <li>Haz clic en "Recargar Página" para actualizar el formulario y volver a intentarlo</li>
                <li>Si el problema persiste, cierra sesión y vuelve a iniciar sesión</li>
                <li>Asegúrate de que las cookies estén habilitadas en tu navegador</li>
                <li>Evita mantener formularios abiertos por períodos prolongados</li>
            </ul>
        </div>

        @auth
        <div class="mt-3">
            <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #ff7b00;">
                <i class="fas fa-home me-1"></i>Ir al Dashboard
            </a>
        </div>
        @else
        <div class="mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none" style="color: #ff7b00;">
                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
            </a>
        </div>
        @endauth
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
