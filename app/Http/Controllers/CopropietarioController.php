<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Http\Requests\UpdateCopropietarioRequest;
use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CopropietarioController extends Controller
{
    public function index(Request $request)
    {
        // Verificar autorización - Requisito 23.1
        $this->authorize('viewAny', Copropietario::class);

        $buscar = $request->get('buscar');
        $dept_page = $request->get('dept_page', 1);
        $co_page = $request->input('co_page', []); // Ensure co_page is an array

        $departmentsPerPage = 3;
        $coownersPerPage = 10;

        $copropietariosData = [];
        $matchingIds = collect();

        if ($buscar) {
            // Los campos personales están cifrados y no se pueden consultar con LIKE.
            // En una instalación local, se descifran y filtran en memoria.
            $matchingRecords = Copropietario::all()->filter(
                fn (Copropietario $copropietario) => $this->matchesSearch($copropietario, $buscar)
            );

            $matchingIds = $matchingRecords->pluck('id');
            $relevantDepartmentNumbers = $matchingRecords
                ->pluck('numero_departamento')
                ->unique()
                ->sort()
                ->values();
        } else {
            $relevantDepartmentNumbers = Copropietario::query()
                ->select('numero_departamento')
                ->distinct()
                ->orderBy('numero_departamento')
                ->pluck('numero_departamento');
        }

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
                $coownerQuery->whereIn('id', $matchingIds);
            }

            $coownerQuery
                ->orderByRaw("CASE WHEN tipo = 'propietario' THEN 0 ELSE 1 END")
                ->orderBy('id');

            // Ensure co_page value for this deptNum is an integer
            $currentCoPageForDept = isset($co_page[$deptNum]) ? (int) $co_page[$deptNum] : 1;

            $paginatedCoowners = $coownerQuery->paginate($coownersPerPage, ['*'], 'co_page.'.$deptNum, $currentCoPageForDept);
            $copropietariosData[$deptNum] = $paginatedCoowners;
        }

        return view('copropietarios.index', compact('departmentsPaginator', 'copropietariosData', 'buscar', 'co_page'));
    }

    private function matchesSearch(Copropietario $copropietario, string $search): bool
    {
        $needle = Str::lower(trim($search));

        foreach ([
            $copropietario->nombre_completo,
            $copropietario->telefono,
            $copropietario->correo,
            $copropietario->patente,
            $copropietario->estacionamiento,
            $copropietario->bodega,
            $copropietario->numero_departamento,
        ] as $value) {
            if ($value !== null && Str::contains(Str::lower((string) $value), $needle)) {
                return true;
            }
        }

        return false;
    }

    public function create()
    {
        // Verificar autorización - Requisito 23.1
        $this->authorize('create', Copropietario::class);

        return view('copropietarios.create');
    }

    public function store(Request $request)
    {
        // Verificar autorización - Requisito 23.1
        $this->authorize('create', Copropietario::class);

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

        $personas = collect($validated['copropietarios']);
        $propietarioExistente = Copropietario::query()
            ->where('numero_departamento', $validated['numero_departamento'])
            ->where('tipo', 'propietario')
            ->first();

        if (! $propietarioExistente && ! $personas->contains(fn (array $persona) => $persona['tipo'] === 'propietario')) {
            return back()->withInput()->withErrors([
                'copropietarios' => 'Debe registrar al menos un propietario.',
            ]);
        }

        DB::transaction(function () use ($validated, $propietarioExistente): void {
            $propietarioPrincipalId = $propietarioExistente?->id;
            $personasOrdenadas = collect($validated['copropietarios'])
                ->sortBy(fn (array $persona) => $persona['tipo'] === 'propietario' ? 0 : 1);

            foreach ($personasOrdenadas as $persona) {
                $nuevo = Copropietario::create([
                    'nombre_completo' => $persona['nombre_completo'],
                    'telefono' => $persona['telefono'] ?? null,
                    'correo' => $persona['correo'] ?? null,
                    'tipo' => $persona['tipo'],
                    'patente' => $persona['patente'] ?? null,
                    'numero_departamento' => $validated['numero_departamento'],
                    'estacionamiento' => $validated['estacionamiento'] ?? null,
                    'bodega' => $validated['bodega'] ?? null,
                    'propietario_id' => $persona['tipo'] === 'arrendatario' ? $propietarioPrincipalId : null,
                ]);

                AuditLogger::logCreate(Copropietario::class, $nuevo->id, $nuevo->toArray());

                if ($persona['tipo'] === 'propietario' && $propietarioPrincipalId === null) {
                    $propietarioPrincipalId = $nuevo->id;
                }
            }

            foreach ($validated['autorizados'] ?? [] as $autorizado) {
                PersonaAutorizada::create([
                    'nombre_completo' => $autorizado['nombre_completo'],
                    'rut_pasaporte' => $autorizado['rut_pasaporte'],
                    'departamento' => $autorizado['departamento'] ?? $validated['numero_departamento'],
                    'patente' => $autorizado['patente'] ?? null,
                    'copropietario_id' => $propietarioPrincipalId,
                ]);
            }
        });

        return redirect()->route('copropietarios.index')->with('success', 'Copropietarios y personas autorizadas registradas correctamente.');
    }

    public function edit($id)
    {
        $copropietario = Copropietario::findOrFail($id);

        // Verificar autorización - Requisito 23.1
        $this->authorize('update', $copropietario);

        $autorizados = PersonaAutorizada::where('departamento', $copropietario->numero_departamento)->get();

        return view('copropietarios.edit', compact('copropietario', 'autorizados'));
    }

    public function update(UpdateCopropietarioRequest $request, $id)
    {
        $copropietario = Copropietario::findOrFail($id);

        // Verificar autorización - Requisito 23.1
        $this->authorize('update', $copropietario);

        // Guardar valores antiguos para auditoría - Requisito 28.2
        $oldValues = $copropietario->toArray();

        $copropietario->update($request->validated());

        // Auditoría - Requisito 28.2
        AuditLogger::logUpdate(
            Copropietario::class,
            $copropietario->id,
            $oldValues,
            $copropietario->toArray()
        );

        return redirect()->route('copropietarios.index')->with('success', 'Copropietario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $copropietario = Copropietario::findOrFail($id);

        // Verificar autorización - Requisito 23.2
        $this->authorize('delete', $copropietario);

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

        // Guardar valores para auditoría antes de eliminar - Requisito 28.3
        $oldValues = $copropietario->toArray();

        $copropietario->delete();

        // Auditoría - Requisito 28.3
        AuditLogger::logDelete(
            Copropietario::class,
            $id,
            $oldValues
        );

        return redirect()->route('copropietarios.index')->with('success', 'Copropietario eliminado correctamente.');
    }

    /**
     * Fetch details for a specific copropietario.
     *
     * @return JsonResponse
     */
    public function getDetails(Copropietario $copropietario)
    {
        // Verificar autorización - Requisito 23.1
        $this->authorize('view', $copropietario);

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
