<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SesionMantenimientoResource extends JsonResource
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
            'fecha' => $this->fecha->toDateTimeString(),
            'horas_trabajadas' => $this->horas_trabajadas,
            'observaciones' => $this->observaciones,
            'descripcion_trabajo' => $this->descripcion_trabajo,
            'costo_hora' => $this->costo_hora,
            'mantenimiento' => new MantenimientoResource($this->whenLoaded('mantenimiento')),
            'tecnico' => new UserResource($this->whenLoaded('tecnico')),
            'repuestos_utilizados' => RepuestoUsadoResource::collection($this->whenLoaded('repuestosUtilizados')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
