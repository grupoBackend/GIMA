<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticuloResource extends JsonResource
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
            'tipo' => $this->tipo,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'descripcion' => $this->descripcion,
           // Opcional: Mostrar las relaciones si han sido cargadas (Eager Loaded)
            'materiales' => MaterialArticuloResource::collection($this->whenLoaded('materiales')),
            'activos_count' => $this->whenCounted('activos'), // Útil para conteos
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
