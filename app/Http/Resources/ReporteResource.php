<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReporteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descripcion' => $this->descripcion,
            'prioridad' => $this->prioridad,
            'estado' => $this->estado,
            // Relaciones usando los Resources que ya tienes
            'usuario' => new UserResource($this->whenLoaded('usuario')),
            'activo' => new ActivoResource($this->whenLoaded('activo')),
            // Cargamos los mantenimientos si están presentes (historial)
            'mantenimientos' => MantenimientoResource::collection($this->whenLoaded('mantenimientos')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
