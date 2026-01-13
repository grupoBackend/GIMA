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
            'edificio' => $this->edificio,
            'piso' => $this->piso,
            'salon' => $this->salon,
            // Si la ubicación pertenece a una dirección (Sede), la cargamos aquí
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
            'activos_count' => $this->whenCounted('activos'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
