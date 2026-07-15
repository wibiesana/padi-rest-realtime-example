<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ChatResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message,
            'is_read' => $this->is_read,

            // Relations
            'sender' => $this->whenLoaded('sender'),
            'receiver' => $this->whenLoaded('receiver'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'sender_name' => $this->sender['username'] ?? null,
            'receiver_name' => $this->receiver['username'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}