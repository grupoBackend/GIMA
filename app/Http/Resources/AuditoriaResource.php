<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditoriaResource extends JsonResource
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
            'entidad' => $this->entidad,
            'entidad_id' => $this->entidad_id,
            'accion' => $this->accion,
            'descripcion' => $this->descripcion,
            'fecha' => $this->fecha->toDateTimeString(),
            'usuario' => new UserResource($this->whenLoaded('usuario')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
