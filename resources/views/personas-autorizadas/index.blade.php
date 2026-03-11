@extends('layouts.base')

@section('title', 'Personas Autorizadas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">👥 Personas Autorizadas</h2>
        <a href="{{ route('personas-autorizadas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nueva Persona Autorizada
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($personasAutorizadas->isEmpty())
        <div class="alert alert-warning">No hay personas autorizadas registradas aún.</div>
    @else
        {{-- Requisito 30.6: Mostrar información de paginación --}}
        <div class="mb-3 text-muted">
            <small>
                Mostrando {{ $personasAutorizadas->firstItem() ?? 0 }} a {{ $personasAutorizadas->lastItem() ?? 0 }} 
                de {{ $personasAutorizadas->total() }} resultados 
                (Página {{ $personasAutorizadas->currentPage() }} de {{ $personasAutorizadas->lastPage() }})
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Identificación</th>
                        <th>Departamento</th>
                        <th>Patente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personasAutorizadas as $persona)
                        <tr>
                            <td>{{ ($personasAutorizadas->currentPage() - 1) * $personasAutorizadas->perPage() + $loop->iteration }}</td>
                            <td>{{ $persona->nombre_completo }}</td>
                            <td>{{ $persona->rut_pasaporte }}</td>
                            <td>{{ $persona->departamento }}</td>
                            <td>{{ $persona->patente }}</td>
                            <td>
                                <form action="{{ route('personas-autorizadas.destroy', $persona->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta persona?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">🗑️ Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Requisito 30.4: Controles de navegación entre páginas --}}
        @if ($personasAutorizadas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $personasAutorizadas->links() }}
            </div>
        @endif
    @endif
@endsection

