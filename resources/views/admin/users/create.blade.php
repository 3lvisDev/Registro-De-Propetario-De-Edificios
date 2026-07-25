@extends('layouts.base')

@section('title', 'Crear usuario')

@section('content')
    <h1 class="h3 mb-3">Crear usuario</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="card card-body" style="max-width: 640px">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email"
                name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password"
                type="password" name="password" required>
            <div class="form-text">Mínimo 12 caracteres, con mayúscula, minúscula, número y símbolo.</div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
            <input class="form-control" id="password_confirmation" type="password"
                name="password_confirmation" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" id="is_admin" type="checkbox" name="is_admin" value="1"
                @checked(old('is_admin'))>
            <label class="form-check-label" for="is_admin">Otorgar permisos administrativos</label>
        </div>
        <div>
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
