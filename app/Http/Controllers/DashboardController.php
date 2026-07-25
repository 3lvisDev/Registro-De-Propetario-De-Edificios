<?php

namespace App\Http\Controllers;

use App\Models\Copropietario;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Copropietario::count();
        $propietarios = Copropietario::where('tipo', 'propietario')->count();
        $arrendatarios = Copropietario::where('tipo', 'arrendatario')->count();
        $departamentos = Copropietario::query()
            ->distinct()
            ->count('numero_departamento');

        return view('dashboard', compact('total', 'propietarios', 'arrendatarios', 'departamentos'));
    }
}
