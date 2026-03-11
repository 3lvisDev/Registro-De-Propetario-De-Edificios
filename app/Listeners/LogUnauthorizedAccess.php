<?php

namespace App\Listeners;

use App\Helpers\AuditLogger;
use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

class LogUnauthorizedAccess
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Access\Events\GateEvaluated  $event
     * @return void
     */
    public function handle(GateEvaluated $event)
    {
        // Only log when authorization fails
        if ($event->result === false) {
            $modelType = null;
            $modelId = null;
            
            // Extract model information if available
            if (!empty($event->arguments)) {
                $firstArg = $event->arguments[0] ?? null;
                if (is_object($firstArg) && method_exists($firstArg, 'getKey')) {
                    $modelType = get_class($firstArg);
                    $modelId = $firstArg->getKey();
                }
            }
            
            // Log unauthorized attempt - Requisito 28.5
            AuditLogger::logUnauthorized(
                $event->ability ?? 'unknown',
                $modelType,
                $modelId
            );
            
            // Also log to Laravel's default log for monitoring
            Log::warning('Unauthorized access attempt', [
                'user_id' => $event->user?->id,
                'ability' => $event->ability,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'ip_address' => request()->ip(),
                'url' => request()->fullUrl(),
            ]);
        }
    }
}
