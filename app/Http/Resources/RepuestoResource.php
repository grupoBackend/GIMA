<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepuestoResource extends JsonResource
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
            'descripcion' => $this->descripcion,
            'codigo' => $this->codigo,
            'stock' => $this->stock,
            'stock_minimo' => $this->stock_minimo,
            'costo' => $this->costo,
            'proveedor' => new ProveedorResource($this->whenLoaded('proveedor')),
            'direccion' => new DireccionResource($this->whenLoaded('direccion')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
