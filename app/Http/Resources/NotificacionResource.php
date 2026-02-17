<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Determinar si es una notificación de Laravel o del modelo personalizado
        $esLaravelNotification = isset($this->data) || $this->resource instanceof \Illuminate\Notifications\DatabaseNotification;
        
        if ($esLaravelNotification) {
            // Es una notificación del sistema de Laravel
            $data = $this->data;
            
            return [
                'id' => $this->id,
                'tipo' => class_basename($this->type ?? 'Notificacion'),
                'contenido' => $data['mensaje'] ?? $data['message'] ?? $data['contenido'] ?? 'Sin contenido',
                'leido' => !is_null($this->read_at),
                'leido_at' => $this->read_at?->toDateTimeString(),
                'data' => $data, // Datos completos de la notificación
                'reporte_id' => $data['reporte_id'] ?? null,
                'usuario_id' => $data['usuario_id'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'prioridad' => $data['prioridad'] ?? null,
                'activo_id' => $data['activo_id'] ?? null,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ];
        } else {
            // Es una notificación del modelo personalizado (para compatibilidad)
            return [
                'id' => $this->id,
                'tipo' => 'Personalizada',
                'contenido' => $this->contenido,
                'leido' => $this->leido ?? false,
                'leido_at' => $this->leido_at,
                'data' => ['contenido' => $this->contenido],
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ];
        }
    }
}