# Tarea 13: Implementación de Auditoría de Operaciones

**Fecha:** 2025-05-13  
**Estado:** ✅ COMPLETADO  
**Requisitos:** 28.1-28.7

---

## Resumen Ejecutivo

Se implementó un sistema completo de auditoría de operaciones críticas que registra todas las acciones de creación, actualización, eliminación e intentos no autorizados en el sistema. El sistema cumple con los requisitos de trazabilidad, retención de 90 días y captura de información relevante (usuario, acción, timestamp, IP, datos).

---

## Subtareas Implementadas

### ✅ 13.1 Crear tabla de auditoría

**Archivo:** `database/migrations/2025_05_13_000000_create_audit_logs_table.php`

**Campos implementados:**
- `id`: Identificador único
- `user_id`: Usuario que realizó la acción (nullable, FK a users)
- `action`: Tipo de acción (create, update, delete, unauthorized)
- `model_type`: Clase del modelo afectado
- `model_id`: ID del modelo afectado (nullable)
- `old_values`: Valores anteriores en formato JSON (nullable)
- `new_values`: Valores nuevos en formato JSON (nullable)
- `ip_address`: Dirección IP del usuario (varchar 45 para IPv6)
- `user_agent`: User agent del navegador
- `created_at`: Timestamp de la acción

**Índices creados:**
- `user_id`: Para consultas por usuario
- `model_type, model_id`: Para consultas por modelo
- `action`: Para filtrar por tipo de acción
- `created_at`: Para consultas temporales y limpieza

**Restricciones:**
- Foreign key a `users.id` con `ON DELETE SET NULL`

### ✅ 13.2 Crear modelo AuditLog

**Archivo:** `app/Models/AuditLog.php`

**Características:**
- Modelo Eloquent con `$fillable` definido
- Casts automáticos para JSON (`old_values`, `new_values`)
- Cast de `created_at` a datetime
- Relación `belongsTo` con User
- Relación polimórfica `morphTo` para el modelo auditado
- Sin timestamps automáticos (solo `created_at` manual)

### ✅ 13.3 Crear helper para auditoría

**Archivo:** `app/Helpers/AuditLogger.php`

**Métodos implementados:**

1. **`logCreate(string $modelType, ?int $modelId, array $newValues)`**
   - Registra operaciones de creación
   - Captura valores nuevos del modelo

2. **`logUpdate(string $modelType, ?int $modelId, array $oldValues, array $newValues)`**
   - Registra operaciones de actualización
   - Captura valores antes y después del cambio

3. **`logDelete(string $modelType, ?int $modelId, array $oldValues)`**
   - Registra operaciones de eliminación
   - Captura valores antes de la eliminación

4. **`logUnauthorized(string $action, ?string $modelType, ?int $modelId)`**
   - Registra intentos de acceso no autorizados
   - Captura la acción intentada

**Información capturada automáticamente:**
- `user_id`: Obtenido de `Auth::id()`
- `ip_address`: Obtenido de `request()->ip()`
- `user_agent`: Obtenido de `request()->userAgent()`
- `created_at`: Timestamp actual

**Manejo de errores:**
- Try-catch para evitar que fallos en auditoría interrumpan operaciones
- Logging a Laravel log si falla la auditoría

### ✅ 13.4 Implementar auditoría en CopropietarioController

**Archivo:** `app/Http/Controllers/CopropietarioController.php`

**Operaciones auditadas:**

1. **`store()` - Creación**
   - Registra cada copropietario creado
   - Captura todos los datos del nuevo registro
   - Requisito 28.1

2. **`update()` - Actualización**
   - Captura valores antiguos antes de actualizar
   - Registra valores nuevos después de actualizar
   - Permite comparación de cambios
   - Requisito 28.2

3. **`destroy()` - Eliminación**
   - Captura valores antes de eliminar
   - Registra la eliminación con ID del registro
   - Requisito 28.3

### ✅ 13.5 Implementar auditoría en PersonaAutorizadaController

**Archivo:** `app/Http/Controllers/PersonaAutorizadaController.php`

**Operaciones auditadas:**

1. **`store()` - Creación**
   - Registra persona autorizada creada
   - Captura todos los datos del nuevo registro
   - Requisito 28.4

2. **`destroy()` - Eliminación**
   - Captura valores antes de eliminar
   - Registra la eliminación con ID del registro
   - Requisito 28.4

### ✅ 13.6 Implementar auditoría de intentos no autorizados

**Archivos:**
- `app/Listeners/LogUnauthorizedAccess.php`
- `app/Providers/EventServiceProvider.php`

**Implementación:**
- Listener para evento `GateEvaluated`
- Se activa cuando `$event->result === false`
- Extrae información del modelo si está disponible
- Registra en audit_logs y en Laravel log
- Requisito 28.5

**Información capturada:**
- Usuario que intentó la acción
- Ability/permiso que falló
- Tipo y ID del modelo (si aplica)
- IP y URL completa
- Timestamp

### ✅ 13.7 Configurar retención de logs

**Archivos:**
- `app/Console/Commands/CleanOldAuditLogs.php`
- `app/Console/Kernel.php`

**Comando Artisan:**
```bash
php artisan audit:clean --days=90
```

**Características:**
- Opción `--days` configurable (default: 90)
- Validación de parámetros
- Mensajes informativos de progreso
- Manejo de errores con try-catch
- Retorna códigos de éxito/fallo

**Programación automática:**
- Ejecuta diariamente a las 2:00 AM
- Retención de 90 días (configurable)
- Callbacks de éxito/fallo con logging
- Requisito 28.6

---

## Requisitos Cumplidos

### ✅ Requisito 28.1: Auditoría de Creación
- Registra creación de copropietarios
- Incluye: usuario, timestamp, datos relevantes, IP

### ✅ Requisito 28.2: Auditoría de Actualización
- Registra actualizaciones de copropietarios
- Incluye: cambios realizados (old_values vs new_values)

### ✅ Requisito 28.3: Auditoría de Eliminación de Copropietarios
- Registra eliminación con usuario y timestamp
- Captura datos antes de eliminar

### ✅ Requisito 28.4: Auditoría de Eliminación de Personas Autorizadas
- Registra eliminación con usuario y timestamp
- Captura datos antes de eliminar

### ✅ Requisito 28.5: Auditoría de Intentos No Autorizados
- Registra intentos fallidos de autorización
- Incluye: usuario, acción intentada, detalles del modelo

### ✅ Requisito 28.6: Retención de Logs
- Almacenamiento seguro en base de datos
- Retención mínima de 90 días
- Comando automático de limpieza

### ✅ Requisito 28.7: Información en Logs
- Usuario (user_id)
- Acción (action)
- Timestamp (created_at)
- Dirección IP (ip_address)
- Datos relevantes (old_values, new_values)
- User agent para contexto adicional

---

## Estructura de Datos de Auditoría

### Ejemplo de Log de Creación
```json
{
  "id": 1,
  "user_id": 5,
  "action": "create",
  "model_type": "App\\Models\\Copropietario",
  "model_id": 42,
  "old_values": null,
  "new_values": {
    "nombre_completo": "Juan Pérez",
    "tipo": "propietario",
    "numero_departamento": "101",
    "telefono": "+56912345678",
    "correo": "juan@example.com"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-05-13 14:30:00"
}
```

### Ejemplo de Log de Actualización
```json
{
  "id": 2,
  "user_id": 5,
  "action": "update",
  "model_type": "App\\Models\\Copropietario",
  "model_id": 42,
  "old_values": {
    "telefono": "+56912345678",
    "correo": "juan@example.com"
  },
  "new_values": {
    "telefono": "+56987654321",
    "correo": "juan.perez@example.com"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-05-13 15:45:00"
}
```

### Ejemplo de Log de Eliminación
```json
{
  "id": 3,
  "user_id": 5,
  "action": "delete",
  "model_type": "App\\Models\\PersonaAutorizada",
  "model_id": 15,
  "old_values": {
    "nombre_completo": "María González",
    "rut_pasaporte": "12345678-9",
    "departamento": "101"
  },
  "new_values": null,
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-05-13 16:20:00"
}
```

### Ejemplo de Log de Intento No Autorizado
```json
{
  "id": 4,
  "user_id": 8,
  "action": "unauthorized",
  "model_type": "App\\Models\\Copropietario",
  "model_id": 42,
  "old_values": null,
  "new_values": {
    "attempted_action": "delete"
  },
  "ip_address": "192.168.1.150",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-05-13 17:10:00"
}
```

---

## Uso del Sistema de Auditoría

### Consultar Logs de un Usuario
```php
$userLogs = AuditLog::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

### Consultar Logs de un Modelo Específico
```php
$modelLogs = AuditLog::where('model_type', Copropietario::class)
    ->where('model_id', $copropietarioId)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Consultar Intentos No Autorizados
```php
$unauthorizedAttempts = AuditLog::where('action', 'unauthorized')
    ->where('created_at', '>=', now()->subDays(7))
    ->with('user')
    ->get();
```

### Consultar Cambios en un Período
```php
$recentChanges = AuditLog::whereBetween('created_at', [$startDate, $endDate])
    ->whereIn('action', ['create', 'update', 'delete'])
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## Comandos de Mantenimiento

### Limpiar Logs Manualmente
```bash
# Limpiar logs mayores a 90 días (default)
php artisan audit:clean

# Limpiar logs mayores a 180 días
php artisan audit:clean --days=180

# Limpiar logs mayores a 30 días
php artisan audit:clean --days=30
```

### Verificar Scheduler
```bash
# Listar tareas programadas
php artisan schedule:list

# Ejecutar scheduler manualmente (para testing)
php artisan schedule:run
```

### Ejecutar Migración
```bash
php artisan migrate
```

---

## Consideraciones de Seguridad

### ✅ Protección de Datos Sensibles
- Passwords y tokens NO se registran en auditoría
- Solo se capturan campos definidos en $fillable
- User agent e IP para contexto de seguridad

### ✅ Integridad de Logs
- Tabla separada para auditoría
- No se permite actualización de logs (solo INSERT)
- Foreign key con SET NULL para preservar logs si se elimina usuario

### ✅ Rendimiento
- Índices optimizados para consultas comunes
- Limpieza automática de logs antiguos
- Try-catch para no interrumpir operaciones críticas

### ✅ Privacidad
- Retención limitada a 90 días (configurable)
- Logs accesibles solo por administradores
- Cumplimiento con políticas de retención de datos

---

## Próximos Pasos Opcionales

### Mejoras Futuras (No Requeridas para MVP)

1. **Dashboard de Auditoría**
   - Vista web para consultar logs
   - Filtros por usuario, acción, fecha
   - Gráficos de actividad

2. **Alertas de Seguridad**
   - Notificaciones de intentos no autorizados repetidos
   - Alertas de eliminaciones masivas
   - Detección de patrones sospechosos

3. **Exportación de Logs**
   - Exportar a CSV/Excel
   - Reportes programados
   - Integración con SIEM

4. **Auditoría Extendida**
   - Registrar cambios en configuración
   - Auditar accesos a datos sensibles
   - Tracking de sesiones de usuario

---

## Testing

### Tests Opcionales (Subtarea 13.8)

Si se desea implementar tests para auditoría:

```php
// tests/Feature/AuditLoggingTest.php

public function test_copropietario_creation_is_logged()
{
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post('/copropietarios', [
        'numero_departamento' => '101',
        'copropietarios' => [[
            'nombre_completo' => 'Test User',
            'tipo' => 'propietario',
        ]]
    ]);
    
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'create',
        'model_type' => Copropietario::class,
    ]);
}

public function test_copropietario_update_logs_changes()
{
    $user = User::factory()->create();
    $copropietario = Copropietario::factory()->create();
    $this->actingAs($user);
    
    $response = $this->put("/copropietarios/{$copropietario->id}", [
        'nombre_completo' => 'Updated Name',
        // ... otros campos
    ]);
    
    $log = AuditLog::where('action', 'update')
        ->where('model_id', $copropietario->id)
        ->first();
    
    $this->assertNotNull($log);
    $this->assertNotNull($log->old_values);
    $this->assertNotNull($log->new_values);
}

public function test_unauthorized_access_is_logged()
{
    $user = User::factory()->create();
    $copropietario = Copropietario::factory()->create();
    $this->actingAs($user);
    
    // Simular fallo de autorización
    Gate::define('delete-copropietario', fn() => false);
    
    $response = $this->delete("/copropietarios/{$copropietario->id}");
    
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'unauthorized',
    ]);
}
```

---

## Verificación de Implementación

### ✅ Checklist de Completitud

- [x] Migración de audit_logs creada con todos los campos
- [x] Modelo AuditLog con $fillable y relaciones
- [x] Helper AuditLogger con métodos: logCreate, logUpdate, logDelete, logUnauthorized
- [x] Auditoría en CopropietarioController (store, update, destroy)
- [x] Auditoría en PersonaAutorizadaController (store, destroy)
- [x] Listener para intentos no autorizados
- [x] Comando artisan para limpieza de logs
- [x] Scheduler configurado con retención de 90 días
- [x] Documentación completa

### ✅ Requisitos Validados

- [x] 28.1: Creación de copropietarios registrada
- [x] 28.2: Actualización de copropietarios registrada con cambios
- [x] 28.3: Eliminación de copropietarios registrada
- [x] 28.4: Eliminación de personas autorizadas registrada
- [x] 28.5: Intentos no autorizados registrados
- [x] 28.6: Retención de 90 días configurada
- [x] 28.7: Información completa capturada (usuario, acción, timestamp, IP, datos)

---

## Conclusión

El sistema de auditoría está completamente implementado y cumple con todos los requisitos especificados. Proporciona trazabilidad completa de operaciones críticas, captura información relevante para análisis de seguridad, y mantiene logs con retención adecuada.

**Estado Final:** ✅ TAREA 13 COMPLETADA

**Archivos Creados/Modificados:**
- ✅ `database/migrations/2025_05_13_000000_create_audit_logs_table.php`
- ✅ `app/Models/AuditLog.php`
- ✅ `app/Helpers/AuditLogger.php`
- ✅ `app/Http/Controllers/CopropietarioController.php` (modificado)
- ✅ `app/Http/Controllers/PersonaAutorizadaController.php` (modificado)
- ✅ `app/Listeners/LogUnauthorizedAccess.php`
- ✅ `app/Providers/EventServiceProvider.php` (modificado)
- ✅ `app/Console/Commands/CleanOldAuditLogs.php`
- ✅ `app/Console/Kernel.php` (modificado)
- ✅ `docs/TASK_13_AUDIT_IMPLEMENTATION.md` (este documento)
