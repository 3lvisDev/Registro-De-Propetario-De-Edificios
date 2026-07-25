@extends('layouts.base')

@section('title', 'Editar usuario')

@section('content')
    <h1 class="h3 mb-3">Editar usuario</h1>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card card-body" style="max-width: 640px">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email"
                name="email" value="{{ old('email', $user->email) }}" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Nueva contraseña</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password"
                type="password" name="password" aria-describedby="passwordHelp">
            <div id="passwordHelp" class="form-text">Déjela vacía para mantenerla. Si la cambia, use al menos 12 caracteres, mayúscula, minúscula, número y símbolo.</div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirmar nueva contraseña</label>
            <input class="form-control" id="password_confirmation" type="password"
                name="password_confirmation">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input @error('is_admin') is-invalid @enderror" id="is_admin"
                type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->isAdmin()))>
            <label class="form-check-label" for="is_admin">Administrador con todos los permisos</label>
            @error('is_admin') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div>
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
