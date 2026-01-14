<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auditorias = Auditoria::with([]),->get();
        return response()->json($auditorias, Response::HTTP_OK);
    }

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Auditoria $auditoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Auditoria $auditoria)
    {
        //
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


/*class Auditoria extends Model
{
    
    protected $table = 'auditorias';

    protected $fillable = [
        'usuario_id',
        'entidad',
        'entidad_id',
        'accion',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
    
    //Relación inversa con el modelo Usuario 
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
*/

