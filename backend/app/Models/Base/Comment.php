<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\ModelQuery;
use Wibiesana\Padi\Core\Realtime;

class Comment extends ActiveRecord
{
    protected string $table = 'comments';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'post_id', 'user_id', 'parent_id', 'content', 'status'
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


    public function post()
    {
        return $this->belongsTo(\App\Models\Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(\App\Models\Comment::class, 'parent_id');
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
            $conditions[] = ['posts.title', 'LIKE', $keyword];
            $conditions[] = ['users.username', 'LIKE', $keyword];
            $conditions[] = ['comments_parent_id.id', 'LIKE', $keyword];
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
            ->leftJoin('posts AS posts', 'comments.post_id = posts.id')
            ->leftJoin('users AS users', 'comments.user_id = users.id')
            ->leftJoin('comments AS comments_parent_id', 'comments.parent_id = comments_parent_id.id')
            ->leftJoin('users AS users_created_by', 'comments.created_by = users_created_by.id')
            ->leftJoin('users AS users_updated_by', 'comments.updated_by = users_updated_by.id')
            ->where($conditions);
    }


    /**
     * Lifecycle Hook: Called after save (create/update)
     * Automatically broadcasts changes via Mercure real-time hub.
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
     * Automatically broadcasts deletion via Mercure real-time hub.
     */
    protected function afterDelete(int|string|array $id): void
    {
        Realtime::publish('comments', [
            'event' => 'comment_deleted',
            'id'    => $id
        ]);
    }
}