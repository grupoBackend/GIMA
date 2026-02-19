<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepuestoUsadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cantidad' => $this->cantidad,
            'costo_total' => $this->costo_total,
            'sesion' => new SesionMantenimientoResource($this->whenLoaded('sesion')),
            'repuesto' => new RepuestoResource($this->whenLoaded('repuesto')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
