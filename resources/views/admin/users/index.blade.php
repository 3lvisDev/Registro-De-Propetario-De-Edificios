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

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Correo</th><th>Rol</th><th>Creado</th></tr></thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->isAdmin() ? 'Administrador' : 'Usuario' }}</td>
                        <td>{{ $user->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
