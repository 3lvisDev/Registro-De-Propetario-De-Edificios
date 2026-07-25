<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'action' => ['nullable', 'in:create,update,delete,unauthorized,backup_export,backup_import'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $logs = AuditLog::query()
            ->with('user:id,email')
            ->when($validated['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($validated['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($validated['model'] ?? null, fn ($query, $model) => $query->where('model_type', $model))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('email')->get(['id', 'email']),
            'models' => AuditLog::query()->distinct()->orderBy('model_type')->pluck('model_type'),
        ]);
    }
}
