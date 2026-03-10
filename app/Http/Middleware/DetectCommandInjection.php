<?php

namespace App\Http\Middleware;

use App\Helpers\CommandInjectionDetector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DetectCommandInjection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener todos los datos de entrada (query, post, json)
        $allInput = $request->all();
        
        // Detectar patrones sospechosos
        $this->detectSuspiciousPatterns($request, $allInput);

        return $next($request);
    }

    /**
     * Detectar patrones sospechosos en los datos de entrada
     *
     * @param Request $request
     * @param array $data
     * @param string $prefix
     * @return void
     */
    protected function detectSuspiciousPatterns(Request $request, array $data, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                // Recursivamente revisar arrays anidados
                $this->detectSuspiciousPatterns($request, $value, $fullKey);
            } elseif (is_string($value)) {
                // Verificar patrones de inyección de comandos
                $this->checkForCommandInjection($request, $fullKey, $value);
            }
        }
    }

    /**
     * Verificar si un valor contiene patrones de inyección de comandos
     *
     * @param Request $request
     * @param string $field
     * @param string $value
     * @return void
     */
    protected function checkForCommandInjection(Request $request, string $field, string $value): void
    {
        $detectedPatterns = CommandInjectionDetector::getMatchedPatterns($value);

        // Si se detectaron patrones sospechosos, registrar en logs
        if (!empty($detectedPatterns)) {
            $this->logSuspiciousActivity($request, $field, $value, $detectedPatterns);
        }
    }

    /**
     * Registrar actividad sospechosa en los logs
     *
     * @param Request $request
     * @param string $field
     * @param string $value
     * @param array $patterns
     * @return void
     */
    protected function logSuspiciousActivity(Request $request, string $field, string $value, array $patterns): void
    {
        Log::warning('Intento sospechoso de inyección de comandos detectado', [
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'field' => $field,
            'value' => $value,
            'matched_patterns' => $patterns,
            'all_input' => $request->except(['password', 'password_confirmation']),
        ]);
    }
}
