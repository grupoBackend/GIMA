<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DireccionResource extends JsonResource
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
            'estado' => $this->estado,
            'ciudad' => $this->ciudad,
            'sector' => $this->sector,
            'calle' => $this->calle,
            'sede' => $this->sede,
            // Concatenación útil para mostrar en selectores del frontend
            'direccion_completa' => "{$this->sede} - {$this->ciudad}, {$this->estado}",

            // Relaciones condicionales (Asumiendo que crearás UbicacionResource y RepuestoResource luego)
            'ubicaciones' => UbicacionResource::collection($this->whenLoaded('ubicaciones')),
            'repuestos' => RepuestoResource::collection($this->whenLoaded('repuestos')),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
