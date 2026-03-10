# Ejemplos de Uso - Detección de Inyección de Comandos

## Ejemplos Prácticos

### 1. Validación en FormRequest

```php
<?php

namespace App\Http\Requests;

use App\Helpers\CommandInjectionDetector;
use Illuminate\Foundation\Http\FormRequest;

class StoreCopropietarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_completo' => [
                'required',
                'string',
                'min:5',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (CommandInjectionDetector::containsSuspiciousPatterns($value)) {
                        CommandInjectionDetector::validateAndLog($attribute, $value, [
                            'user_id' => $this->user()?->id,
                            'action' => 'validation_failed'
                        ]);
                        $fail('El campo contiene caracteres no permitidos.');
                    }
                },
            ],
            // ... otros campos
        ];
    }
}
```

### 2. Validación en Controlador

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\CommandInjectionDetector;
use Illuminate\Http\Request;

class CopropietarioController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica
        $validated = $request->validate([
            'nombre_completo' => 'required|string|min:5',
            'telefono' => 'nullable|string',
            // ... otros campos
        ]);

        // Validación adicional de seguridad
        foreach ($validated as $field => $value) {
            if (is_string($value) && CommandInjectionDetector::containsSuspiciousPatterns($value)) {
                CommandInjectionDetector::validateAndLog($field, $value, [
                    'user_id' => auth()->id(),
                    'action' => 'store_copropietario',
                    'ip' => $request->ip()
                ]);
                
                return back()
                    ->withErrors([$field => 'Se detectó contenido sospechoso en este campo.'])
                    ->withInput();
            }
        }

        // Continuar con el procesamiento normal
        $copropietario = Copropietario::create($validated);

        return redirect()->route('copropietarios.index')
            ->with('success', 'Copropietario registrado correctamente.');
    }
}
```

### 3. Validación en Servicio

```php
<?php

namespace App\Services;

use App\Helpers\CommandInjectionDetector;
use App\Models\Copropietario;
use Illuminate\Support\Facades\DB;

class CopropietarioService
{
    /**
     * Crear un nuevo copropietario con validación de seguridad
     */
    public function create(array $data): Copropietario
    {
        // Validar todos los campos de texto
        $this->validateSecurityPatterns($data);

        return DB::transaction(function () use ($data) {
            return Copropietario::create($data);
        });
    }

    /**
     * Validar patrones de seguridad en los datos
     */
    protected function validateSecurityPatterns(array $data): void
    {
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                if (CommandInjectionDetector::containsSuspiciousPatterns($value)) {
                    CommandInjectionDetector::validateAndLog($field, $value, [
                        'service' => 'CopropietarioService',
                        'method' => 'create'
                    ]);
                    
                    throw new \InvalidArgumentException(
                        "El campo {$field} contiene caracteres no permitidos."
                    );
                }
            } elseif (is_array($value)) {
                $this->validateSecurityPatterns($value);
            }
        }
    }
}
```

### 4. Validación en Búsqueda

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\CommandInjectionDetector;
use App\Models\Copropietario;
use Illuminate\Http\Request;

class CopropietarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        // Validar término de búsqueda
        if ($buscar && CommandInjectionDetector::containsSuspiciousPatterns($buscar)) {
            CommandInjectionDetector::validateAndLog('buscar', $buscar, [
                'user_id' => auth()->id(),
                'action' => 'search_copropietarios'
            ]);
            
            return redirect()->route('copropietarios.index')
                ->withErrors(['buscar' => 'El término de búsqueda contiene caracteres no permitidos.']);
        }

        // Continuar con la búsqueda normal
        $query = Copropietario::query();

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        $copropietarios = $query->paginate(10);

        return view('copropietarios.index', compact('copropietarios', 'buscar'));
    }
}
```

### 5. Middleware Personalizado para Rutas Específicas

```php
<?php

namespace App\Http\Middleware;

use App\Helpers\CommandInjectionDetector;
use Closure;
use Illuminate\Http\Request;

class StrictCommandInjectionCheck
{
    /**
     * Middleware más estricto que BLOQUEA peticiones sospechosas
     */
    public function handle(Request $request, Closure $next)
    {
        $allInput = $request->all();

        if ($this->containsSuspiciousContent($allInput)) {
            CommandInjectionDetector::validateAndLog('request', json_encode($allInput), [
                'user_id' => $request->user()?->id,
                'action' => 'blocked_request',
                'ip' => $request->ip()
            ]);

            abort(403, 'Petición bloqueada por contenido sospechoso.');
        }

        return $next($request);
    }

    protected function containsSuspiciousContent($data): bool
    {
        foreach ($data as $value) {
            if (is_string($value) && CommandInjectionDetector::containsSuspiciousPatterns($value)) {
                return true;
            } elseif (is_array($value) && $this->containsSuspiciousContent($value)) {
                return true;
            }
        }

        return false;
    }
}
```

Registrar en `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... otros middlewares
    'strict.command.injection' => \App\Http\Middleware\StrictCommandInjectionCheck::class,
];
```

Usar en rutas específicas:

```php
// En routes/web.php
Route::post('/api/external-data', [ExternalDataController::class, 'store'])
    ->middleware(['auth', 'strict.command.injection']);
```

### 6. Validación en API

```php
<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CommandInjectionDetector;
use App\Models\Copropietario;
use Illuminate\Http\Request;

class CopropietarioApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|min:5',
            // ... otros campos
        ]);

        // Validación de seguridad
        $suspiciousFields = [];
        foreach ($validated as $field => $value) {
            if (is_string($value) && CommandInjectionDetector::containsSuspiciousPatterns($value)) {
                $suspiciousFields[] = $field;
                CommandInjectionDetector::validateAndLog($field, $value, [
                    'user_id' => auth()->id(),
                    'action' => 'api_store_copropietario',
                    'endpoint' => $request->path()
                ]);
            }
        }

        if (!empty($suspiciousFields)) {
            return response()->json([
                'message' => 'Validación fallida',
                'errors' => [
                    'security' => 'Se detectó contenido sospechoso en: ' . implode(', ', $suspiciousFields)
                ]
            ], 422);
        }

        $copropietario = Copropietario::create($validated);

        return response()->json([
            'message' => 'Copropietario creado exitosamente',
            'data' => $copropietario
        ], 201);
    }
}
```

### 7. Comando Artisan para Analizar Logs

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeSecurityLogs extends Command
{
    protected $signature = 'security:analyze-logs {--days=7}';
    protected $description = 'Analizar logs de seguridad de inyección de comandos';

    public function handle()
    {
        $days = $this->option('days');
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            $this->error('Archivo de log no encontrado.');
            return 1;
        }

        $content = File::get($logFile);
        $lines = explode("\n", $content);

        $attempts = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            if (str_contains($line, 'Intento sospechoso de inyección de comandos detectado')) {
                if ($currentEntry) {
                    $attempts[] = $currentEntry;
                }
                $currentEntry = ['log' => $line, 'context' => ''];
            } elseif ($currentEntry && str_starts_with($line, '{')) {
                $currentEntry['context'] .= $line;
            }
        }

        if ($currentEntry) {
            $attempts[] = $currentEntry;
        }

        $this->info("Total de intentos detectados: " . count($attempts));

        if (count($attempts) > 0) {
            $this->table(
                ['Fecha', 'IP', 'Usuario', 'Campo', 'Patrón'],
                array_map(function ($attempt) {
                    $context = json_decode($attempt['context'], true) ?? [];
                    return [
                        $context['timestamp'] ?? 'N/A',
                        $context['ip_address'] ?? 'N/A',
                        $context['user_email'] ?? 'N/A',
                        $context['field'] ?? 'N/A',
                        implode(', ', $context['matched_patterns'] ?? [])
                    ];
                }, array_slice($attempts, -10)) // Últimos 10
            );
        }

        return 0;
    }
}
```

### 8. Agregar Patrones Personalizados en Service Provider

```php
<?php

namespace App\Providers;

use App\Helpers\CommandInjectionDetector;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Agregar patrones personalizados específicos de la aplicación
        CommandInjectionDetector::addCustomPattern('/\bsudo\b/i');
        CommandInjectionDetector::addCustomPattern('/\bsu\s+/i');
        CommandInjectionDetector::addSuspiciousCommand('/\b(kill|pkill|killall)\b/i');
        CommandInjectionDetector::addSuspiciousCommand('/\b(apt|yum|dnf|pacman)\b/i');
    }
}
```

Registrar en `config/app.php`:

```php
'providers' => [
    // ... otros providers
    App\Providers\SecurityServiceProvider::class,
],
```

## Recomendaciones

1. **Usar el middleware automático**: Para la mayoría de casos, el middleware automático es suficiente.

2. **Validación adicional en puntos críticos**: Usar el helper manualmente en operaciones sensibles (búsquedas, APIs externas, etc.).

3. **No confiar solo en detección**: Siempre usar validación de Laravel y reglas de negocio apropiadas.

4. **Revisar logs regularmente**: Establecer un proceso para revisar logs de seguridad.

5. **Considerar bloqueo en APIs públicas**: Para APIs públicas, considerar usar el middleware estricto que bloquea peticiones.

6. **Actualizar patrones**: Mantener los patrones actualizados con nuevas técnicas de ataque.
