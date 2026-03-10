# Guía de Verificación de Rate Limiting

## Verificación Manual del Rate Limiting en Autenticación

Esta guía proporciona instrucciones paso a paso para verificar que el rate limiting está funcionando correctamente en las rutas de autenticación.

## Requisitos Previos

- Servidor Laravel en ejecución (`php artisan serve`)
- Navegador web o herramienta como Postman/cURL
- Usuario de prueba creado en la base de datos

## Método 1: Verificación con Navegador Web

### Test de Login Rate Limiting

1. **Iniciar el servidor**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=5050
   ```

2. **Acceder a la página de login**:
   ```
   http://localhost:5050/login
   ```

3. **Realizar intentos de login fallidos**:
   - Ingresar email: `test@example.com`
   - Ingresar contraseña incorrecta: `wrongpassword`
   - Hacer clic en "Login"
   - Repetir 5 veces

4. **Verificar el bloqueo**:
   - En el sexto intento, deberías ver un error:
   - **Mensaje esperado**: "Too Many Attempts. Please try again in X seconds."
   - **Código HTTP**: 429 Too Many Requests

5. **Esperar y reintentar**:
   - Esperar 60 segundos
   - Intentar login nuevamente
   - El sistema debería permitir nuevos intentos

### Test de Password Reset Rate Limiting

1. **Acceder a "Forgot Password"**:
   ```
   http://localhost:5050/forgot-password
   ```

2. **Solicitar reset 5 veces**:
   - Ingresar email: `test@example.com`
   - Hacer clic en "Send Password Reset Link"
   - Repetir 5 veces

3. **Verificar el bloqueo**:
   - En el sexto intento, deberías ver el error 429

## Método 2: Verificación con cURL

### Test de Login

```bash
# Realizar 6 intentos de login
for i in {1..6}; do
  echo "Intento $i:"
  curl -X POST http://localhost:5050/login \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "email=test@example.com&password=wrongpassword" \
    -w "\nHTTP Status: %{http_code}\n\n" \
    -s -o /dev/null
  sleep 1
done
```

**Resultado esperado**:
- Intentos 1-5: HTTP Status 302 (redirect) o 422 (validation error)
- Intento 6: HTTP Status 429 (Too Many Requests)

### Test de Password Reset

```bash
# Realizar 6 intentos de password reset
for i in {1..6}; do
  echo "Intento $i:"
  curl -X POST http://localhost:5050/forgot-password \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "email=test@example.com" \
    -w "\nHTTP Status: %{http_code}\n\n" \
    -s -o /dev/null
  sleep 1
done
```

## Método 3: Verificación con Tests Automatizados

### Ejecutar Tests de Rate Limiting

```bash
# Ejecutar todos los tests de rate limiting
php artisan test --filter=AuthRateLimitingTest

# Ejecutar test específico
php artisan test --filter=test_login_rate_limiting_blocks_after_five_attempts
```

### Resultados Esperados

```
PASS  Tests\Feature\AuthRateLimitingTest
✓ login rate limiting blocks after five attempts
✓ successful login within rate limit
✓ rate limiting is per ip address

Tests:  3 passed
Time:   X.XXs
```

## Método 4: Verificación con Postman

### Configurar Colección de Postman

1. **Crear nueva colección**: "Rate Limiting Tests"

2. **Agregar request de Login**:
   - Method: POST
   - URL: `http://localhost:5050/login`
   - Body (x-www-form-urlencoded):
     - `email`: test@example.com
     - `password`: wrongpassword

3. **Configurar Runner**:
   - Seleccionar la colección
   - Iterations: 6
   - Delay: 1000ms

4. **Ejecutar y verificar**:
   - Primeras 5 iteraciones: Status 302 o 422
   - Sexta iteración: Status 429

## Verificación de Logs

### Revisar Logs de Laravel

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

### Buscar Eventos de Rate Limiting

```bash
# Buscar eventos de throttle
grep "ThrottleRequestsException" storage/logs/laravel.log
```

**Ejemplo de log esperado**:
```
[2024-XX-XX XX:XX:XX] local.ERROR: Too Many Attempts. {"exception":"[object] (Illuminate\\Http\\Exceptions\\ThrottleRequestsException...
```

## Verificación de Headers HTTP

### Usar cURL con Headers Verbose

```bash
curl -X POST http://localhost:5050/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=test@example.com&password=wrongpassword" \
  -v
```

### Headers Esperados en Respuesta 429

```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0
```

## Checklist de Verificación

- [ ] Login bloqueado después de 5 intentos
- [ ] Password reset bloqueado después de 5 intentos
- [ ] Reset password bloqueado después de 5 intentos
- [ ] Mensaje de error apropiado mostrado
- [ ] Header Retry-After presente en respuesta 429
- [ ] Rate limit se resetea después de 1 minuto
- [ ] Tests automatizados pasan correctamente
- [ ] Logs registran eventos de rate limiting

## Troubleshooting

### Problema: Rate limiting no funciona

**Posibles causas**:
1. Cache no configurado correctamente
2. Middleware no aplicado a la ruta
3. Configuración de throttle incorrecta

**Solución**:
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear

# Verificar rutas
php artisan route:list --path=login
```

### Problema: Rate limit se aplica demasiado rápido

**Causa**: Múltiples requests desde la misma IP

**Solución**: Esperar 60 segundos entre tests o limpiar cache:
```bash
php artisan cache:clear
```

### Problema: Tests fallan

**Causa**: Base de datos no configurada para tests

**Solución**:
```bash
# Configurar base de datos de tests
php artisan migrate --env=testing

# Ejecutar tests con refresh
php artisan test --filter=AuthRateLimitingTest
```

## Notas Importantes

1. **Ambiente de Desarrollo**: En desarrollo, el rate limiting puede ser más permisivo
2. **Ambiente de Producción**: Verificar que `APP_ENV=production` en `.env`
3. **Cache Driver**: El rate limiting usa el cache driver configurado en `config/cache.php`
4. **IP Forwarding**: En producción con proxy/load balancer, configurar `TrustProxies` middleware

## Referencias

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/10.x/routing#rate-limiting)
- [HTTP Status Code 429](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429)
- Requisito 25.1 del documento de requisitos
