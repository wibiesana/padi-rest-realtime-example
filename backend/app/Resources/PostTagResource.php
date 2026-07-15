<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class PostTagResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'tag_id' => $this->tag_id,

            // Relations
            'post' => $this->whenLoaded('post'),
            'tag' => $this->whenLoaded('tag'),
            'createdBy' => $this->whenLoaded('createdBy'),

            // Flattened Fields
            'post_name' => $this->post['title'] ?? null,
            'tag_name' => $this->tag['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,

        ];
    }
}