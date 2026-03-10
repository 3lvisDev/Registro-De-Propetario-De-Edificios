# Tarea 8.3: Protección XSS en Respuestas JSON

## Resumen

Se implementó protección contra ataques XSS en las respuestas JSON del endpoint `getDetails()` del `CopropietarioController`, cumpliendo con el **Requisito 27.3**.

## Cambios Realizados

### 1. Modificación del método `getDetails()` en `CopropietarioController.php`

**Ubicación**: `app/Http/Controllers/CopropietarioController.php`

**Cambio**: Se agregaron flags de escape HTML a la respuesta JSON:

```php
return response()->json(
    $copropietario,
    200,
    [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
```

### 2. Flags de Escape Implementadas

Las siguientes flags de PHP se utilizan para escapar caracteres HTML en la respuesta JSON:

| Flag | Carácter | Conversión | Propósito |
|------|----------|------------|-----------|
| `JSON_HEX_TAG` | `<` y `>` | `\u003C` y `\u003E` | Previene inyección de tags HTML |
| `JSON_HEX_AMP` | `&` | `\u0026` | Previene inyección de entidades HTML |
| `JSON_HEX_APOS` | `'` | `\u0027` | Previene escape de atributos HTML con comillas simples |
| `JSON_HEX_QUOT` | `"` | `\u0022` | Previene escape de atributos HTML con comillas dobles |

### 3. Ejemplo de Funcionamiento

**Entrada (datos en BD desencriptados)**:
```json
{
  "nombre_completo": "Juan <script>alert('XSS')</script> Pérez",
  "telefono": "555-1234 & Co.",
  "patente": "ABC'123"
}
```

**Salida (JSON escapado)**:
```json
{
  "nombre_completo": "Juan \u003Cscript\u003Ealert(\u0027XSS\u0027)\u003C/script\u003E Pérez",
  "telefono": "555-1234 \u0026 Co.",
  "patente": "ABC\u0027123"
}
```

## Contexto de Seguridad

### Flujo de Datos

1. **Almacenamiento**: Los datos sensibles (`nombre_completo`, `telefono`, `correo`) se almacenan **encriptados** en la base de datos usando el trait `EncryptsAttributes`.

2. **Desencriptación**: Al recuperar el modelo, los datos se desencriptan automáticamente.

3. **Escape JSON**: Al retornar como JSON, los caracteres HTML se escapan usando las flags implementadas.

4. **Cliente**: El cliente recibe datos seguros que no pueden ejecutar scripts maliciosos.

### ¿Por qué es necesario el escape si los datos están encriptados?

La encriptación protege los datos **en reposo** (en la base de datos), pero no protege contra XSS cuando los datos se envían al cliente. Un atacante podría:

1. Inyectar código malicioso en un campo de texto (ej: `<script>alert('XSS')</script>`)
2. El código se almacena encriptado en la BD (seguro)
3. Al recuperarse y desencriptarse, el código malicioso está presente
4. **Sin escape**: El cliente recibe el código y podría ejecutarlo
5. **Con escape**: El cliente recibe el código escapado y no se ejecuta

## Tests Implementados

Se creó el archivo `tests/Feature/CopropietarioJsonEscapeTest.php` con dos tests:

### Test 1: `test_get_details_escapes_html_characters_in_json_response`

Verifica que caracteres HTML peligrosos se escapan correctamente:
- Crea un copropietario con caracteres especiales: `<`, `>`, `&`, `'`, `"`
- Hace petición GET al endpoint `/copropietarios/{id}/details`
- Verifica que los caracteres están escapados en el JSON crudo
- Verifica que tags `<script>` no aparecen sin escapar

### Test 2: `test_get_details_works_with_normal_data`

Verifica que datos normales funcionan correctamente:
- Crea un copropietario con datos normales
- Hace petición GET al endpoint
- Verifica que la estructura JSON es correcta
- Verifica que los datos están presentes

## Requisitos Cumplidos

✅ **Requisito 27.3**: "WHEN se retorna JSON con datos de usuario, THE Sistema SHALL escapar caracteres especiales HTML"

## Comandos para Ejecutar Tests

```bash
# Ejecutar solo los tests de escape JSON
php artisan test --filter=CopropietarioJsonEscapeTest

# Ejecutar todos los tests de la aplicación
php artisan test

# Ejecutar con cobertura
php artisan test --coverage
```

## Notas Adicionales

- Esta implementación es específica para el endpoint `getDetails()` que retorna un solo copropietario.
- Si se agregan más endpoints que retornen JSON con datos de usuario, deben aplicarse las mismas flags.
- Las vistas Blade ya están protegidas usando la sintaxis `{{ }}` que escapa HTML automáticamente (Tarea 8.1).
- La sanitización de entradas se realiza en los FormRequests (Tarea 8.2).

## Referencias

- [PHP JSON Constants](https://www.php.net/manual/en/json.constants.php)
- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- Requisito 27.3 en `.kiro/specs/gestion-copropietarios/requirements.md`
