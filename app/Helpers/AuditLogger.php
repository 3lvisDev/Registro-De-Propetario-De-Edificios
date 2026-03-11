<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log a create operation.
     *
     * @param string $modelType The model class name
     * @param int|null $modelId The model ID
     * @param array $newValues The new values
     * @return void
     */
    public static function logCreate(string $modelType, ?int $modelId, array $newValues): void
    {
        self::log('create', $modelType, $modelId, null, $newValues);
    }

    /**
     * Log an update operation.
     *
     * @param string $modelType The model class name
     * @param int|null $modelId The model ID
     * @param array $oldValues The old values before update
     * @param array $newValues The new values after update
     * @return void
     */
    public static function logUpdate(string $modelType, ?int $modelId, array $oldValues, array $newValues): void
    {
        self::log('update', $modelType, $modelId, $oldValues, $newValues);
    }

    /**
     * Log a delete operation.
     *
     * @param string $modelType The model class name
     * @param int|null $modelId The model ID
     * @param array $oldValues The values before deletion
     * @return void
     */
    public static function logDelete(string $modelType, ?int $modelId, array $oldValues): void
    {
        self::log('delete', $modelType, $modelId, $oldValues, null);
    }

    /**
     * Log an unauthorized access attempt.
     *
     * @param string $action The action that was attempted
     * @param string|null $modelType The model class name (optional)
     * @param int|null $modelId The model ID (optional)
     * @return void
     */
    public static function logUnauthorized(string $action, ?string $modelType = null, ?int $modelId = null): void
    {
        self::log('unauthorized', $modelType ?? 'N/A', $modelId, null, ['attempted_action' => $action]);
    }

    /**
     * Core logging method.
     *
     * @param string $action The action performed
     * @param string $modelType The model class name
     * @param int|null $modelId The model ID
     * @param array|null $oldValues The old values
     * @param array|null $newValues The new values
     * @return void
     */
    protected static function log(
        string $action,
        string $modelType,
        ?int $modelId,
        ?array $oldValues,
        ?array $newValues
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log to Laravel's default log if audit logging fails
            \Log::error('Failed to create audit log', [
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
