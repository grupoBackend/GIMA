<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MantenimientoResource extends JsonResource
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
            'estado' => $this->estado,
            'fecha_apertura' => $this->fecha_apertura->toDateTimeString(),
            'fecha_cierre' => $this->fecha_cierre ? $this->fecha_cierre->toDateTimeString() : null,
            'descripcion' => $this->descripcion,
            'validado' => $this->validado,
            'costo_total' => $this->costo_total,
            'activo' => new ActivoResource($this->whenLoaded('activo')),
            'supervisor' => new UserResource($this->whenLoaded('supervisor')),
            'tecnico_principal' => new UserResource($this->whenLoaded('tecnicoPrincipal')),
            'reporte' => new ReporteResource($this->whenLoaded('reporte')),
            'sesiones' => SesionMantenimientoResource::collection($this->whenLoaded('sesiones')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
