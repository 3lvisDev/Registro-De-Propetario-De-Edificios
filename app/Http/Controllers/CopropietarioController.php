<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\UpdateCopropietarioRequest;

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
            $coownerQuery = Copropietario::with(['arrendatarios', 'personasAutorizadas'])
                ->where('numero_departamento', $deptNum);

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
                // Validación de integridad referencial - Requisito 32.4
                // Verificar que existe un propietario principal antes de asignar
                if (!$propietarioPrincipalId) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['copropietarios' => 'Debe registrar un propietario antes de registrar arrendatarios.']);
                }
                $nuevo->propietario_id = $propietarioPrincipalId;
            }

            $nuevo->save();

            if ($persona['tipo'] === 'propietario' && !$propietarioPrincipalId) {
                $propietarioPrincipalId = $nuevo->id;
            }
        }

        if ($request->has('autorizados')) {
            foreach ($request->autorizados as $autorizado) {
                // Validación de integridad referencial - Requisito 32.5
                // Verificar que existe un copropietario principal antes de crear persona autorizada
                if (!$propietarioPrincipalId) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['autorizados' => 'Debe registrar un copropietario antes de registrar personas autorizadas.']);
                }

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

    public function update(UpdateCopropietarioRequest $request, $id)
    {
        $copropietario = Copropietario::findOrFail($id);

        $copropietario->update($request->validated());

        return redirect()->route('copropietarios.index')->with('success', 'Copropietario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $copropietario = Copropietario::findOrFail($id);

        // Validación de integridad referencial - Requisito 32.1
        // Verificar si es un Propietario con Arrendatarios asociados
        if ($copropietario->tipo === 'propietario') {
            $arrendatariosCount = $copropietario->arrendatarios()->count();
            
            if ($arrendatariosCount > 0) {
                return redirect()->route('copropietarios.index')
                    ->with('error', "No se puede eliminar el propietario porque tiene {$arrendatariosCount} arrendatario(s) asociado(s). Elimine primero los arrendatarios o confirme la eliminación en cascada.");
            }
        }

        // Validación de integridad referencial - Requisito 32.2
        // Verificar si tiene Personas Autorizadas asociadas
        $personasAutorizadasCount = $copropietario->personasAutorizadas()->count();
        
        if ($personasAutorizadasCount > 0) {
            return redirect()->route('copropietarios.index')
                ->with('warning', "El copropietario tiene {$personasAutorizadasCount} persona(s) autorizada(s) asociada(s). Al eliminarlo, también se eliminarán las personas autorizadas.");
        }

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

        // Escape HTML characters in JSON response to prevent XSS attacks (Requisito 27.3)
        // JSON_HEX_TAG: Converts < and > to \u003C and \u003E
        // JSON_HEX_AMP: Converts & to \u0026
        // JSON_HEX_APOS: Converts ' to \u0027
        // JSON_HEX_QUOT: Converts " to \u0022
        return response()->json(
            $copropietario,
            200,
            [],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }
}

