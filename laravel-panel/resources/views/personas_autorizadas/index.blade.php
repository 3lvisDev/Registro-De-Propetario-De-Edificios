@extends('layouts.base')

@section('title', 'Personas Autorizadas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">🛂 Personas Autorizadas</h2>
        <a href="{{ route('personas-autorizadas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Agregar Nueva
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-dark text-center">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>RUT/Pasaporte</th>
                <th>Depto</th>
                <th>Patente</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="text-center align-middle">
            @foreach ($personas as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->nombre_completo }}</td>
                    <td>{{ $p->rut_o_pasaporte }}</td>
                    <td>{{ $p->departamento }}</td>
                    <td>{{ $p->patente }}</td>
                    <td>
                        <form action="{{ route('personas-autorizadas.destroy', $p->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('¿Eliminar esta persona?')" class="btn btn-sm btn-danger">🗑</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

