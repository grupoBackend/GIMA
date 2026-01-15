<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class UbicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ubicaciones = Ubicacion::with(['direccion', 'repuestos'])->get();
        return response()->json($ubicaciones, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'edificio' => 'required|string|max:100',
            'piso' => 'required|string|max:100',
            'salon' => 'required|string|max:100',
        ]);

        $ubicacion = Ubicacion::create($data);

        return response()->json($ubicacion, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ubicacion $ubicacion)
    {
        $ubicacion->load(['ubicaciones', 'repuestos']);
        return response()->json($ubicacion, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ubicacion $ubicacion)
    {
        $data = $request->validate([
            'edificio' => 'sometimes|required|string|max:100',
            'piso' => 'sometimes|required|string|max:100',
            'salon' => 'sometimes|required|string|max:100',

        ]);

        $ubicacion->update($data);
        return response()->json($ubicacion, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
