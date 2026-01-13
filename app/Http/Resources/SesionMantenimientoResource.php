<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SesionMantenimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha->toDateTimeString(),
            'horas_trabajadas' => $this->horas_trabajadas,
            'descripcion_trabajo' => $this->descripcion_trabajo,
            'observaciones' => $this->observaciones,
            'costo_hora' => $this->costo_hora,
            // Relaciones cargadas de forma condicional
            'mantenimiento' => new MantenimientoResource($this->whenLoaded('mantenimiento')),
            'tecnico' => new UserResource($this->whenLoaded('tecnico')),
            // Aquí cargamos los repuestos que Franklyn registró
            'repuestos_usados' => RepuestoUsadoResource::collection($this->whenLoaded('repuestosUtilizados')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
