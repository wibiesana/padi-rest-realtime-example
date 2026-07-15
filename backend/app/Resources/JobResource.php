<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class JobResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'queue' => $this->queue,
            'payload' => $this->payload,
            'attempts' => $this->attempts,
            'reserved_at' => $this->reserved_at,
            'available_at' => $this->available_at,

            // Relations

            // Flattened Fields

        ];
    }
}