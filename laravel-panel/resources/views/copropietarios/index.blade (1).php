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

    @if ($departmentsPaginator->isEmpty() && request('buscar'))
        <div class="alert alert-warning text-center" role="alert">
            <h5>No se encontraron departamentos ni copropietarios que coincidan con su término de búsqueda: "{{ request('buscar') }}"</h5>
        </div>
    @elseif ($departmentsPaginator->isEmpty())
        <div class="alert alert-info text-center" role="alert">
            <h5>No hay departamentos registrados o que coincidan con los criterios actuales.</h5>
        </div>
    @else
        @foreach ($departmentsPaginator as $deptNum)
            @php
                $coownersPaginator = $copropietariosData[$deptNum] ?? null;
            @endphp
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">🏠 Departamento {{ $deptNum }}</h5>
                </div>
                <div class="card-body p-0">
                    @if ($coownersPaginator && $coownersPaginator->count() > 0)
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
                                    @foreach ($coownersPaginator as $c)
                                        <tr>
                                            <td>{{ ($coownersPaginator->currentPage() - 1) * $coownersPaginator->perPage() + $loop->iteration }}</td>
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
                        @if ($coownersPaginator->hasPages())
                            <div class="d-flex justify-content-center p-3">
                                {{ $coownersPaginator->appends(['buscar' => $buscar, 'dept_page' => $departmentsPaginator->currentPage()] + ['co_page' => request()->input('co_page', [])])->links() }}
                            </div>
                        @endif
                    @elseif ($coownersPaginator && $coownersPaginator->isEmpty() && request('buscar'))
                        <div class="card-body">
                            <p class="text-center text-muted m-3">No se encontraron copropietarios que coincidan con su término de búsqueda en este departamento.</p>
                        </div>
                    @elseif ($coownersPaginator && $coownersPaginator->isEmpty())
                        <div class="card-body">
                            <p class="text-center text-muted m-3">No hay copropietarios registrados para este departamento.</p>
                        </div>
                    @else {{-- Should ideally not happen if controller guarantees $copropietariosData[$deptNum] for $deptNum in $departmentsPaginator --}}
                        <div class="card-body">
                            <p class="text-center text-muted m-3">No hay información de copropietarios disponible para este departamento.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($departmentsPaginator->hasPages())
            <div class="d-flex justify-content-center mt-4">
                 {{ $departmentsPaginator->appends(['buscar' => $buscar] + ['co_page' => request()->input('co_page', [])])->links() }}
            </div>
        @endif
    @endif
@endsection

