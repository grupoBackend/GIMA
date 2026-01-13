<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UbicacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre, // O el campo que uses para el nombre (ej. 'piso', 'sala')
            'descripcion' => $this->descripcion,
            // Si la ubicación pertenece a una dirección (Sede), la cargamos aquí
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}