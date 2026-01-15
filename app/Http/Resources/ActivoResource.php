<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivoResource extends JsonResource
{
    /**
     * Transform the resource into an array. 
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estado' => $this->estado, // Laravel serializa el Enum automáticamente
            'valor' => $this->valor,
            // Relaciones: Solo se cargan si están presentes para optimizar rendimiento
            'articulo' => new ArticuloResource($this->whenLoaded('articulo')),
                //Codigo Original:
            //'ubicacion' => new UbicacionResource($this->whenLoaded('ubicacion')),
                //Codigo Modificado para hacer pruebas, 
                //ya que no existe el UbicacionResource en mi rama:
            'ubicacion_detalle' => $this->whenLoaded('ubicacion'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
