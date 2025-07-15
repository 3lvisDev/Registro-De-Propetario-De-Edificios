<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class CopropietarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $dept_page = $request->get('dept_page', 1);
        $co_page = $request->input('co_page', []); // Ensure co_page is an array

        $departmentsPerPage = 3;
        $coownersPerPage = 10;

        $allDepartmentNumbersQuery = Copropietario::query();
        $copropietariosData = [];

        if ($buscar) {
            // Apply search to the base query to find relevant co-owners
            $allDepartmentNumbersQuery->where(function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%$buscar%")
                    ->orWhere('telefono', 'like', "%$buscar%")
                    ->orWhere('correo', 'like', "%$buscar%")
                    ->orWhere('patente', 'like', "%$buscar%")
                    ->orWhere('estacionamiento', 'like', "%$buscar%")
                    ->orWhere('bodega', 'like', "%$buscar%");
                if (is_numeric($buscar)) {
                    $q->orWhere('numero_departamento', '=', $buscar);
                } else {
                    // Allow searching for non-numeric department numbers if they exist as strings
                    $q->orWhere('numero_departamento', 'like', "%$buscar%");
                }
            });
        }

        // Get the list of department numbers that are relevant after applying the search
        $relevantDepartmentNumbers = $allDepartmentNumbersQuery->select('numero_departamento')
                                    ->distinct()
                                    ->orderBy('numero_departamento')
                                    ->pluck('numero_departamento');

        // Create the outer paginator for departments
        $currentPageDept = Paginator::resolveCurrentPage('dept_page');
        $currentDepartmentSlice = $relevantDepartmentNumbers->slice(($currentPageDept - 1) * $departmentsPerPage, $departmentsPerPage);
        
        $departmentsPaginator = new LengthAwarePaginator(
            $currentDepartmentSlice,
            $relevantDepartmentNumbers->count(),
            $departmentsPerPage,
            $currentPageDept,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'dept_page']
        );

        // Now, for each department in the current department page, get its co-owners
        foreach ($currentDepartmentSlice as $deptNum) {
            $coownerQuery = Copropietario::where('numero_departamento', $deptNum);

            // If buscar is active, we need to filter co-owners *within* this department.
            // However, if $buscar *is* $deptNum, we don't need to re-filter by $buscar,
            // as we want all co-owners of that specific department.
            if ($buscar && strval($deptNum) !== strval($buscar)) { // ensure string comparison
                 $coownerQuery->where(function ($q) use ($buscar) {
                    $q->where('nombre_completo', 'like', "%$buscar%")
                        ->orWhere('telefono', 'like', "%$buscar%")
                        ->orWhere('correo', 'like', "%$buscar%")
                        ->orWhere('patente', 'like', "%$buscar%")
                        ->orWhere('estacionamiento', 'like', "%$buscar%")
                        ->orWhere('bodega', 'like', "%$buscar%");
                    // No need to check for numero_departamento here again as it's already $deptNum
                });
            }
            
            $coownerQuery->orderBy('tipo'); // Order co-owners by type

            // Ensure co_page value for this deptNum is an integer
            $currentCoPageForDept = isset($co_page[$deptNum]) ? (int)$co_page[$deptNum] : 1;

            $paginatedCoowners = $coownerQuery->paginate($coownersPerPage, ['*'], 'co_page.' . $deptNum, $currentCoPageForDept);
            $copropietariosData[$deptNum] = $paginatedCoowners;
        }

        return view('copropietarios.index', compact('departmentsPaginator', 'copropietariosData', 'buscar', 'co_page'));
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

    /**
     * Fetch details for a specific copropietario.
     *
     * @param  \App\Models\Copropietario  $copropietario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Copropietario $copropietario)
    {
        // The $copropietario model is already loaded by route model binding.
        // You can choose to load specific relations if needed, e.g.,
        // $copropietario->load('relationName');

        return response()->json($copropietario);
    }
}

