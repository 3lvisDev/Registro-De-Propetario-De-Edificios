# Sistema de Auditoría - Guía Rápida

## Uso Básico

### Registrar Operaciones Manualmente

```php
use App\Helpers\AuditLogger;

// Crear
AuditLogger::logCreate(
    ModelClass::class,
    $model->id,
    $model->toArray()
);

// Actualizar
$oldValues = $model->toArray();
$model->update($data);
AuditLogger::logUpdate(
    ModelClass::class,
    $model->id,
    $oldValues,
    $model->fresh()->toArray()
);

// Eliminar
$oldValues = $model->toArray();
$model->delete();
AuditLogger::logDelete(
    ModelClass::class,
    $model->id,
    $oldValues
);

// Intento no autorizado
AuditLogger::logUnauthorized(
    'delete',
    ModelClass::class,
    $model->id
);
```

## Consultas Comunes

### Actividad de un Usuario
```php
$logs = AuditLog::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

### Historial de un Registro
```php
$logs = AuditLog::where('model_type', Copropietario::class)
    ->where('model_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Intentos No Autorizados Recientes
```php
$attempts = AuditLog::where('action', 'unauthorized')
    ->where('created_at', '>=', now()->subDays(7))
    ->with('user')
    ->get();
```

### Cambios en las Últimas 24 Horas
```php
$changes = AuditLog::where('created_at', '>=', now()->subDay())
    ->whereIn('action', ['create', 'update', 'delete'])
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Actividad por IP
```php
$logs = AuditLog::where('ip_address', $ipAddress)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Comandos Artisan

```bash
# Limpiar logs mayores a 90 días
php artisan audit:clean

# Limpiar logs con retención personalizada
php artisan audit:clean --days=180

# Ver tareas programadas
php artisan schedule:list

# Ejecutar scheduler manualmente
php artisan schedule:run
```

## Estructura de Log

```php
[
    'id' => 1,
    'user_id' => 5,
    'action' => 'create|update|delete|unauthorized',
    'model_type' => 'App\\Models\\Copropietario',
    'model_id' => 42,
    'old_values' => [...], // JSON
    'new_values' => [...], // JSON
    'ip_address' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0...',
    'created_at' => '2025-05-13 14:30:00'
]
```

## Acciones Disponibles

- `create`: Creación de registro
- `update`: Actualización de registro
- `delete`: Eliminación de registro
- `unauthorized`: Intento de acceso no autorizado

## Configuración

### Retención de Logs
Editar `app/Console/Kernel.php`:
```php
$schedule->command('audit:clean --days=90')
    ->daily()
    ->at('02:00');
```

### Modelos Auditados Automáticamente
- Copropietario (create, update, delete)
- PersonaAutorizada (create, delete)
- Intentos de autorización fallidos (todos los modelos)

## Mejores Prácticas

1. **Siempre capturar valores antiguos antes de actualizar/eliminar**
   ```php
   $oldValues = $model->toArray();
   $model->update($data);
   AuditLogger::logUpdate(..., $oldValues, ...);
   ```

2. **No registrar datos sensibles**
   - Evitar passwords, tokens, etc.
   - Solo registrar campos en $fillable

3. **Usar try-catch en operaciones críticas**
   - El helper ya maneja errores internamente
   - Pero considerar logging adicional si es necesario

4. **Consultar logs con paginación**
   - Los logs crecen rápidamente
   - Siempre usar `paginate()` en consultas web

5. **Monitorear intentos no autorizados**
   - Revisar regularmente logs de 'unauthorized'
   - Configurar alertas para patrones sospechosos
