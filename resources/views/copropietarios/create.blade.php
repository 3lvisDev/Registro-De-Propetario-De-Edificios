@extends('layouts.base')

@section('title', 'Agregar Copropietario')

@section('content')
<div class="container">
    <h2 class="text-dark mb-4">🏢 Agregar Nuevo Copropietario y Departamento</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('copropietarios.store') }}" method="POST">
        @csrf
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Información del Departamento</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="numero_departamento" class="form-label">Número de Departamento</label>
                        <input type="text" class="form-control" id="numero_departamento" name="numero_departamento" value="{{ old('numero_departamento') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="estacionamiento" class="form-label">Estacionamiento</label>
                        <input type="text" class="form-control" id="estacionamiento" name="estacionamiento" value="{{ old('estacionamiento') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="bodega" class="form-label">Bodega</label>
                        <input type="text" class="form-control" id="bodega" name="bodega" value="{{ old('bodega') }}">
                    </div>
                </div>
            </div>
        </div>

        <div id="copropietarios-container">
            <div class="card mb-3 copropietario-card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Datos del Copropietario</h5>
                    <button type="button" class="btn btn-danger btn-sm remove-copropietario">Eliminar</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="copropietarios[0][nombre_completo]" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" name="copropietarios[0][nombre_completo]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="copropietarios[0][tipo]" class="form-label">Tipo</label>
                            <select class="form-select" name="copropietarios[0][tipo]">
                                <option value="propietario">Propietario</option>
                                <option value="arrendatario">Arrendatario</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="copropietarios[0][telefono]" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="copropietarios[0][telefono]">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="copropietarios[0][correo]" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="copropietarios[0][correo]">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="copropietarios[0][patente]" class="form-label">Patente</label>
                            <input type="text" class="form-control" name="copropietarios[0][patente]">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="add-copropietario" class="btn btn-primary mb-3">Agregar Otro Copropietario</button>

        <div class="d-flex justify-content-end">
            <a href="{{ route('copropietarios.index') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let copropietarioIndex = 1;
    document.getElementById('add-copropietario').addEventListener('click', function() {
        const container = document.getElementById('copropietarios-container');
        const newCard = document.createElement('div');
        newCard.classList.add('card', 'mb-3', 'copropietario-card');
        newCard.innerHTML = `
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Datos del Copropietario</h5>
                <button type="button" class="btn btn-danger btn-sm remove-copropietario">Eliminar</button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="copropietarios[${copropietarioIndex}][nombre_completo]" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="copropietarios[${copropietarioIndex}][nombre_completo]" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="copropietarios[${copropietarioIndex}][tipo]" class="form-label">Tipo</label>
                        <select class="form-select" name="copropietarios[${copropietarioIndex}][tipo]">
                            <option value="propietario">Propietario</option>
                            <option value="arrendatario">Arrendatario</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="copropietarios[${copropietarioIndex}][telefono]" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="copropietarios[${copropietarioIndex}][telefono]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="copropietarios[${copropietarioIndex}][correo]" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="copropietarios[${copropietarioIndex}][correo]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="copropietarios[${copropietarioIndex}][patente]" class="form-label">Patente</label>
                        <input type="text" class="form-control" name="copropietarios[${copropietarioIndex}][patente]">
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newCard);
        copropietarioIndex++;
    });

    document.getElementById('copropietarios-container').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-copropietario')) {
            e.target.closest('.copropietario-card').remove();
        }
    });
});
</script>
@endpush
@endsection
