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
    Route::resource('copropietarios', CopropietarioController::class);

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

    // Rutas Personas Autorizadas (solo si necesitas acceso directo por CRUD)
    Route::resource('personas-autorizadas', PersonaAutorizadaController::class);

    // Estado de DuckDNS
    Route::get('/estado-duckdns', function () {
        $ip = trim(shell_exec("curl -s https://ipv4.icanhazip.com"));
        $estado = trim(file_get_contents(env('HOME') . '/duckdns/duck.log'));
        $hora = date("d-m-Y H:i:s", filemtime(env('HOME') . '/duckdns/duck.log'));
        return view('estado-duckdns', compact('ip', 'estado', 'hora'));
    })->name('duckdns.estado');
});

require __DIR__.'/auth.php';

