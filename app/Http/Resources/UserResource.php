<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
            'fecha_aprobacion' => $this->fecha_aprobacion ? $this->fecha_aprobacion->toDateTimeString() : null,
            // Carga los roles solo si se han precargado con with('roles')
            'roles' => $this->whenLoaded('roles', function () {
                return $this->getRoleNames();
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}