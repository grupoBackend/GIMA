<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuditoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auditorias = Auditoria::with(['usuario'])->get();
        return response()->json($auditorias, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'entidad'    => 'required|string|max:100',
            'entidad_id' => 'required|integer',
            'accion'     => 'required|string|max:50',
            'descripcion'=> 'nullable|string|max:255',
            'fecha'      => 'nullable|date',
        ]);

        $auditoria = Auditoria::create($data);

        return response()->json($auditoria, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Auditoria $auditoria)
    {
        $auditoria->load(['usuario']);
        return response()->json($auditoria, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Auditoria $auditoria)
    {
        $data = $request->validate([
            'usuario_id' => 'sometimes|required|exists:users,id',
            'entidad'    => 'sometimes|required|string|max:100',
            'entidad_id' => 'sometimes|required|integer',
            'accion'     => 'sometimes|required|string|max:50',
            'descripcion'=> 'nullable|string|max:255',
            'fecha'      => 'nullable|date',
        ]);

        $auditoria->update($data);

        return response()->json($auditoria, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Auditoria $auditoria)
    {
        $auditoria->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
