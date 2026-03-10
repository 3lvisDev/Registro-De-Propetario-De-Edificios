# Detección de Inyección de Comandos

## Descripción

Este sistema implementa la detección y registro de intentos de inyección de comandos externos en el sistema de gestión de copropietarios. Cumple con el **Requisito 19.4**: "THE Sistema SHALL registrar en logs cualquier intento de ejecución de comandos externos".

## Componentes

### 1. Middleware: `DetectCommandInjection`

**Ubicación**: `app/Http/Middleware/DetectCommandInjection.php`

El middleware intercepta todas las peticiones HTTP y analiza los datos de entrada en busca de patrones sospechosos de inyección de comandos.

**Características**:
- Se ejecuta automáticamente en todas las rutas web
- Analiza recursivamente arrays anidados
- No bloquea las peticiones, solo registra intentos sospechosos
- Registra contexto completo (IP, usuario, URL, método, etc.)

**Registro**: El middleware está registrado en `app/Http/Kernel.php` en el grupo 'web' y también como alias 'detect.command.injection'.

### 2. Helper: `CommandInjectionDetector`

**Ubicación**: `app/Helpers/CommandInjectionDetector.php`

Clase helper que puede ser utilizada independientemente del middleware para validar entradas específicas en cualquier parte de la aplicación.

**Métodos principales**:

```php
// Verificar si un valor contiene patrones sospechosos
CommandInjectionDetector::containsSuspiciousPatterns(string $value): bool

// Obtener los patrones que coinciden
CommandInjectionDetector::getMatchedPatterns(string $value): array

// Validar y registrar en logs
CommandInjectionDetector::validateAndLog(string $field, string $value, array $context = []): bool

// Sanitizar un valor (NO recomendado como única medida)
CommandInjectionDetector::sanitize(string $value): string

// Agregar patrones personalizados
CommandInjectionDetector::addCustomPattern(string $pattern): void
CommandInjectionDetector::addSuspiciousCommand(string $pattern): void
```

## Patrones Detectados

### Caracteres de Control de Shell
- `;` - Separador de comandos
- `&` - Ejecución en background
- `|` - Pipe
- `` ` `` - Backticks para ejecución
- `$` - Variables y sustitución de comandos

### Sustitución de Comandos
- `$(comando)` - Sustitución de comandos
- `` `comando` `` - Backticks

### Operadores Lógicos
- `||` - OR lógico
- `&&` - AND lógico

### Redirección
- `> /ruta` - Redirección de salida
- `< /ruta` - Lectura de archivos

### Funciones Peligrosas de PHP
- `exec()`
- `shell_exec()`
- `system()`
- `passthru()`
- `popen()`
- `proc_open()`
- `pcntl_exec()`

### Comandos Shell Comunes
- `cat`, `ls`, `pwd`, `whoami`, `id`, `uname`
- `wget`, `curl`
- `nc`, `netcat`
- `bash`, `sh`
- `chmod`, `chown`
- `rm`, `mv`, `cp`

### Caracteres Escapados
- `\xHH` - Caracteres hexadecimales
- `\OOO` - Caracteres octales

## Uso

### Uso Automático (Middleware)

El middleware se ejecuta automáticamente en todas las rutas web. No requiere configuración adicional.

### Uso Manual (Helper)

```php
use App\Helpers\CommandInjectionDetector;

// En un controlador o servicio
public function store(Request $request)
{
    $nombre = $request->input('nombre_completo');
    
    // Verificar si contiene patrones sospechosos
    if (CommandInjectionDetector::containsSuspiciousPatterns($nombre)) {
        // Manejar el caso sospechoso
        return back()->withErrors(['nombre_completo' => 'Entrada no válida detectada']);
    }
    
    // O validar y registrar automáticamente
    CommandInjectionDetector::validateAndLog('nombre_completo', $nombre, [
        'user_id' => auth()->id(),
        'action' => 'store_copropietario'
    ]);
    
    // Continuar con el procesamiento normal
}
```

### Agregar Patrones Personalizados

```php
// En un Service Provider o al inicio de la aplicación
CommandInjectionDetector::addCustomPattern('/\bsudo\b/i');
CommandInjectionDetector::addSuspiciousCommand('/\b(kill|pkill)\b/i');
```

## Formato de Logs

Cuando se detecta un intento sospechoso, se registra en `storage/logs/laravel.log` con el siguiente formato:

```
[2024-01-15 10:30:45] local.WARNING: Intento sospechoso de inyección de comandos detectado
{
    "timestamp": "2024-01-15T10:30:45+00:00",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "user_id": 5,
    "user_email": "admin@example.com",
    "url": "http://localhost/copropietarios",
    "method": "POST",
    "field": "copropietarios.0.nombre_completo",
    "value": "Test; rm -rf /",
    "matched_patterns": [
        "/[;&|`$]/",
        "/\\b(rm)\\b/i"
    ],
    "all_input": {
        "numero_departamento": "101",
        "copropietarios": [...]
    }
}
```

## Monitoreo de Logs

### Ver logs en tiempo real

```bash
# Linux/Mac
tail -f storage/logs/laravel.log | grep "inyección de comandos"

# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait | Select-String "inyección de comandos"
```

### Buscar intentos sospechosos

```bash
# Linux/Mac
grep "inyección de comandos" storage/logs/laravel.log

# Windows PowerShell
Select-String -Path storage/logs/laravel.log -Pattern "inyección de comandos"
```

## Tests

**Ubicación**: `tests/Feature/DetectCommandInjectionMiddlewareTest.php`

El sistema incluye tests completos que verifican:
- Detección de caracteres de control de shell
- Detección de sustitución de comandos
- Detección de backticks
- Detección de pipes y operadores lógicos
- Detección de comandos shell comunes
- No detección de entradas normales
- Detección en arrays anidados
- Registro completo de contexto

### Ejecutar tests

```bash
php artisan test --filter DetectCommandInjectionMiddlewareTest
```

## Consideraciones de Seguridad

### ⚠️ Importante

1. **Este sistema NO bloquea las peticiones**, solo las registra. Es una medida de detección, no de prevención.

2. **No confiar solo en sanitización**: El método `sanitize()` del helper NO debe usarse como única medida de seguridad. Es mejor rechazar entradas sospechosas.

3. **Validación adicional**: Siempre usar validación de Laravel y reglas de negocio apropiadas además de este sistema.

4. **Falsos positivos**: Algunos patrones legítimos pueden ser detectados (ej: correos con `&` en el nombre). Revisar logs regularmente.

### Mejores Prácticas

1. **Revisar logs regularmente**: Establecer un proceso para revisar logs de seguridad.

2. **Alertas automáticas**: Considerar configurar alertas para múltiples intentos desde la misma IP.

3. **Bloqueo opcional**: Si se detectan múltiples intentos, considerar implementar bloqueo temporal de IP.

4. **Actualizar patrones**: Mantener los patrones actualizados con nuevas técnicas de ataque.

## Integración con Requisito 19.4

Este sistema cumple completamente con el **Requisito 19.4**:

> "THE Sistema SHALL registrar en logs cualquier intento de ejecución de comandos externos"

**Cumplimiento**:
- ✅ Detecta patrones de inyección de comandos
- ✅ Registra intentos sospechosos en logs
- ✅ Incluye contexto completo (usuario, IP, timestamp, etc.)
- ✅ Funciona automáticamente en todas las peticiones web
- ✅ Puede ser usado manualmente en casos específicos

## Mantenimiento

### Agregar nuevos patrones

Si se descubren nuevas técnicas de inyección, agregar los patrones en:
- `app/Helpers/CommandInjectionDetector.php` (arrays estáticos)
- O dinámicamente usando `addCustomPattern()` y `addSuspiciousCommand()`

### Desactivar temporalmente

Para desactivar el middleware temporalmente:

```php
// En app/Http/Kernel.php, comentar la línea:
// \App\Http\Middleware\DetectCommandInjection::class,
```

### Configurar nivel de log

Para cambiar el nivel de log (por defecto: WARNING):

```php
// En DetectCommandInjection.php, cambiar:
Log::warning(...) // a Log::error(...) o Log::info(...)
```

## Soporte

Para preguntas o problemas relacionados con este sistema, contactar al equipo de desarrollo o revisar la documentación de seguridad del proyecto.
