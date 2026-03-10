# Tarea 10.2: Configuración de Rate Limiting para Creación de Recursos

## Resumen

Se ha implementado rate limiting en las rutas de creación (store) de Copropietarios y Personas Autorizadas para prevenir abuso del sistema y ataques de spam, limitando las peticiones de creación a **10 intentos por minuto** por usuario autenticado.

## Cambios Realizados

### 1. Modificación de Rutas Web

**Archivo**: `routes/web.php`

Se aplicó el middleware `throttle:10,1` específicamente a las rutas POST (store) de los recursos, manteniendo las demás rutas CRUD sin rate limiting adicional.

#### Rutas de Copropietarios

**Antes**:
```php
Route::resource('copropietarios', CopropietarioController::class);
```

**Después**:
```php
// Aplicar rate limiting específicamente a la ruta de creación (store)
Route::post('copropietarios', [CopropietarioController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('copropietarios.store');

// Resto de rutas del resource sin rate limiting adicional
Route::resource('copropietarios', CopropietarioController::class)->except(['store']);
```

#### Rutas de Personas Autorizadas

**Antes**:
```php
Route::resource('personas-autorizadas', PersonaAutorizadaController::class);
```

**Después**:
```php
// Aplicar rate limiting específicamente a la ruta de creación (store)
Route::post('personas-autorizadas', [PersonaAutorizadaController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('personas-autorizadas.store');

// Resto de rutas del resource sin rate limiting adicional
Route::resource('personas-autorizadas', PersonaAutorizadaController::class)->except(['store']);
```

**Parámetros del middleware**:
- `10`: Máximo de 10 intentos permitidos
- `1`: Ventana de tiempo de 1 minuto

**Nota**: Las rutas GET, PUT/PATCH y DELETE no están afectadas por este rate limiting, permitiendo operaciones de lectura, actualización y eliminación sin restricciones adicionales.

### 2. Creación de Factory para Tests

**Archivo**: `database/factories/CopropietarioFactory.php`

Se creó un factory para el modelo Copropietario para facilitar la creación de datos de prueba en los tests.

**Características**:
- Genera datos aleatorios realistas usando Faker
- Incluye estados `propietario()` y `arrendatario()` para casos específicos
- Soporta todos los campos del modelo incluyendo opcionales
- Genera patentes con formato realista: `[A-Z]{2}[0-9]{4}`

**Ejemplo de uso**:
```php
// Crear un propietario
$propietario = Copropietario::factory()->propietario()->create();

// Crear un arrendatario asociado
$arrendatario = Copropietario::factory()->arrendatario()->create([
    'propietario_id' => $propietario->id,
    'numero_departamento' => $propietario->numero_departamento,
]);
```

### 3. Tests de Verificación

**Archivo**: `tests/Feature/ResourceCreationRateLimitingTest.php`

Se crearon cinco tests completos para verificar el funcionamiento del rate limiting:

#### Test 1: Bloqueo de creación de copropietarios después de 10 intentos
```php
test_copropietario_creation_rate_limiting_blocks_after_ten_attempts()
```
- Realiza 10 creaciones exitosas de copropietarios
- Verifica que el intento 11 retorna error 429 (Too Many Requests)
- Valida que el límite se aplica correctamente

#### Test 2: Bloqueo de creación de personas autorizadas después de 10 intentos
```php
test_persona_autorizada_creation_rate_limiting_blocks_after_ten_attempts()
```
- Crea un copropietario base para asociar personas autorizadas
- Realiza 10 creaciones exitosas de personas autorizadas
- Verifica que el intento 11 retorna error 429

#### Test 3: Rate limiting por usuario autenticado
```php
test_rate_limiting_is_per_authenticated_user()
```
- Verifica que el límite se aplica por usuario, no globalmente
- Usuario 1 alcanza el límite (10 intentos)
- Usuario 2 puede crear sin problemas (contador independiente)

#### Test 4: Otras operaciones CRUD no afectadas
```php
test_other_crud_operations_not_affected_by_creation_rate_limiting()
```
- Alcanza el límite de creación (10 intentos)
- Verifica que GET (listar, ver) funciona normalmente
- Verifica que PUT (actualizar) funciona normalmente
- Confirma que solo POST está limitado

## Comportamiento del Sistema

### Escenario 1: Creación Normal (Dentro del Límite)
1. Usuario autenticado crea copropietarios o personas autorizadas
2. Sistema permite hasta 10 creaciones en 1 minuto
3. Cada creación exitosa incrementa el contador
4. Usuario recibe respuesta normal (302 redirect o 201 created)

### Escenario 2: Límite Alcanzado
1. Usuario intenta crear el recurso número 11 en el mismo minuto
2. Sistema retorna:
   - **Código HTTP**: 429 Too Many Requests
   - **Header**: `Retry-After` indicando cuándo puede reintentar
   - **Mensaje**: "Too Many Attempts. Please try again later."
3. La petición no se procesa

### Escenario 3: Espera y Reintento
- Después de 1 minuto, el contador se resetea automáticamente
- El usuario puede volver a crear recursos (10 intentos nuevos)

### Escenario 4: Operaciones de Lectura/Actualización
- Las operaciones GET, PUT/PATCH y DELETE no están afectadas
- Usuario puede listar, ver, actualizar y eliminar sin límites adicionales
- Solo la creación (POST) está limitada

## Requisitos Cumplidos

✅ **Requisito 25.2**: El sistema limita peticiones de creación de Copropietarios a 10 por minuto por usuario autenticado

✅ **Requisito 25.3**: El sistema limita peticiones de creación de Personas Autorizadas a 10 por minuto por usuario autenticado

## Ventajas de la Implementación

### Seguridad
✅ Previene spam y abuso del sistema
✅ Protege contra ataques automatizados de creación masiva
✅ Reduce carga del servidor por peticiones maliciosas
✅ No afecta operaciones legítimas (10 creaciones/minuto es suficiente)

### Granularidad
✅ Solo afecta operaciones de creación (POST)
✅ Lectura, actualización y eliminación sin restricciones
✅ Límite por usuario autenticado (no por IP)
✅ Contadores independientes por usuario

### Flexibilidad
✅ Fácil de ajustar cambiando parámetros del middleware
✅ No requiere cambios en controladores
✅ Compatible con la arquitectura existente
✅ Reutiliza middleware nativo de Laravel

## Configuración de Laravel

Laravel incluye rate limiting por defecto a través del middleware `throttle`. La configuración se puede personalizar en:

**Archivo**: `app/Http/Kernel.php`

```php
'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
```

### Personalización Avanzada

Si se requiere rate limiting más complejo, se puede definir en `app/Providers/RouteServiceProvider.php`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('resource-creation', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});
```

Y usar en rutas:
```php
Route::post('copropietarios', [CopropietarioController::class, 'store'])
    ->middleware('throttle:resource-creation');
```

## Verificación Manual

Para verificar manualmente el rate limiting:

1. Iniciar sesión en el sistema
2. Acceder al formulario de creación de copropietarios
3. Crear 10 copropietarios rápidamente (dentro de 1 minuto)
4. Intentar crear el copropietario número 11
5. Observar el error 429 Too Many Requests
6. Esperar 1 minuto y verificar que se puede volver a crear

## Comandos de Prueba

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar solo tests de rate limiting de recursos
php artisan test --filter=ResourceCreationRateLimitingTest

# Ejecutar test específico
php artisan test --filter=test_copropietario_creation_rate_limiting_blocks_after_ten_attempts

# Ver rutas con middleware aplicado
php artisan route:list --path=copropietarios
php artisan route:list --path=personas-autorizadas
```

## Logs y Monitoreo

Laravel registra automáticamente los eventos de rate limiting en:
- **Archivo**: `storage/logs/laravel.log`
- **Evento**: `Illuminate\Http\Exceptions\ThrottleRequestsException`

Para implementar logging personalizado (Tarea 10.4), se puede crear un listener para el evento `RateLimitExceeded`.

## Consideraciones de Seguridad

### Ventajas
✅ Previene spam y abuso del sistema
✅ Protege contra creación masiva automatizada
✅ Reduce carga del servidor
✅ No afecta usuarios legítimos (10 creaciones/minuto es generoso)
✅ Límite por usuario autenticado (más preciso que por IP)

### Limitaciones
⚠️ Un atacante con múltiples cuentas puede evadir el límite
⚠️ No protege contra ataques distribuidos (DDoS)
⚠️ El límite es por usuario, no global

### Mejoras Futuras
- Implementar rate limiting global adicional (ej: 100 creaciones/minuto para todo el sistema)
- Agregar CAPTCHA después de 5 creaciones en 1 minuto
- Implementar alertas para administradores cuando se alcanza el límite repetidamente
- Considerar bloqueo temporal de cuenta después de múltiples violaciones

## Diferencias con Tarea 10.1

| Aspecto | Tarea 10.1 (Autenticación) | Tarea 10.2 (Recursos) |
|---------|---------------------------|----------------------|
| **Límite** | 5 intentos/minuto | 10 intentos/minuto |
| **Alcance** | Por IP | Por usuario autenticado |
| **Rutas** | Login, password reset | POST copropietarios, personas-autorizadas |
| **Objetivo** | Prevenir fuerza bruta | Prevenir spam/abuso |
| **Impacto** | Usuarios no autenticados | Usuarios autenticados |

## Integración con Otras Tareas

Esta tarea es parte de la **Fase 3: Protecciones de Seguridad Web**

**Tareas relacionadas**:
- **Tarea 10.1**: ✅ Rate limiting para autenticación (completada)
- **Tarea 10.2**: ✅ Rate limiting para creación de recursos (esta tarea)
- **Tarea 10.3**: ⏳ Personalizar respuestas de rate limiting (pendiente)
- **Tarea 10.4**: ⏳ Agregar logging para rate limiting (pendiente)
- **Tarea 10.5**: ⏳ Tests adicionales de rate limiting (pendiente)

## Archivos Modificados

1. ✅ `routes/web.php` - Aplicación de middleware throttle a rutas store
2. ✅ `database/factories/CopropietarioFactory.php` - Factory para tests (nuevo)
3. ✅ `tests/Feature/ResourceCreationRateLimitingTest.php` - Tests de verificación (nuevo)
4. ✅ `docs/TASK_10.2_RATE_LIMITING_RESOURCES.md` - Esta documentación (nuevo)

## Referencias

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/10.x/routing#rate-limiting)
- [Laravel Throttle Middleware](https://laravel.com/docs/10.x/middleware#throttling-middleware)
- [OWASP: Denial of Service Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Denial_of_Service_Cheat_Sheet.html)
- Requisitos 25.2 y 25.3 del documento de requisitos
- Tarea 10.1: Rate Limiting para Autenticación

## Estado

✅ **COMPLETADO** - Rate limiting configurado para creación de recursos

**Implementación**:
- ✅ Middleware throttle aplicado a POST /copropietarios
- ✅ Middleware throttle aplicado a POST /personas-autorizadas
- ✅ Factory de Copropietario creado
- ✅ Tests de verificación creados
- ✅ Documentación completa

**Fecha**: 2024
**Implementado por**: Kiro AI Assistant

---

## Notas Adicionales

### ¿Por qué 10 intentos y no 5 como en autenticación?

El límite de 10 intentos por minuto para creación de recursos es más generoso que el de autenticación (5 intentos) porque:

1. **Uso legítimo**: Un administrador puede necesitar crear múltiples copropietarios o personas autorizadas en una sesión de trabajo
2. **Menor riesgo**: A diferencia del login, la creación de recursos requiere autenticación previa
3. **Impacto**: Bloquear a un usuario autenticado es más disruptivo que bloquear intentos de login
4. **Balance**: 10 creaciones/minuto es suficiente para uso normal pero previene abuso automatizado

### ¿Por qué solo limitar POST y no otras operaciones?

- **POST (crear)**: Es la operación más costosa y susceptible a abuso (spam, creación masiva)
- **GET (leer)**: Operación de solo lectura, menos costosa, necesaria para navegación normal
- **PUT/PATCH (actualizar)**: Requiere ID existente, menos susceptible a abuso masivo
- **DELETE (eliminar)**: Requiere ID existente, menos susceptible a abuso masivo

Si se detecta abuso en otras operaciones, se puede agregar rate limiting específico.

### ¿Cómo afecta esto a la experiencia del usuario?

**Impacto mínimo en uso normal**:
- 10 creaciones por minuto es más que suficiente para uso legítimo
- La mayoría de usuarios no alcanzará este límite
- Si se alcanza, solo necesita esperar 1 minuto

**Protección efectiva**:
- Scripts automatizados serán bloqueados rápidamente
- Ataques de spam quedan limitados a 10 registros/minuto
- Reduce significativamente el impacto de abuso

