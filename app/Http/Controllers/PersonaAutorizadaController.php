<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaAutorizadaRequest;
use App\Models\PersonaAutorizada;
use Illuminate\Http\Request;
use App\Helpers\AuditLogger;

class PersonaAutorizadaController extends Controller
{
    public function index()
    {
        // Verificar autorización - Requisito 23.3
        $this->authorize('viewAny', PersonaAutorizada::class);
        
        // Requisito 30.1, 30.3: Paginación de 15 registros por página
        $personasAutorizadas = PersonaAutorizada::with('copropietario')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('personas_autorizadas.index', compact('personasAutorizadas'));
    }

    public function create()
    {
        // Verificar autorización - Requisito 23.3
        $this->authorize('create', PersonaAutorizada::class);
        
        return view('personas_autorizadas.create');
    }

    public function store(StorePersonaAutorizadaRequest $request)
    {
        // Verificar autorización - Requisito 23.3
        $this->authorize('create', PersonaAutorizada::class);
        
        $personaAutorizada = PersonaAutorizada::create($request->validated());

        // Auditoría - Requisito 28.4
        AuditLogger::logCreate(
            PersonaAutorizada::class,
            $personaAutorizada->id,
            $personaAutorizada->toArray()
        );

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona autorizada registrada correctamente.');
    }

    public function destroy($id)
    {
        $persona = PersonaAutorizada::findOrFail($id);
        
        // Verificar autorización - Requisito 23.4
        $this->authorize('delete', $persona);
        
        // Guardar valores para auditoría antes de eliminar - Requisito 28.4
        $oldValues = $persona->toArray();
        
        $persona->delete();

        // Auditoría - Requisito 28.4
        AuditLogger::logDelete(
            PersonaAutorizada::class,
            $id,
            $oldValues
        );

        return redirect()->route('personas-autorizadas.index')->with('success', 'Persona eliminada.');
    }
}

