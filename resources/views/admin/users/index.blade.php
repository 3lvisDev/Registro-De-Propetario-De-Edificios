@extends('layouts.base')

@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Usuarios</h1>
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Crear usuario</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Correo</th><th>Rol</th><th>Creado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->isAdmin() ? 'Administrador' : 'Usuario' }}</td>
                        <td>{{ $user->created_at->format('d-m-Y H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-info" href="{{ route('admin.users.edit', $user) }}"
                                aria-label="Editar {{ $user->email }}">
                                <i class="fas fa-pen"></i> Editar
                            </a>
                            <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit"
                                    @disabled(auth()->user()->is($user))
                                    onclick="return confirm('¿Eliminar esta cuenta? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
