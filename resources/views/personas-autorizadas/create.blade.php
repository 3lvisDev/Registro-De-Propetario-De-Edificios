@extends('layouts.base')

@section('title', 'Registrar Persona Autorizada')

@section('content')
    <h2 class="mb-4">🧾 Registrar Persona Autorizada</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('personas-autorizadas.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre Completo</label>
            <input type="text" name="nombre_completo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">RUT o Pasaporte</label>
            <input type="text" name="documento_identidad" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Número de Departamento</label>
            <input type="text" name="departamento" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Patente del Vehículo</label>
            <input type="text" name="patente_vehiculo" class="form-control">
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('personas-autorizadas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Registrar</button>
        </div>
    </form>
@endsection

