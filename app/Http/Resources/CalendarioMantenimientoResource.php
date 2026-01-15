<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarioMantenimientoResource extends JsonResource
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
            'fecha_programada' => $this->fecha_programada->toDateTimeString(),
            'estado' => $this->estado,
            'activo' => new ActivoResource($this->whenLoaded('activo')),
            'tecnico_asignado' => new UserResource($this->whenLoaded('tecnicoAsignado')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
