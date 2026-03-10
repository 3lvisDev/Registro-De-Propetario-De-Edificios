<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Helper para detectar patrones de inyección de comandos
 * 
 * Esta clase puede ser utilizada independientemente del middleware
 * para validar entradas específicas en cualquier parte de la aplicación.
 */
class CommandInjectionDetector
{
    /**
     * Patrones de inyección de comandos shell a detectar
     *
     * @var array
     */
    protected static $commandInjectionPatterns = [
        '/[;&|`$]/',                    // Caracteres de control de shell
        '/\$\(.*\)/',                   // Sustitución de comandos $(...)
        '/`.*`/',                       // Backticks para ejecución de comandos
        '/\|\|/',                       // OR lógico en shell
        '/&&/',                         // AND lógico en shell
        '/>\s*\//',                     // Redirección a archivos del sistema
        '/<\s*\//',                     // Lectura de archivos del sistema
        '/\bexec\s*\(/i',              // Función exec()
        '/\bshell_exec\s*\(/i',        // Función shell_exec()
        '/\bsystem\s*\(/i',            // Función system()
        '/\bpassthru\s*\(/i',          // Función passthru()
        '/\bpopen\s*\(/i',             // Función popen()
        '/\bproc_open\s*\(/i',         // Función proc_open()
        '/\bpcntl_exec\s*\(/i',        // Función pcntl_exec()
        '/\\\x[0-9a-f]{2}/i',          // Caracteres hexadecimales escapados
        '/\\\[0-7]{1,3}/',             // Caracteres octales escapados
    ];

    /**
     * Comandos shell comunes a detectar
     *
     * @var array
     */
    protected static $suspiciousCommands = [
        '/\b(cat|ls|pwd|whoami|id|uname|wget|curl|nc|netcat|bash|sh|chmod|chown|rm|mv|cp)\b/i',
    ];

    /**
     * Verificar si un valor contiene patrones de inyección de comandos
     *
     * @param string $value Valor a verificar
     * @return bool True si se detectan patrones sospechosos
     */
    public static function containsSuspiciousPatterns(string $value): bool
    {
        // Verificar patrones de inyección
        foreach (self::$commandInjectionPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        // Verificar comandos sospechosos
        foreach (self::$suspiciousCommands as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener los patrones que coinciden con el valor dado
     *
     * @param string $value Valor a verificar
     * @return array Array de patrones que coinciden
     */
    public static function getMatchedPatterns(string $value): array
    {
        $matched = [];

        // Verificar patrones de inyección
        foreach (self::$commandInjectionPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $matched[] = $pattern;
            }
        }

        // Verificar comandos sospechosos
        foreach (self::$suspiciousCommands as $pattern) {
            if (preg_match($pattern, $value)) {
                $matched[] = $pattern;
            }
        }

        return $matched;
    }

    /**
     * Validar y registrar si un valor contiene patrones sospechosos
     *
     * @param string $field Nombre del campo
     * @param string $value Valor a verificar
     * @param array $context Contexto adicional para el log
     * @return bool True si se detectan patrones sospechosos
     */
    public static function validateAndLog(string $field, string $value, array $context = []): bool
    {
        $patterns = self::getMatchedPatterns($value);

        if (!empty($patterns)) {
            Log::warning('Intento sospechoso de inyección de comandos detectado', array_merge([
                'timestamp' => now()->toIso8601String(),
                'field' => $field,
                'value' => $value,
                'matched_patterns' => $patterns,
            ], $context));

            return true;
        }

        return false;
    }

    /**
     * Sanitizar un valor removiendo caracteres peligrosos
     * 
     * NOTA: Esta función NO debe usarse como única medida de seguridad.
     * Es mejor rechazar entradas sospechosas que intentar sanitizarlas.
     *
     * @param string $value Valor a sanitizar
     * @return string Valor sanitizado
     */
    public static function sanitize(string $value): string
    {
        // Remover caracteres de control de shell
        $value = preg_replace('/[;&|`$]/', '', $value);
        
        // Remover sustitución de comandos
        $value = preg_replace('/\$\(.*?\)/', '', $value);
        
        // Remover backticks
        $value = str_replace('`', '', $value);
        
        // Remover operadores lógicos
        $value = str_replace(['||', '&&'], '', $value);
        
        return $value;
    }

    /**
     * Agregar un patrón personalizado de detección
     *
     * @param string $pattern Expresión regular del patrón
     * @return void
     */
    public static function addCustomPattern(string $pattern): void
    {
        self::$commandInjectionPatterns[] = $pattern;
    }

    /**
     * Agregar un comando sospechoso personalizado
     *
     * @param string $pattern Expresión regular del comando
     * @return void
     */
    public static function addSuspiciousCommand(string $pattern): void
    {
        self::$suspiciousCommands[] = $pattern;
    }
}
