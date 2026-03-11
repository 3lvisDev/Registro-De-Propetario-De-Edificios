<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Logging para rate limiting (Requisito 25.6)
        $this->reportable(function (ThrottleRequestsException $e) {
            $request = request();
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'route' => $request->path(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toDateTimeString(),
                'user_agent' => $request->userAgent(),
                'limit_exceeded' => $e->getMessage(),
            ]);
        });

        // Manejo de errores de base de datos (Requisito 31.4)
        $this->reportable(function (QueryException $e) {
            $request = request();
            
            // Registrar error completo en logs con todos los detalles
            Log::channel('database')->error('Database query error', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route' => $request->path(),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'timestamp' => now()->toDateTimeString(),
                'user_agent' => $request->userAgent(),
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Manejo personalizado de errores de base de datos (Requisito 31.3, 31.4)
        if ($exception instanceof QueryException) {
            // En producción, mostrar mensaje genérico sin detalles técnicos
            if (!config('app.debug')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Ha ocurrido un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.',
                        'error' => 'database_error'
                    ], 500);
                }

                return response()->view('errors.500', [
                    'exception' => $exception
                ], 500);
            }
        }

        return parent::render($request, $exception);
    }
}
