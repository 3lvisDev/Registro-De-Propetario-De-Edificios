@extends('layouts.base')

@section('title', 'Agregar Copropietarios')

@section('content')
    <h2 class="mb-4">Agregar Copropietario y Arrendatarios</h2>

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
        <h5>Personas asociadas</h5>
        <div id="form-personas">
            @include('copropietarios.partials.persona', ['index' => 0, 'tipo' => 'propietario'])
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="agregarPersona('propietario')">
                    ➕ Agregar Propietario
                </button>
                <button type="button" class="btn btn-outline-info btn-sm me-2" onclick="agregarPersona('arrendatario')">
                    ➕ Agregar Arrendatario
                </button>
            </div>
        </div>

        <hr>
        <h5>Personas Autorizadas</h5>
        <div id="autorizadosContainer"></div>
        <button type="button" class="btn btn-outline-warning btn-sm mb-4" onclick="agregarAutorizado()">
            ➕ Agregar Autorizado
        </button>

        <div class="mt-3 d-flex justify-content-between">
            <a href="{{ route('copropietarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Registrar</button>
        </div>
    </form>

    <script>
        let index = 1;
        let autorizadoIndex = 0;

        function agregarPersona(tipo) {
            fetch(`{{ route('copropietarios.partial.persona') }}?index=${index}&tipo=${tipo}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('form-personas').insertAdjacentHTML('beforeend', html);
                    index++;
                });
        }

        function agregarAutorizado() {
            fetch(`{{ route('copropietarios.partial.persona') }}?index=${autorizadoIndex}&tipo=autorizado`)
                .then(response => response.text())
                .then(html => {
                    html = html
                        .replaceAll(`copropietarios[${autorizadoIndex}]`, `autorizados[${autorizadoIndex}]`)
                        .replaceAll('Propietario', 'Autorizado')
                        .replaceAll('Arrendatario', 'Autorizado');

                    const div = document.createElement('div');
                    div.innerHTML = html;
                    document.getElementById('autorizadosContainer').appendChild(div);
                    autorizadoIndex++;
                });
        }
    </script>
@endsection

