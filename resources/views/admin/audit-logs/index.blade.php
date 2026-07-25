@extends('layouts.base')

@section('title', 'Registro de actividad')

@section('content')
    @php
        $actionLabels = [
            'create' => ['Creó', 'success'],
            'update' => ['Editó', 'info'],
            'delete' => ['Eliminó', 'danger'],
            'unauthorized' => ['Acceso rechazado', 'warning'],
            'backup_export' => ['Exportó respaldo', 'primary'],
            'backup_import' => ['Restauró respaldo', 'primary'],
        ];
        $modelLabels = [
            App\Models\Copropietario::class => 'Copropietario / arrendatario',
            App\Models\PersonaAutorizada::class => 'Persona autorizada',
            App\Models\User::class => 'Cuenta de usuario',
            'SystemBackup' => 'Respaldo del sistema',
            'N/A' => 'Sistema',
        ];
        $fieldLabels = [
            'nombre_completo' => 'Nombre completo',
            'numero_departamento' => 'Departamento',
            'telefono' => 'Teléfono',
            'correo' => 'Correo',
            'tipo' => 'Tipo',
            'patente' => 'Patente',
            'estacionamiento' => 'Estacionamiento',
            'bodega' => 'Bodega',
            'rut_pasaporte' => 'RUT/Pasaporte',
            'departamento' => 'Departamento',
            'email' => 'Correo de usuario',
            'is_admin' => 'Administrador',
            'password_changed' => 'Contraseña modificada',
            'exported_by' => 'Exportado por',
        ];
    @endphp

    <div class="mb-4">
        <h1 class="h3 mb-1">Registro de actividad</h1>
        <p class="text-muted mb-0">Historial de quién creó, modificó o eliminó información del sistema.</p>
    </div>

    <form class="card card-body border-0 shadow-sm mb-4" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="action">Acción</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Todas</option>
                    @foreach ($actionLabels as $value => [$label])
                        <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="user_id">Usuario</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="model">Información</label>
                <select class="form-select" id="model" name="model">
                    <option value="">Toda</option>
                    @foreach ($models as $model)
                        <option value="{{ $model }}" @selected(request('model') === $model)>{{ $modelLabels[$model] ?? class_basename($model) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-dark" type="submit"><i class="fas fa-filter me-2"></i>Filtrar</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.audit-logs.index') }}">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Usuario responsable</th>
                        <th>Acción</th>
                        <th>Información afectada</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            [$actionLabel, $actionColor] = $actionLabels[$log->action] ?? [ucfirst($log->action), 'secondary'];
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') ?? 'Sin fecha' }}</td>
                            <td>
                                <strong>{{ $log->user?->email ?? 'Usuario eliminado / sistema' }}</strong>
                                @if ($log->user_id)<div class="small text-muted">ID {{ $log->user_id }}</div>@endif
                            </td>
                            <td><span class="badge text-bg-{{ $actionColor }}">{{ $actionLabel }}</span></td>
                            <td>
                                {{ $modelLabels[$log->model_type] ?? class_basename($log->model_type) }}
                                @if ($log->model_id)<span class="text-muted">#{{ $log->model_id }}</span>@endif
                            </td>
                            <td style="min-width: 260px">
                                @if ($log->old_values || $log->new_values)
                                    <details>
                                        <summary class="btn btn-sm btn-outline-secondary">Ver cambios</summary>
                                        <div class="mt-2 small">
                                            @if ($log->old_values)
                                                <div class="fw-bold text-danger mb-1">Datos anteriores</div>
                                                @foreach ($log->old_values as $field => $value)
                                                    <div><span class="text-muted">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                        {{ is_bool($value) ? ($value ? 'Sí' : 'No') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? 'Vacío')) }}
                                                    </div>
                                                @endforeach
                                            @endif
                                            @if ($log->new_values)
                                                <div class="fw-bold text-success mt-2 mb-1">Datos nuevos</div>
                                                @foreach ($log->new_values as $field => $value)
                                                    <div><span class="text-muted">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                        {{ is_bool($value) ? ($value ? 'Sí' : 'No') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? 'Vacío')) }}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <span class="text-muted">Sin cambios registrados</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-center text-muted py-5" colspan="5">No hay actividad que coincida con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
