@extends('layouts.adminlte')

@section('title', 'Editar Copropietario')

@section('content')
    <h2 class="mb-4">Editar Copropietario</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('copropietarios.update', $copropietario) }}">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Nombre Completo</label>
            <input type="text" name="nombre_completo" class="form-control" value="{{ $copropietario->nombre_completo }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="{{ $copropietario->telefono }}">
        </div>

        <div class="form-group mb-3">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" value="{{ $copropietario->correo }}">
        </div>

        <div class="form-group mb-3">
            <label>Tipo</label>
            <select name="tipo" class="form-control">
                <option value="propietario" {{ $copropietario->tipo == 'propietario' ? 'selected' : '' }}>Propietario</option>
                <option value="arrendatario" {{ $copropietario->tipo == 'arrendatario' ? 'selected' : '' }}>Arrendatario</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Patente</label>
            <input type="text" name="patente" class="form-control" value="{{ $copropietario->patente }}">
        </div>

        <hr>

        <div class="form-group mb-3">
            <label>Número de Departamento</label>
            <input type="text" name="numero_departamento" class="form-control" value="{{ $copropietario->numero_departamento }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Estacionamiento</label>
            <input type="text" name="estacionamiento" class="form-control" value="{{ $copropietario->estacionamiento }}">
        </div>

        <div class="form-group mb-3">
            <label>Bodega</label>
            <input type="text" name="bodega" class="form-control" value="{{ $copropietario->bodega }}">
        </div>

        <hr>
        <h5>Personas Autorizadas</h5>
        <div id="autorizadosContainer">
            @foreach ($autorizados as $i => $autorizado)
                <div class="border rounded p-3 mb-3">
                    <input type="hidden" name="autorizados[{{ $i }}][id]" value="{{ $autorizado->id }}">
                    <div class="form-group mb-2">
                        <label>Nombre Completo</label>
                        <input type="text" name="autorizados[{{ $i }}][nombre_completo]" class="form-control" value="{{ $autorizado->nombre_completo }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>RUT o Pasaporte</label>
                        <input type="text" name="autorizados[{{ $i }}][rut_pasaporte]" class="form-control" value="{{ $autorizado->rut_pasaporte }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Patente Vehículo</label>
                        <input type="text" name="autorizados[{{ $i }}][patente]" class="form-control" value="{{ $autorizado->patente }}">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            <a href="{{ route('copropietarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-warning text-white">Actualizar</button>
        </div>
    </form>
@endsection

