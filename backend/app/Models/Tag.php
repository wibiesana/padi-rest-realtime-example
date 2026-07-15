<?php

namespace App\Models;

use App\Models\Base\Tag as BaseTag;
use Wibiesana\Padi\Core\Realtime;

class Tag extends BaseTag
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
        $event = $insert ? 'tag_created' : 'tag_updated';
        Realtime::publish('tags', [
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
        Realtime::publish('tags', [
            'event' => 'tag_deleted',
            'id'    => $id
        ]);
    }
}
