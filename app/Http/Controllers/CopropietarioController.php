<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Copropietario;
use App\Models\PersonaAutorizada;

class CopropietarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $query = Copropietario::query();

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%$buscar%")
                    ->orWhere('telefono', 'like', "%$buscar%")
                    ->orWhere('correo', 'like', "%$buscar%")
                    ->orWhere('patente', 'like', "%$buscar%")
                    ->orWhere('estacionamiento', 'like', "%$buscar%")
                    ->orWhere('bodega', 'like', "%$buscar%");

                // Búsqueda exacta si es número
                if (is_numeric($buscar)) {
                    $q->orWhere('numero_departamento', '=', $buscar);
                } else {
                    $q->orWhere('numero_departamento', 'like', "%$buscar%");
                }
            });
        }

        $filtrados = $query->orderBy('numero_departamento')->orderBy('tipo')->get();
        $agrupado = $filtrados->groupBy('numero_departamento');
        $departamentos = $agrupado->keys()->sort()->values()->all();

        $pagina = $request->get('page', 1);
        $porPagina = 2;
        $bloqueDeptos = array_slice($departamentos, ($pagina - 1) * $porPagina, $porPagina);

        $copropietarios = collect();
        foreach ($bloqueDeptos as $numero) {
            $copropietarios[$numero] = $agrupado[$numero];
        }

        $totalPaginas = ceil(count($departamentos) / $porPagina);

        return view('copropietarios.index', compact('copropietarios', 'pagina', 'totalPaginas'));
    }

    public function create()
    {
        return view('copropietarios.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_departamento' => 'required|string|max:10',
            'estacionamiento' => 'nullable|string|max:50',
            'bodega' => 'nullable|string|max:50',
            'copropietarios' => 'required|array|min:1',
            'copropietarios.*.nombre_completo' => 'required|string|min:5|max:100',
            'copropietarios.*.telefono' => 'nullable|string|max:20',
            'copropietarios.*.correo' => 'nullable|email',
            'copropietarios.*.patente' => 'nullable|string|max:20',
            'copropietarios.*.tipo' => 'required|in:propietario,arrendatario',
            'autorizados.*.nombre_completo' => 'required|string|min:3',
            'autorizados.*.rut_pasaporte' => 'required|string',
            'autorizados.*.departamento' => 'nullable|string|max:10',
            'autorizados.*.patente' => 'nullable|string|max:20',
        ]);

        $propietarioPrincipalId = null;

        foreach ($request->copropietarios as $persona) {
            $nuevo = new Copropietario();
            $nuevo->nombre_completo = $persona['nombre_completo'];
            $nuevo->telefono = $persona['telefono'];
            $nuevo->correo = $persona['correo'];
            $nuevo->tipo = $persona['tipo'];
            $nuevo->patente = $persona['patente'];
            $nuevo->numero_departamento = $request->numero_departamento;
            $nuevo->estacionamiento = $request->estacionamiento;
            $nuevo->bodega = $request->bodega;

            if ($persona['tipo'] === 'arrendatario') {
                $nuevo->propietario_id = $propietarioPrincipalId;
            }

            $nuevo->save();

            if ($persona['tipo'] === 'propietario' && !$propietarioPrincipalId) {
                $propietarioPrincipalId = $nuevo->id;
            }
        }

        if ($request->has('autorizados')) {
            foreach ($request->autorizados as $autorizado) {
                PersonaAutorizada::create([
                    'nombre_completo' => $autorizado['nombre_completo'],
                    'rut_pasaporte' => $autorizado['rut_pasaporte'],
                    'departamento' => $autorizado['departamento'] ?? $request->numero_departamento,
                    'patente' => $autorizado['patente'],
                    'copropietario_id' => $propietarioPrincipalId,
                ]);
            }
        }

        return redirect()->route('copropietarios.index')->with('success', 'Copropietarios y personas autorizadas registradas correctamente.');
    }

    public function edit($id)
    {
        $copropietario = Copropietario::findOrFail($id);
        $autorizados = PersonaAutorizada::where('departamento', $copropietario->numero_departamento)->get();

        return view('copropietarios.edit', compact('copropietario', 'autorizados'));
    }

    public function update(Request $request, $id)
    {
        $copropietario = Copropietario::findOrFail($id);

        $copropietario->update([
            'nombre_completo' => $request->nombre_completo,
            'tipo' => $request->tipo,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'patente' => $request->patente,
            'numero_departamento' => $request->numero_departamento,
            'estacionamiento' => $request->estacionamiento,
            'bodega' => $request->bodega,
        ]);

        return redirect()->route('copropietarios.index')->with('success', 'Copropietario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $copropietario = Copropietario::findOrFail($id);
        $copropietario->delete();

        return redirect()->route('copropietarios.index')->with('success', 'Copropietario eliminado correctamente.');
    }
}

