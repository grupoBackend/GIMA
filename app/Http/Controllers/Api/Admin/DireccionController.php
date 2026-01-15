<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DireccionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $direcciones = Direccion::with(['ubicaciones', 'repuestos'])->get();
        return response()->json($direcciones, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'estado' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'sector' => 'nullable|string|max:100',
            'calle'  => 'nullable|string|max:255',
            'sede'   => 'nullable|string|max:100',
        ]);

        $direccion = Direccion::create($data);

        return response()->json($direccion, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Direccion $direccion)
    {
        $direccion->load(['ubicaciones', 'repuestos']);
        return response()->json($direccion, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Direccion $direccion)
    {
        $data = $request->validate([
            'estado' => 'sometimes|required|string|max:100',
            'ciudad' => 'sometimes|required|string|max:100',
            'sector' => 'nullable|string|max:100',
            'calle'  => 'nullable|string|max:255',
            'sede'   => 'nullable|string|max:100',
        ]);

        $direccion->update($data);

        return response()->json($direccion, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Direccion $direccion)
    {
        $direccion->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
