@extends('layouts.base')

@section('title', 'Dashboard')

@section('content')
    <h2 class="mb-4">¡Bienvenido al panel, {{ Auth::user()->name }}!</h2>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-users fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title m-0">Copropietarios</h6>
                        <h2 class="m-0">{{ $total }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-user-shield fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title m-0">Propietarios</h6>
                        <h2 class="m-0">{{ $propietarios }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-user-tag fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title m-0">Arrendatarios</h6>
                        <h2 class="m-0">{{ $arrendatarios }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-dark text-white shadow">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-building fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title m-0">Departamentos únicos</h6>
                        <h2 class="m-0">{{ $departamentos }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

