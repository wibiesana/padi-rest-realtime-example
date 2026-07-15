<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class CommentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'status' => $this->status,

            // Relations
            'post' => $this->whenLoaded('post'),
            'user' => $this->whenLoaded('user'),
            'parent' => $this->whenLoaded('parent'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'post_name' => $this->post['title'] ?? null,
            'user_name' => $this->user['username'] ?? null,
            'parent_name' => $this->parent['id'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}