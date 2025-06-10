<?php

namespace App\Http\Controllers;

use App\Models\PersonaAutorizada;
use Illuminate\Http\Request;

class PersonaAutorizadaController extends Controller
{
    public function index()
    {
        $personas = PersonaAutorizada::orderBy('created_at', 'desc')->get();
        return view('personas_autorizadas.index', compact('personas'));
    }

    public function create()
    {
        return view('personas_autorizadas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'rut_o_pasaporte' => 'required|string|max:20',
            'departamento' => 'required|string|max:10',
            'patente' => 'nullable|string|max:20',
        ]);

        PersonaAutorizada::create($request->all());

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona autorizada registrada correctamente.');
    }

    public function destroy($id)
    {
        $persona = PersonaAutorizada::findOrFail($id);
        $persona->delete();

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona eliminada.');
    }
}

