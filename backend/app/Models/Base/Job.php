<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\ModelQuery;

class Job extends ActiveRecord
{
    protected string $table = 'jobs';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'queue', 'payload', 'attempts', 'reserved_at', 'available_at'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'unix'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'unix';


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
            
            ->where($conditions);
    }
}