<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\ModelQuery;
use Wibiesana\Padi\Core\Queue;
use App\Jobs\BroadcastRealtimeJob;

class Chat extends ActiveRecord
{
    protected string $table = 'chats';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'sender_id', 'receiver_id', 'message', 'is_read'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, updated_at, created_by, updated_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';


    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Build global search conditions
     * Searches all fillable fields + related table display columns
     */
    protected function buildSearchConditions(string $keyword): array
    {
        $conditions = ['OR'];

        // Search all fillable fields from this table
        foreach ($this->fillable as $field) {
            $conditions[] = ["{$this->table}.{$field}", 'LIKE', $keyword];
        }

            // Search in related tables
            $conditions[] = ['users.username', 'LIKE', $keyword];
            $conditions[] = ['users_receiver_id.username', 'LIKE', $keyword];
            $conditions[] = ['users_created_by.username', 'LIKE', $keyword];
            $conditions[] = ['users_updated_by.username', 'LIKE', $keyword];

        return $conditions;
    }

    /**
     * Start a model-aware search query builder
     */
    public static function search(string $keyword): ModelQuery
    {
        $instance = new static();
        $conditions = $instance->buildSearchConditions("%{$keyword}%");

        return static::find()
            ->select("{$instance->table}.*")
            ->leftJoin('users AS users', 'chats.sender_id = users.id')
            ->leftJoin('users AS users_receiver_id', 'chats.receiver_id = users_receiver_id.id')
            ->leftJoin('users AS users_created_by', 'chats.created_by = users_created_by.id')
            ->leftJoin('users AS users_updated_by', 'chats.updated_by = users_updated_by.id')
            ->where($conditions);
    }


    /**
     * Lifecycle Hook: Called after save (create/update)
     * Automatically broadcasts changes via background queue.
     */
    protected function afterSave(bool $insert, array $data): void
    {
        $event = $insert ? 'chat_created' : 'chat_updated';
        Queue::push(BroadcastRealtimeJob::class, [
            'topic' => 'chats',
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
            'topic' => 'chats',
            'data' => [
                'event' => 'chat_deleted',
                'id'    => $id
            ]
        ]);
    }
}