<?php

namespace App\Models;

use App\Models\Base\Post as BasePost;
use Wibiesana\Padi\Core\Realtime;

class Post extends BasePost
{
    /**
     * Override methods here to add custom logic.
     * Use beforeSave(), afterSave(), etc. for lifecycle hooks.
     */

    /**
     * Lifecycle Hook: Called after save (create/update)
     * Automatically broadcasts changes instantly.
     */
    protected function afterSave(bool $insert, array $data): void
    {
        $event = $insert ? 'post_created' : 'post_updated';
        Realtime::publish('posts', [
            'event' => $event,
            'data'  => $data
        ]);
    }

    /**
     * Lifecycle Hook: Called after delete
     * Automatically broadcasts deletion instantly.
     */
    protected function afterDelete(int|string|array $id): void
    {
        Realtime::publish('posts', [
            'event' => 'post_deleted',
            'id'    => $id
        ]);
    }
}
