# Tarea 10.4: Implementación de Logging para Rate Limiting

## Resumen

Se ha implementado el logging automático para todos los eventos de rate limiting en el sistema, cumpliendo con el requisito 25.6.

## Implementación

### 1. Exception Handler Modificado

**Archivo**: `app/Exceptions/Handler.php`

Se agregó un handler específico para capturar la excepción `ThrottleRequestsException` que Laravel lanza cuando se excede un límite de rate limiting.

```php
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;

$this->reportable(function (ThrottleRequestsException $e) {
    $request = request();
    
    Log::warning('Rate limit exceeded', [
        'ip' => $request->ip(),
        'user_id' => auth()->id(),
        'user_email' => auth()->user()?->email,
        'route' => $request->path(),
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'timestamp' => now()->toDateTimeString(),
        'user_agent' => $request->userAgent(),
        'limit_exceeded' => $e->getMessage(),
    ]);
});
```

### 2. Información Registrada

Cada vez que se excede un límite de rate limiting, se registra en los logs con la siguiente información:

- **ip**: Dirección IP del cliente que realizó la petición
- **user_id**: ID del usuario autenticado (null si no está autenticado)
- **user_email**: Email del usuario autenticado (null si no está autenticado)
- **route**: Ruta/endpoint que se intentó acceder
- **method**: Método HTTP utilizado (GET, POST, etc.)
- **url**: URL completa de la petición
- **timestamp**: Fecha y hora exacta del evento
- **user_agent**: User agent del navegador/cliente
- **limit_exceeded**: Mensaje de la excepción con detalles del límite

### 3. Rutas Protegidas con Rate Limiting

El logging se aplica automáticamente a todas las rutas con rate limiting:

#### Autenticación (5 intentos por minuto)
- `POST /login`
- `POST /forgot-password`
- `POST /reset-password`

#### Creación de Recursos (10 intentos por minuto)
- `POST /copropietarios` - Creación de copropietarios
- `POST /personas-autorizadas` - Creación de personas autorizadas

### 4. Tests Implementados

**Archivo**: `tests/Feature/RateLimitingLoggingTest.php`

Se crearon 7 tests para verificar el correcto funcionamiento del logging:

1. **test_rate_limit_exceeded_is_logged**: Verifica que se registra el evento cuando se excede el límite
2. **test_rate_limit_log_includes_ip_address**: Verifica que se registra la IP del cliente
3. **test_rate_limit_log_includes_user_info**: Verifica que se registra la información del usuario
4. **test_rate_limit_log_includes_route_info**: Verifica que se registra la ruta y método HTTP
5. **test_rate_limit_log_includes_timestamp**: Verifica que se registra el timestamp
6. **test_login_rate_limit_exceeded_is_logged**: Verifica el logging en intentos de login
7. **test_persona_autorizada_rate_limit_exceeded_is_logged**: Verifica el logging en creación de personas autorizadas

## Ejemplo de Log Generado

Cuando un usuario excede el límite de rate limiting, se genera un log similar a este:

```
[2024-01-15 10:30:45] local.WARNING: Rate limit exceeded
{
    "ip": "192.168.1.100",
    "user_id": 5,
    "user_email": "admin@example.com",
    "route": "copropietarios",
    "method": "POST",
    "url": "http://localhost/copropietarios",
    "timestamp": "2024-01-15 10:30:45",
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
    "limit_exceeded": "Too Many Attempts."
}
```

## Beneficios de Seguridad

1. **Detección de Ataques**: Permite identificar intentos de fuerza bruta o abuso del sistema
2. **Análisis de Patrones**: Facilita el análisis de patrones de uso sospechosos
3. **Auditoría**: Proporciona un registro completo de todos los intentos bloqueados
4. **Respuesta a Incidentes**: Ayuda en la investigación de incidentes de seguridad
5. **Monitoreo**: Permite configurar alertas automáticas basadas en estos logs

## Ubicación de los Logs

Los logs se almacenan en:
- **Desarrollo**: `storage/logs/laravel.log`
- **Producción**: Según configuración en `config/logging.php`

## Configuración Recomendada para Producción

1. **Rotación de Logs**: Configurar rotación diaria o por tamaño
2. **Retención**: Mantener logs por al menos 90 días (Requisito 28.6)
3. **Monitoreo**: Configurar alertas para múltiples eventos de rate limiting desde la misma IP
4. **Análisis**: Implementar herramientas de análisis de logs (ELK Stack, Splunk, etc.)

## Requisitos Cumplidos

✅ **Requisito 25.6**: THE Sistema SHALL registrar en logs los intentos que excedan los límites de tasa

### Criterios de Aceptación Cumplidos:
- ✅ Registra dirección IP del cliente
- ✅ Registra usuario autenticado (si aplica)
- ✅ Registra ruta/endpoint que se intentó acceder
- ✅ Registra timestamp del evento
- ✅ Registra información adicional útil (método HTTP, user agent, mensaje de error)

## Notas Técnicas

### ¿Por qué no usar un Event Listener?

Laravel no emite un evento específico `RateLimitExceeded` por defecto. En su lugar, lanza una excepción `ThrottleRequestsException`. Por esta razón, la implementación se realizó en el Exception Handler, que es el lugar apropiado para capturar y procesar esta excepción.

### Alternativas Consideradas

1. **Middleware Personalizado**: Se podría crear un middleware que envuelva el throttle middleware, pero esto sería más complejo y menos mantenible.

2. **Event Listener Personalizado**: Se podría crear un evento personalizado y emitirlo desde un middleware, pero esto agrega complejidad innecesaria.

3. **Exception Handler** (Implementado): Es la solución más simple y directa, aprovechando el mecanismo nativo de Laravel para manejar excepciones.

## Verificación

Para verificar que el logging funciona correctamente:

1. **Ejecutar los tests**:
   ```bash
   php artisan test --filter=RateLimitingLoggingTest
   ```

2. **Prueba manual**:
   - Intentar hacer login 6 veces seguidas
   - Revisar el archivo `storage/logs/laravel.log`
   - Verificar que aparece el log con toda la información

3. **Monitoreo en producción**:
   - Configurar alertas para el mensaje "Rate limit exceeded"
   - Revisar periódicamente los logs para detectar patrones sospechosos

## Mantenimiento

- Los logs se generan automáticamente, no requiere mantenimiento adicional
- Revisar periódicamente el tamaño de los archivos de log
- Configurar rotación automática de logs en producción
- Considerar implementar un sistema de análisis de logs centralizado

## Conclusión

La implementación del logging para rate limiting proporciona una capa adicional de seguridad y auditoría al sistema, permitiendo detectar y responder a intentos de abuso o ataques de fuerza bruta de manera efectiva.
