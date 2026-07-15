<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class PostResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'views' => $this->views,

            // Relations
            'user' => $this->whenLoaded('user'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'user_name' => $this->user['username'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}