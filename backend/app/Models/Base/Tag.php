<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\ModelQuery;
use Wibiesana\Padi\Core\Realtime;

class Tag extends ActiveRecord
{
    protected string $table = 'tags';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'slug', 'description'
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


    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function posttagBytag()
    {
        return $this->hasOne(\App\Models\PostTag::class, 'tag_id');
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
            ->leftJoin('users AS users', 'tags.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'tags.updated_by = users_updated_by.id')
            ->where($conditions);
    }


    /**
     * Lifecycle Hook: Called after save (create/update)
     * Automatically broadcasts changes via Mercure real-time hub.
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
     * Automatically broadcasts deletion via Mercure real-time hub.
     */
    protected function afterDelete(int|string|array $id): void
    {
        Realtime::publish('tags', [
            'event' => 'tag_deleted',
            'id'    => $id
        ]);
    }
}