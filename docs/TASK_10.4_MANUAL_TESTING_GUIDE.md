# Guía de Pruebas Manuales - Logging de Rate Limiting

## Objetivo

Esta guía proporciona instrucciones paso a paso para probar manualmente el logging de rate limiting y verificar que funciona correctamente.

---

## Prerrequisitos

1. Servidor Laravel en ejecución
2. Acceso a los archivos de log (`storage/logs/laravel.log`)
3. Usuario de prueba creado en el sistema
4. Herramienta para hacer peticiones HTTP (navegador, Postman, curl, etc.)

---

## Prueba 1: Rate Limiting en Login (No Autenticado)

### Objetivo
Verificar que se registra en logs cuando un usuario no autenticado excede el límite de intentos de login.

### Pasos

1. **Limpiar el log actual** (opcional, para facilitar la verificación):
   ```bash
   # En Linux/Mac
   > storage/logs/laravel.log
   
   # En Windows PowerShell
   Clear-Content storage/logs/laravel.log
   ```

2. **Abrir el navegador en modo incógnito** (para evitar cookies de sesión)

3. **Navegar a la página de login**:
   ```
   http://localhost/login
   ```

4. **Intentar hacer login 6 veces seguidas** con credenciales incorrectas:
   - Email: `test@example.com`
   - Password: `wrongpassword`
   - Hacer clic en "Login" 6 veces

5. **Verificar la respuesta**:
   - Las primeras 5 peticiones deben mostrar error de credenciales inválidas
   - La 6ta petición debe mostrar error 429 "Too Many Attempts"

6. **Revisar el log**:
   ```bash
   tail -n 50 storage/logs/laravel.log
   ```

### Resultado Esperado

Debe aparecer un log similar a:

```
[2024-01-15 10:30:45] local.WARNING: Rate limit exceeded
{
    "ip": "127.0.0.1",
    "user_id": null,
    "user_email": null,
    "route": "login",
    "method": "POST",
    "url": "http://localhost/login",
    "timestamp": "2024-01-15 10:30:45",
    "user_agent": "Mozilla/5.0 ...",
    "limit_exceeded": "Too Many Attempts."
}
```

**Verificar**:
- ✅ `user_id` es `null` (no autenticado)
- ✅ `user_email` es `null` (no autenticado)
- ✅ `ip` contiene la IP del cliente
- ✅ `route` es "login"
- ✅ `method` es "POST"

---

## Prueba 2: Rate Limiting en Creación de Copropietarios (Autenticado)

### Objetivo
Verificar que se registra en logs cuando un usuario autenticado excede el límite de creación de copropietarios.

### Pasos

1. **Autenticarse en el sistema**:
   - Ir a `http://localhost/login`
   - Usar credenciales válidas
   - Verificar que se redirige al dashboard

2. **Preparar datos de prueba**:
   - Abrir Postman o similar
   - Configurar la petición:
     - Método: POST
     - URL: `http://localhost/copropietarios`
     - Headers: Incluir cookies de sesión
     - Body (form-data):
       ```json
       {
         "copropietarios": [
           {
             "nombre_completo": "Test Copropietario",
             "numero_departamento": 101,
             "tipo": "Propietario"
           }
         ]
       }
       ```

3. **Hacer 11 peticiones rápidamente**:
   - Enviar la petición 11 veces seguidas
   - Puede usar la función "Send" de Postman repetidamente

4. **Verificar las respuestas**:
   - Las primeras 10 peticiones deben ser exitosas (201 o 302)
   - La 11va petición debe retornar 429 "Too Many Attempts"

5. **Revisar el log**:
   ```bash
   tail -n 50 storage/logs/laravel.log
   ```

### Resultado Esperado

```
[2024-01-15 10:35:20] local.WARNING: Rate limit exceeded
{
    "ip": "127.0.0.1",
    "user_id": 1,
    "user_email": "admin@example.com",
    "route": "copropietarios",
    "method": "POST",
    "url": "http://localhost/copropietarios",
    "timestamp": "2024-01-15 10:35:20",
    "user_agent": "PostmanRuntime/7.32.3",
    "limit_exceeded": "Too Many Attempts."
}
```

**Verificar**:
- ✅ `user_id` contiene el ID del usuario autenticado
- ✅ `user_email` contiene el email del usuario
- ✅ `route` es "copropietarios"
- ✅ `timestamp` es la fecha/hora actual

---

## Prueba 3: Rate Limiting en Creación de Personas Autorizadas

### Objetivo
Verificar el logging en la ruta de personas autorizadas.

### Pasos

1. **Asegurarse de estar autenticado**

2. **Configurar petición en Postman**:
   - Método: POST
   - URL: `http://localhost/personas-autorizadas`
   - Body:
     ```json
     {
       "personas_autorizadas": [
         {
           "nombre_completo": "Test Persona",
           "rut_pasaporte": "12345678-9",
           "numero_departamento": 101
         }
       ]
     }
     ```

3. **Hacer 11 peticiones rápidamente**

4. **Verificar respuesta 429 en la 11va petición**

5. **Revisar el log**

### Resultado Esperado

```
[2024-01-15 10:40:15] local.WARNING: Rate limit exceeded
{
    "ip": "127.0.0.1",
    "user_id": 1,
    "user_email": "admin@example.com",
    "route": "personas-autorizadas",
    "method": "POST",
    ...
}
```

---

## Prueba 4: Verificar Información Completa del Log

### Objetivo
Verificar que todos los campos requeridos están presentes y son correctos.

### Checklist de Campos

Para cada log de rate limiting, verificar:

- [ ] **Mensaje**: "Rate limit exceeded"
- [ ] **Nivel**: WARNING
- [ ] **ip**: Dirección IP válida (IPv4 o IPv6)
- [ ] **user_id**: Número o null
- [ ] **user_email**: Email válido o null
- [ ] **route**: Ruta sin el dominio (ej: "login", "copropietarios")
- [ ] **method**: Método HTTP válido (GET, POST, PUT, DELETE)
- [ ] **url**: URL completa con protocolo y dominio
- [ ] **timestamp**: Fecha y hora en formato válido
- [ ] **user_agent**: String con información del cliente
- [ ] **limit_exceeded**: Mensaje de error

---

## Prueba 5: Diferentes Escenarios

### Escenario A: Usuario Autenticado vs No Autenticado

**Verificar**:
- Cuando NO está autenticado: `user_id` y `user_email` son `null`
- Cuando SÍ está autenticado: `user_id` y `user_email` tienen valores

### Escenario B: Diferentes Rutas

**Verificar** que el logging funciona en:
- ✅ `/login`
- ✅ `/copropietarios`
- ✅ `/personas-autorizadas`
- ✅ `/forgot-password`
- ✅ `/reset-password`

### Escenario C: Diferentes Métodos HTTP

**Verificar** que se registra el método correcto:
- POST en creación de recursos
- GET en consultas (si aplica rate limiting)

---

## Troubleshooting

### Problema: No aparecen logs

**Posibles causas**:
1. El archivo de log no tiene permisos de escritura
2. La configuración de logging está incorrecta
3. El rate limiting no está activado

**Soluciones**:
```bash
# Verificar permisos
ls -la storage/logs/

# Dar permisos de escritura
chmod 775 storage/logs/
chmod 664 storage/logs/laravel.log

# Verificar configuración de logging
cat config/logging.php | grep -A 10 "default"

# Verificar que el middleware throttle está activo
cat routes/web.php | grep throttle
```

### Problema: Los tests pasan pero no veo logs en el archivo

**Explicación**: Los tests usan mocks de Log, por lo que no escriben en el archivo real. Esto es correcto y esperado.

**Solución**: Para ver logs reales, hacer pruebas manuales como se describe en esta guía.

### Problema: Error 429 pero sin log

**Posibles causas**:
1. El Exception Handler no está capturando la excepción
2. Hay un error en el código del handler

**Soluciones**:
```bash
# Verificar que el handler está correcto
cat app/Exceptions/Handler.php | grep -A 20 "ThrottleRequestsException"

# Verificar logs de errores de PHP
tail -n 50 storage/logs/laravel.log | grep ERROR
```

---

## Script de Prueba Automatizado (Bash)

Para facilitar las pruebas, puede usar este script:

```bash
#!/bin/bash

# Script de prueba de rate limiting logging
# Uso: ./test_rate_limiting.sh

echo "=== Prueba de Rate Limiting Logging ==="
echo ""

# Limpiar log
echo "Limpiando log anterior..."
> storage/logs/laravel.log

# Hacer 11 peticiones
echo "Haciendo 11 peticiones a /copropietarios..."
for i in {1..11}
do
  echo "Petición $i..."
  curl -X POST http://localhost/copropietarios \
    -H "Content-Type: application/json" \
    -d '{"copropietarios":[{"nombre_completo":"Test","numero_departamento":101,"tipo":"Propietario"}]}' \
    -b cookies.txt \
    -c cookies.txt \
    -s -o /dev/null -w "Status: %{http_code}\n"
  sleep 0.1
done

echo ""
echo "=== Revisando logs ==="
echo ""
tail -n 30 storage/logs/laravel.log | grep -A 10 "Rate limit exceeded"

echo ""
echo "=== Prueba completada ==="
```

---

## Script de Prueba Automatizado (PowerShell)

Para Windows:

```powershell
# Script de prueba de rate limiting logging
# Uso: .\test_rate_limiting.ps1

Write-Host "=== Prueba de Rate Limiting Logging ===" -ForegroundColor Green
Write-Host ""

# Limpiar log
Write-Host "Limpiando log anterior..." -ForegroundColor Yellow
Clear-Content storage/logs/laravel.log

# Hacer 11 peticiones
Write-Host "Haciendo 11 peticiones a /copropietarios..." -ForegroundColor Yellow

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

for ($i = 1; $i -le 11; $i++) {
    Write-Host "Petición $i..."
    
    $body = @{
        copropietarios = @(
            @{
                nombre_completo = "Test"
                numero_departamento = 101
                tipo = "Propietario"
            }
        )
    } | ConvertTo-Json
    
    try {
        $response = Invoke-WebRequest -Uri "http://localhost/copropietarios" `
            -Method POST `
            -Body $body `
            -ContentType "application/json" `
            -WebSession $session `
            -ErrorAction SilentlyContinue
        
        Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    }
    catch {
        Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
    
    Start-Sleep -Milliseconds 100
}

Write-Host ""
Write-Host "=== Revisando logs ===" -ForegroundColor Green
Write-Host ""

Get-Content storage/logs/laravel.log -Tail 30 | Select-String -Pattern "Rate limit exceeded" -Context 0,10

Write-Host ""
Write-Host "=== Prueba completada ===" -ForegroundColor Green
```

---

## Conclusión

Siguiendo esta guía, puede verificar manualmente que:

1. ✅ El logging de rate limiting funciona correctamente
2. ✅ Todos los campos requeridos están presentes
3. ✅ La información registrada es precisa y útil
4. ✅ El sistema cumple con el requisito 25.6

**Próximo paso**: Ejecutar los tests automatizados con `php artisan test --filter=RateLimitingLoggingTest`
