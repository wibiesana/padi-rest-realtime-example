<?php

namespace App\Models;

use App\Models\Base\PostTag as BasePostTag;
use Wibiesana\Padi\Core\Queue;
use App\Jobs\BroadcastRealtimeJob;

class PostTag extends BasePostTag
{
    /**
     * Override methods here to add custom logic.
     * Use beforeSave(), afterSave(), etc. for lifecycle hooks.
     */

    /**
     * Lifecycle Hook: Called after save (create/update)
     * Automatically broadcasts changes via background queue.
     */
    protected function afterSave(bool $insert, array $data): void
    {
        $event = $insert ? 'posttag_created' : 'posttag_updated';
        Queue::push(BroadcastRealtimeJob::class, [
            'topic' => 'posttags',
            'data' => [
                'event' => $event,
                'data'  => $data
            ]
        ]);
    }

    /**
     * Lifecycle Hook: Called after delete
     * Automatically broadcasts deletion via background queue.
     */
    protected function afterDelete(int|string|array $id): void
    {
        Queue::push(BroadcastRealtimeJob::class, [
            'topic' => 'posttags',
            'data' => [
                'event' => 'posttag_deleted',
                'id'    => $id
            ]
        ]);
    }
}
