<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepuestoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            // Si el repuesto está vinculado a un artículo del catálogo
            'articulo' => new ArticuloResource($this->whenLoaded('articulo')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}