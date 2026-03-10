<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaAutorizadaRequest;
use App\Models\PersonaAutorizada;
use Illuminate\Http\Request;

class PersonaAutorizadaController extends Controller
{
    public function index()
    {
        $personas = PersonaAutorizada::with('copropietario')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('personas_autorizadas.index', compact('personas'));
    }

    public function create()
    {
        return view('personas_autorizadas.create');
    }

    public function store(StorePersonaAutorizadaRequest $request)
    {
        PersonaAutorizada::create($request->validated());

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona autorizada registrada correctamente.');
    }

    public function destroy($id)
    {
        $persona = PersonaAutorizada::findOrFail($id);
        $persona->delete();

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona eliminada.');
    }
}

