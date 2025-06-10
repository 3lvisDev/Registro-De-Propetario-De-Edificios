@extends('layouts.base')

@section('title', 'Editar Copropietario')

@section('content')
    <h2 class="mb-4">✏️ Editar Copropietario</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('copropietarios.update', $copropietario->id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo" class="form-control" value="{{ $copropietario->nombre_completo }}" required>
            </div>
            <div class="col-md-6">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ $copropietario->telefono }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control" value="{{ $copropietario->correo }}">
            </div>
            <div class="col-md-6">
                <label>Tipo</label>
                <select name="tipo" id="tipo" class="form-control" onchange="toggleCampos()">
                    <option value="propietario" {{ $copropietario->tipo == 'propietario' ? 'selected' : '' }}>Propietario</option>
                    <option value="arrendatario" {{ $copropietario->tipo == 'arrendatario' ? 'selected' : '' }}>Arrendatario</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Patente</label>
                <input type="text" name="patente" class="form-control" value="{{ $copropietario->patente }}">
            </div>
            <div class="col-md-6">
                <label>Número de Departamento</label>
                <input type="text" name="numero_departamento" class="form-control" value="{{ $copropietario->numero_departamento }}" required>
            </div>
        </div>

        <div id="camposPropietario">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Estacionamiento</label>
                    <input type="text" name="estacionamiento" class="form-control" value="{{ $copropietario->estacionamiento }}">
                </div>
                <div class="col-md-6">
                    <label>Bodega</label>
                    <input type="text" name="bodega" class="form-control" value="{{ $copropietario->bodega }}">
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('copropietarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-warning text-white">Actualizar</button>
        </div>
    </form>

    <script>
        function toggleCampos() {
            const tipo = document.getElementById('tipo').value;
            const campos = document.getElementById('camposPropietario');
            if (tipo === 'arrendatario') {
                campos.style.display = 'none';
            } else {
                campos.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', toggleCampos);
    </script>
@endsection

