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

    <div class="card mb-4 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover m-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Departamento</th>
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
                        @foreach ($copropietarios as $index => $c)
                            <tr>
                                <td>{{ ($copropietarios->currentPage() - 1) * $copropietarios->perPage() + $loop->iteration }}</td>
                                <td>{{ $c->numero_departamento }}</td>
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

    <div class="d-flex justify-content-center mt-4">
        {{ $copropietarios->links() }}
    </div>
@endsection

