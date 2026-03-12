<?php

namespace App\Http\Controllers\Api\Mantenimiento;

use App\Enums\EstadoReporte;
use App\Enums\NivelPrioridad;
use App\Enums\TipoMantenimiento;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReporteResource;
use App\Models\Mantenimiento;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReporteCreado;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 * name="Mantenimiento - Reportes",
 * description="Gestión de reportes"
 * )
 */
/**
 * @OA\Schema(
 * schema="Reporte",
 * type="object",
 * title="Reporte",
 * @OA\Property(property="id", type="integer", format="int64"),
 * @OA\Property(property="activo_id", type="integer"),
 * @OA\Property(property="descripcion", type="string"),
 * @OA\Property(property="prioridad", type="string"),
 * @OA\Property(property="estado", type="string"),
 * @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 * @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */

class ReporteController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/mantenimiento/reportes",
     * summary="Listar reportes",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Lista de reportes", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reporte")))
     * )
     */
    /**
     * Listar reportes usando el Resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Reporte::with(['usuario', 'activo.ubicacion']); // Se coloco el '.ubicacion' para cargar la relación de ubicación del activo, lo que nos permitirá mostrar la sede en el listado de reportes.

        //Filtro por busqueda
        $query->when($request->search, fn($q, $v) => $q->search($v));

        //Filtro por sede
        $query->when($request->sede_id, fn($q, $v) => $q->porSede($v));

        $usuario = Auth::user();

        /** @var User $usuario */
        $esUsuarioNormal = $usuario->hasRole('reporter');

        if ($esUsuarioNormal) {
            $query->where('usuario_id', Auth::id());
        }

        $reportes = $query->get();
        return ReporteResource::collection($reportes);
    }

    //Actualizar estado del reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    /**
     * @OA\Patch(
     * path="/api/mantenimiento/reportes/{id}/estado",
     * summary="Actualizar estado de un reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(@OA\JsonContent(
     *   @OA\Property(property="estado", type="string")
     * )),
     * @OA\Response(response=200, description="Estado del reporte actualizado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=404, description="No encontrado"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function updateEstado(Request $request, $id)
    {
        $reporte = Reporte::findOrFail($id);

        $validated = $request->validate([
            'estado' => ['required', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte->estado = $validated['estado'];
        $reporte->save();


        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Estado del reporte actualizado']);
    }

    //Actualizar prioridad del reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    /**
     * @OA\Patch(
     * path="/api/mantenimiento/reportes/{id}/prioridad",
     * summary="Actualizar prioridad de un reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(@OA\JsonContent(
     *   @OA\Property(property="prioridad", type="string")
     * )),
     * @OA\Response(response=200, description="Prioridad del reporte actualizada", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=404, description="No encontrado"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function updatePrioridad(Request $request, $id)
    {
        $reporte = Reporte::findOrFail($id);

        $validated = $request->validate([
            'prioridad' => ['required', Rule::enum(NivelPrioridad::class)],
        ]);

        $reporte->prioridad = $validated['prioridad'];
        $reporte->save();

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Prioridad del reporte actualizada']);
    }

    //Asignar mantenimiento a un reporte - Encargado de tarea: Sebastian Rodriguez (Lider: Juan Longart - Haddan Valencia)
    /**
     * @OA\Post(
     * path="/api/mantenimiento/reportes/{id}/asignar-mantenimiento",
     * summary="Asignar mantenimiento a un reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(@OA\JsonContent(
     *   @OA\Property(property="tecnico_id", type="integer"),
     *   @OA\Property(property="supervisor_id", type="integer"),
     *   @OA\Property(property="descripcion", type="string"),
     *   @OA\Property(property="tipo", type="string")
     * )),
     * @OA\Response(response=200, description="Mantenimiento asignado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=404, description="No encontrado"),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function asignarMantenimiento(Request $request, $id)
    {
        // Encontrar el reporte por ID
        $reporte = Reporte::findOrFail($id);

        // Validar los datos de entrada para asignar el mantenimiento
        $validated = $request->validate([
            'tecnico_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
            'descripcion' => 'required|string|min:10',
            'tipo' => ['required', Rule::enum(TipoMantenimiento::class)],
        ]);

        // Crear un nuevo mantenimiento asociado al reporte
        \App\Models\Mantenimiento::create([
            'activo_id' => $reporte->activo_id,
            'reporte_id' => $reporte->id,
            'tecnico_principal_id' => $validated['tecnico_id'], // Se asigna el mismo tecnico como el principal
            'supervisor_id' => $validated['supervisor_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'fecha_apertura' => now(),
            'validado' => false, // El mantenimiento aún no ha sido validado
            'costo_total' => 0, // El costo se actualizará una vez que el mantenimiento se complete y se validen los costos reales
            'fecha_cierre' => now()->addDays(15), // Se asigna una fecha de cierre provisional de 15 dias a partir de la fecha de apertura.

            // Como en la tabla de mantenimientos, la fecha de cierre es obligatoria, 
            // pero se puede actualizar posteriormente cuando el mantenimiento se complete.
            // Por ahora, se asigna una fecha de cierre provisional
        ]);

        // Actualizar el estado del reporte a "asignado"
        $reporte->estado = \App\Enums\EstadoReporte::ASIGNADO->value;
        $reporte->save();

        // 1. Buscamos al técnico asignado
        $tecnico = \App\Models\User::find($validated['tecnico_id']);

        // 2. Le avisamos al USUARIO QUE CREÓ EL REPORTE que ya lo están atendiendo
        if ($reporte->usuario && $tecnico) {
            $reporte->usuario->notify(new \App\Notifications\ReporteAtendido($reporte, $tecnico));
        }

        return (new \App\Http\Resources\ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Mantenimiento asignado al técnico']);
    }

    /**
     * @OA\Post(
     * path="/api/mantenimiento/reportes",
     * summary="Crear reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(@OA\JsonContent(
     * @OA\Property(property="activo_id", type="integer"),
     * @OA\Property(property="descripcion", type="string"),
     * @OA\Property(property="prioridad", type="string"),
     * @OA\Property(property="estado", type="string")
     * )),
     * @OA\Response(response=201, description="Reporte creado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id'   => 'required|exists:activos,id',
            'descripcion' => 'required|string|min:10',
            'prioridad'   => ['required', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['required', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte = Reporte::create([
            ...$validated,
            'usuario_id' => $request->user()->id,
        ]);

        //Se obtienen los usuarios con roles especificos
        $usuarios = User::role(['admin', 'supervisor'])->get();

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte creado exitosamente']);
    }

    /**
     * @OA\Get(
     * path="/api/mantenimiento/reportes/{id}",
     * summary="Ver reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Reporte", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Reporte $reporte): ReporteResource
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        if ($usuario->hasRole('reporter') && $reporte->usuario_id !== $usuario->id) {
            abort(403, 'Acceso denegado. Este reporte no te pertenece.');
        }

        return new ReporteResource($reporte->load(['usuario', 'activo', 'mantenimientos']));
    }




    public function verMisReportes(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $direction = $request->query('direction', 'desc');
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';

        $query = Reporte::with([
            'usuario',
            'activo.ubicacion',
            'mantenimientos.tecnicoPrincipal',
            'mantenimientos.supervisor',
        ])
            ->where('usuario_id', Auth::id());

        // Filtros: búsqueda libre, estado y prioridad
        $query->when($request->search, fn($q, $v) => $q->search($v));
        $query->when($request->estado, fn($q, $v) => $q->where(Rule::enum(EstadoReporte::class), $v));
        $query->when($request->prioridad, fn($q, $v) => $q->where(Rule::enum(NivelPrioridad::class), $v));

        // Ordenar por fecha de creación (default: desc)
        $query->orderBy('created_at', $direction);

        return ReporteResource::collection($query->paginate($perPage));
    }

    /**
     * @OA\Get(
     * path="/api/mantenimiento/reportes/mios",
     * summary="Ver mis reportes",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Lista de reportes del usuario autenticado", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reporte")))
     * )
     */

    /**
     * @OA\Put(
     * path="/api/mantenimiento/reportes/{id}",
     * summary="Actualizar reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(@OA\JsonContent(
     * @OA\Property(property="descripcion", type="string", nullable=true),
     * @OA\Property(property="prioridad", type="string", nullable=true),
     * @OA\Property(property="estado", type="string", nullable=true)
     * )),
     * @OA\Response(response=200, description="Reporte actualizado", @OA\JsonContent(ref="#/components/schemas/Reporte")),
     * @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, Reporte $reporte)
    {
        $validated = $request->validate([
            'activo_id'   => 'sometimes|exists:activos,id',
            'descripcion' => 'sometimes|string|min:10',
            'prioridad'   => ['sometimes', Rule::enum(NivelPrioridad::class)],
            'estado'      => ['sometimes', Rule::enum(EstadoReporte::class)],
        ]);

        $reporte->update($validated);

        return (new ReporteResource($reporte->load(['usuario', 'activo'])))
            ->additional(['message' => 'Reporte actualizado']);
    }

    /**
     * @OA\Delete(
     * path="/api/mantenimiento/reportes/{id}",
     * summary="Eliminar reporte",
     * tags={"Mantenimiento - Reportes"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=204, description="Eliminado"),
     * @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(Reporte $reporte)
    {
        $reporte->delete();
        return response()->noContent();
    }
}
