@extends('layouts.base')

@section('title', 'Crear Copropietario')

@section('content')
    <h2 class="mb-4">Registrar Copropietarios</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('copropietarios.store') }}">
        @csrf

        <div class="mb-3">
            <label>Número de Departamento</label>
            <input type="text" name="numero_departamento" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Estacionamiento</label>
            <input type="text" name="estacionamiento" class="form-control">
        </div>

        <div class="mb-3">
            <label>Bodega</label>
            <input type="text" name="bodega" class="form-control">
        </div>

        <hr>
        <h5>Copropietarios</h5>
        <div class="border rounded p-3 mb-3">
            <input type="hidden" name="copropietarios[0][tipo]" value="propietario">
            <div class="form-group mb-2">
                <label>Nombre Completo</label>
                <input type="text" name="copropietarios[0][nombre_completo]" class="form-control" required>
            </div>
            <div class="form-group mb-2">
                <label>Teléfono</label>
                <input type="text" name="copropietarios[0][telefono]" class="form-control">
            </div>
            <div class="form-group mb-2">
                <label>Correo</label>
                <input type="email" name="copropietarios[0][correo]" class="form-control">
            </div>
            <div class="form-group mb-2">
                <label>Patente</label>
                <input type="text" name="copropietarios[0][patente]" class="form-control">
            </div>
        </div>

        <h5>Personas Autorizadas</h5>
        <div class="border rounded p-3 mb-3">
            <div class="form-group mb-2">
                <label>Nombre Completo</label>
                <input type="text" name="autorizados[0][nombre_completo]" class="form-control">
            </div>
            <div class="form-group mb-2">
                <label>RUT o Pasaporte</label>
                <input type="text" name="autorizados[0][rut_pasaporte]" class="form-control">
            </div>
            <div class="form-group mb-2">
                <label>Patente Vehículo</label>
                <input type="text" name="autorizados[0][patente]" class="form-control">
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('copropietarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
@endsection
