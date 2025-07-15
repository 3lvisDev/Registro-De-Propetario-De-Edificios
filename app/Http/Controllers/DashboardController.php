<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Copropietario;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Copropietario::count();
        $propietarios = Copropietario::where('tipo', 'propietario')->count();
        $arrendatarios = Copropietario::where('tipo', 'arrendatario')->count();
        $departamentos = Copropietario::select('numero_departamento')->distinct()->count();

        return view('dashboard', compact('total', 'propietarios', 'arrendatarios', 'departamentos'));
    }
}

