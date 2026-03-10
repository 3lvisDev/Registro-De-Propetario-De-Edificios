<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CopropietarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonaAutorizadaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider y estarán dentro del
| grupo de middleware "web".
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRUD completo para copropietarios
    // Aplicar rate limiting específicamente a la ruta de creación (store)
    Route::post('copropietarios', [CopropietarioController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('copropietarios.store');
    
    // Resto de rutas del resource sin rate limiting adicional
    Route::resource('copropietarios', CopropietarioController::class)->except(['store']);
    Route::get('/copropietarios/details/{copropietario}', [CopropietarioController::class, 'getDetails'])->name('copropietarios.getDetails');

    // Formulario dinámico por tipo (propietario o arrendatario)
    Route::get('/copropietarios/partials/persona', function () {
        $index = request('index', 0);
        $tipo = request('tipo', 'propietario');
        return view('copropietarios.partials.persona', compact('index', 'tipo'));
    })->name('copropietarios.partial.persona');

    // Formulario parcial para personas autorizadas
    Route::get('/copropietarios/partials/autorizado', function () {
        $index = request('index', 0);
        return view('copropietarios.partials.autorizado', compact('index'));
    })->name('copropietarios.partial.autorizado');

    // Rutas Personas Autorizadas
    // Aplicar rate limiting específicamente a la ruta de creación (store)
    Route::post('personas-autorizadas', [PersonaAutorizadaController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('personas-autorizadas.store');
    
    // Resto de rutas del resource sin rate limiting adicional
    Route::resource('personas-autorizadas', PersonaAutorizadaController::class)->except(['store']);

    // Estado de DuckDNS
    Route::get('/estado-duckdns', function () {
        // Obtener IP del servidor de forma segura usando variables de servidor
        $ip = $_SERVER['SERVER_ADDR'] ?? 'No disponible';
        
        // Validar formato IP antes de usar
        if ($ip !== 'No disponible' && !filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = 'IP inválida';
            \Log::warning('Formato de IP inválido detectado', ['ip' => $_SERVER['SERVER_ADDR'] ?? 'null']);
        }
        
        $estado = trim(file_get_contents(env('HOME') . '/duckdns/duck.log'));
        $hora = date("d-m-Y H:i:s", filemtime(env('HOME') . '/duckdns/duck.log'));
        return view('estado-duckdns', compact('ip', 'estado', 'hora'));
    })->name('duckdns.estado');
});

require __DIR__.'/auth.php';

