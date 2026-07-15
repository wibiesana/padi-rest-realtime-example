<?php

namespace App\Models;

use App\Models\Base\Comment as BaseComment;
use Wibiesana\Padi\Core\Realtime;

class Comment extends BaseComment
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
        $event = $insert ? 'comment_created' : 'comment_updated';
        Realtime::publish('comments', [
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
        Realtime::publish('comments', [
            'event' => 'comment_deleted',
            'id'    => $id
        ]);
    }
}
