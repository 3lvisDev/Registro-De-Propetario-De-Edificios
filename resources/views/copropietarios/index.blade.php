@extends('layouts.base')

@section('title', 'Copropietarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">🏢 Copropietarios por Departamento</h2>
        <a href="{{ route('copropietarios.create') }}" class="btn btn-warning text-white">
            <i class="fas fa-plus-circle"></i> Agregar Nuevo
        </a>
    </div>

    <form method="GET" action="{{ route('copropietarios.index') }}" class="mb-4 d-flex gap-2">
        <input type="text" name="buscar" class="form-control" placeholder="🔍 Buscar por nombre, patente, etc." value="{{ request('buscar') }}">
        <button type="submit" class="btn btn-dark">Buscar</button>
    </form>

    @foreach ($copropietarios as $depto => $grupo)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">🏠 Departamento {{ $depto }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover m-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Estac.</th>
                                <th>Bodega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center align-middle">
                            @foreach ($grupo as $index => $c)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $c->nombre_completo }}</td>
                                    <td>
                                        @if($c->tipo === 'propietario')
                                            <span class="badge bg-primary">Propietario</span>
                                        @else
                                            <span class="badge bg-info text-dark">Arrendatario</span>
                                        @endif
                                    </td>
                                    <td>{{ $c->telefono }}</td>
                                    <td>{{ $c->correo }}</td>
                                    <td>{{ $c->estacionamiento }}</td>
                                    <td>{{ $c->bodega }}</td>
                                    <td>
                                        <a href="{{ route('copropietarios.edit', $c->id) }}" class="btn btn-sm btn-info mb-1">✏️</a>
                                        <form action="{{ route('copropietarios.destroy', $c->id) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este copropietario?')">🗑</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Paginación bonita con flechas --}}
    @if ($totalPaginas > 1)
        <div class="d-flex justify-content-center mt-4">
            @if ($pagina > 1)
                <a href="{{ route('copropietarios.index', ['page' => $pagina - 1, 'buscar' => request('buscar')]) }}"
                   class="btn btn-sm btn-outline-secondary mx-1">&laquo; Anterior</a>
            @endif

            @for ($i = 1; $i <= $totalPaginas; $i++)
                <a href="{{ route('copropietarios.index', ['page' => $i, 'buscar' => request('buscar')]) }}"
                   class="btn btn-sm {{ $pagina == $i ? 'btn-primary' : 'btn-outline-secondary' }} mx-1">
                    {{ $i }}
                </a>
            @endfor

            @if ($pagina < $totalPaginas)
                <a href="{{ route('copropietarios.index', ['page' => $pagina + 1, 'buscar' => request('buscar')]) }}"
                   class="btn btn-sm btn-outline-secondary mx-1">Siguiente &raquo;</a>
            @endif
        </div>
    @endif
@endsection

