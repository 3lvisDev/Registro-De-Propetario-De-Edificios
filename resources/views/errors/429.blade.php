<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demasiadas Solicitudes - Error 429</title>

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
        .retry-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .retry-info strong {
            color: #856404;
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
            <i class="fas fa-hourglass-half"></i>
        </div>
        
        <h1 class="error-code">429</h1>
        <h2 class="error-title">Demasiadas Solicitudes</h2>
        
        <p class="error-message">
            Has realizado demasiadas solicitudes en un corto período de tiempo. Por favor, espera un momento antes de intentar nuevamente.
        </p>

        @php
            $retryAfter = $exception->getHeaders()['Retry-After'] ?? 60;
        @endphp

        <div class="retry-info">
            <i class="fas fa-clock me-2"></i>
            <strong>Tiempo de espera:</strong> {{ $retryAfter }} segundos
        </div>

        <div class="d-flex gap-3 justify-content-center mb-3">
            <button onclick="location.reload()" class="btn btn-primary">
                <i class="fas fa-sync-alt me-2"></i>Reintentar
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="suggestions">
            <h5><i class="fas fa-lightbulb me-2"></i>¿Qué puedes hacer?</h5>
            <ul>
                <li>Espera {{ $retryAfter }} segundos antes de volver a intentar</li>
                <li>Evita hacer clic repetidamente en los botones de envío</li>
                <li>Si necesitas realizar múltiples operaciones, hazlo de forma pausada</li>
                <li>Si el problema persiste, contacta con el soporte técnico</li>
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
            <a href="{{ url('/') }}" class="text-decoration-none" style="color: #ff7b00;">
                <i class="fas fa-home me-1"></i>Ir al Inicio
            </a>
        </div>
        @endauth
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-reload después del tiempo de espera
        setTimeout(function() {
            const retryButton = document.querySelector('.btn-primary');
            if (retryButton) {
                retryButton.innerHTML = '<i class="fas fa-check me-2"></i>Listo para Reintentar';
                retryButton.classList.add('pulse');
            }
        }, {{ $retryAfter * 1000 }});
    </script>
</body>
</html>
