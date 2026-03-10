# Sistema de Encriptación de Datos Sensibles

## 📋 Resumen

Este sistema implementa **encriptación de extremo a extremo** para datos sensibles de usuarios en la base de datos. Los datos se almacenan encriptados y solo son visibles en texto plano para usuarios autenticados con acceso al sistema.

## 🔐 Seguridad Implementada

### Algoritmo de Encriptación
- **Algoritmo:** AES-256-CBC
- **Clave:** APP_KEY definida en `.env`
- **Biblioteca:** Laravel Crypt (basada en OpenSSL)

### Datos Protegidos

#### Modelo Copropietario
Los siguientes campos se encriptan automáticamente:
- `nombre_completo` - Información personal sensible
- `telefono` - Dato de contacto privado
- `correo` - Dato de contacto privado

#### Modelo PersonaAutorizada
Los siguientes campos se encriptan automáticamente:
- `nombre_completo` - Información personal sensible
- `rut_pasaporte` - Documento de identidad (altamente sensible)

## 🛡️ Cómo Funciona

### 1. Encriptación Automática (Al Guardar)

Cuando se crea o actualiza un registro:

```php
$copropietario = Copropietario::create([
    'nombre_completo' => 'Juan Pérez',  // Se guarda encriptado
    'telefono' => '+56912345678',        // Se guarda encriptado
    'correo' => 'juan@example.com',      // Se guarda encriptado
    'numero_departamento' => '101',      // NO se encripta
]);
```

**En la base de datos se almacena:**
```
nombre_completo: "eyJpdiI6IkR2..."  (encriptado)
telefono: "eyJpdiI6IkFiY..."         (encriptado)
correo: "eyJpdiI6IkNkZW..."          (encriptado)
numero_departamento: "101"           (texto plano)
```

### 2. Desencriptación Automática (Al Leer)

Cuando se recupera un registro:

```php
$copropietario = Copropietario::find(1);
echo $copropietario->nombre_completo;  // "Juan Pérez" (desencriptado)
echo $copropietario->telefono;         // "+56912345678" (desencriptado)
```

### 3. Protección de Acceso

**✅ Usuarios autenticados:**
- Pueden ver los datos en texto plano a través de la aplicación
- Los datos se desencriptan automáticamente al acceder

**❌ Acceso directo a la base de datos:**
- Los datos están encriptados
- Sin la clave APP_KEY, los datos son ilegibles
- Protege contra acceso no autorizado a la BD

## 📝 Implementación Técnica

### Trait EncryptsAttributes

El trait `App\Traits\EncryptsAttributes` maneja la encriptación/desencriptación:

```php
use App\Traits\EncryptsAttributes;

class Copropietario extends Model
{
    use EncryptsAttributes;
    
    protected $encryptable = [
        'nombre_completo',
        'telefono',
        'correo',
    ];
}
```

### Métodos Principales

1. **setAttribute()** - Encripta antes de guardar
2. **getAttribute()** - Desencripta al leer
3. **attributesToArray()** - Desencripta al convertir a array/JSON

## ⚠️ Limitaciones Importantes

### 1. Búsqueda en Campos Encriptados

**❌ NO FUNCIONA:**
```php
// Esto NO encontrará resultados porque el campo está encriptado
Copropietario::where('nombre_completo', 'Juan Pérez')->get();
```

**✅ SOLUCIÓN:**
```php
// Buscar por campos NO encriptados
Copropietario::where('numero_departamento', '101')->get();

// O cargar todos y filtrar en PHP (menos eficiente)
$copropietarios = Copropietario::all();
$resultado = $copropietarios->filter(function($c) {
    return str_contains($c->nombre_completo, 'Juan');
});
```

### 2. Rendimiento

- La encriptación/desencriptación tiene un costo computacional
- Para grandes volúmenes de datos, considerar:
  - Cachear resultados frecuentes
  - Limitar campos encriptados a datos realmente sensibles
  - Usar paginación

### 3. Migración de Datos Existentes

Si ya tienes datos en texto plano, necesitas migrarlos:

```php
// Comando artisan para migrar datos existentes
php artisan make:command EncryptExistingData

// En el comando:
Copropietario::chunk(100, function ($copropietarios) {
    foreach ($copropietarios as $copropietario) {
        // Forzar re-guardado para encriptar
        $copropietario->save();
    }
});
```

## 🔑 Gestión de Claves

### APP_KEY

La clave de encriptación está en `.env`:

```env
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

**⚠️ CRÍTICO:**
- **NUNCA** compartas o versiones el archivo `.env`
- **NUNCA** cambies APP_KEY en producción (perderás acceso a datos encriptados)
- Haz backup de APP_KEY en lugar seguro
- Usa diferentes APP_KEY para desarrollo y producción

### Rotación de Claves

Si necesitas cambiar APP_KEY:

1. Desencripta todos los datos con la clave antigua
2. Cambia APP_KEY
3. Re-encripta todos los datos con la clave nueva

```php
// Ejemplo de rotación (simplificado)
$oldKey = config('app.key');
$newKey = 'nueva-clave-generada';

Copropietario::chunk(100, function ($copropietarios) use ($oldKey, $newKey) {
    foreach ($copropietarios as $copropietario) {
        // Desencriptar con clave antigua
        $data = Crypt::decryptString($copropietario->getRawOriginal('nombre_completo'));
        
        // Encriptar con clave nueva
        config(['app.key' => $newKey]);
        $copropietario->nombre_completo = $data;
        $copropietario->save();
    }
});
```

## 🧪 Testing

### Verificar Encriptación

```php
// Test: Verificar que los datos se encriptan
$copropietario = Copropietario::create([
    'nombre_completo' => 'Test User',
]);

// Leer directamente de la BD (sin desencriptar)
$raw = DB::table('copropietarios')
    ->where('id', $copropietario->id)
    ->value('nombre_completo');

// El valor raw debe estar encriptado
$this->assertNotEquals('Test User', $raw);
$this->assertStringStartsWith('eyJpdiI6', $raw); // Formato encriptado

// Pero al leer con Eloquent, se desencripta
$this->assertEquals('Test User', $copropietario->nombre_completo);
```

## 📊 Cumplimiento de Requisitos

✅ **Datos encriptados en la base de datos**
- Los datos sensibles se almacenan encriptados con AES-256-CBC

✅ **Solo usuarios autenticados pueden ver datos**
- La aplicación requiere autenticación (middleware auth)
- Sin acceso a la aplicación, los datos son ilegibles

✅ **Protección de extremo a extremo**
- Datos encriptados en reposo (base de datos)
- Datos transmitidos por HTTPS (en producción)
- Datos solo visibles en la aplicación autenticada

✅ **Protección contra acceso directo a BD**
- Incluso con acceso a la base de datos, los datos están encriptados
- Se requiere APP_KEY para desencriptar

## 🚀 Despliegue en Producción

### Checklist de Seguridad

- [ ] Generar APP_KEY única para producción: `php artisan key:generate`
- [ ] Configurar HTTPS para transmisión segura
- [ ] Hacer backup seguro de APP_KEY
- [ ] Restringir acceso directo a la base de datos
- [ ] Configurar permisos de archivos `.env` (chmod 600)
- [ ] Habilitar logs de auditoría
- [ ] Configurar rate limiting
- [ ] Implementar autenticación de dos factores (opcional)

### Variables de Entorno Requeridas

```env
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_ENV=production
APP_DEBUG=false
HTTPS=true
```

## 📚 Referencias

- [Laravel Encryption](https://laravel.com/docs/encryption)
- [AES-256-CBC](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
- [OWASP Data Protection](https://owasp.org/www-project-top-ten/)

## 🆘 Soporte

Para problemas con la encriptación:

1. Verificar que APP_KEY está configurada
2. Revisar logs en `storage/logs/laravel.log`
3. Verificar permisos de archivos
4. Consultar documentación de Laravel Crypt

---

**Fecha de implementación:** 2024  
**Versión:** 1.0  
**Estado:** ✅ ACTIVO
