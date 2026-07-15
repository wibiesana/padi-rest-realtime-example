<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\ModelQuery;
use Wibiesana\Padi\Core\Queue;
use App\Jobs\BroadcastRealtimeJob;

class PostTag extends ActiveRecord
{
    protected string $table = 'post_tags';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'post_id', 'tag_id'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, created_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';


    public function post()
    {
        return $this->belongsTo(\App\Models\Post::class, 'post_id');
    }

    public function tag()
    {
        return $this->belongsTo(\App\Models\Tag::class, 'tag_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
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
            $conditions[] = ['posts.title', 'LIKE', $keyword];
            $conditions[] = ['tags.name', 'LIKE', $keyword];
            $conditions[] = ['users.username', 'LIKE', $keyword];

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
            ->leftJoin('posts AS posts', 'post_tags.post_id = posts.id')
            ->leftJoin('tags AS tags', 'post_tags.tag_id = tags.id')
            ->leftJoin('users AS users', 'post_tags.created_by = users.id')
            ->where($conditions);
    }


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