<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;

class LogRateLimitExceeded extends ThrottleRequests
{
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        Log::warning('Rate limit exceeded', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'route' => $request->route()?->getName() ?? $request->path(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'timestamp' => now()->toIso8601String(),
        ]);

        return parent::buildException($request, $key, $maxAttempts, $responseCallback);
    }
}
