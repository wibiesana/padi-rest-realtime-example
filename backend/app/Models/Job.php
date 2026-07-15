<?php

namespace App\Models;

use App\Models\Base\Job as BaseJob;
use Wibiesana\Padi\Core\Realtime;

class Job extends BaseJob
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
        $event = $insert ? 'job_created' : 'job_updated';
        Realtime::publish('jobs', [
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
        Realtime::publish('jobs', [
            'event' => 'job_deleted',
            'id'    => $id
        ]);
    }
}
