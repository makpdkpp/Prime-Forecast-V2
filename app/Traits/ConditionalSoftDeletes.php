<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

/**
 * A drop-in replacement for SoftDeletes that only applies the
 * deleted_at scope if the column actually exists in the database.
 * This prevents errors when code is deployed before migrations run.
 *
 * Once migrations have been run and the deleted_at column exists,
 * this behaves identically to the standard SoftDeletes trait.
 */
trait ConditionalSoftDeletes
{
    /**
     * Cache for column existence check per table.
     */
    protected static array $softDeleteColumnCache = [];

    /**
     * Boot the trait — only register the SoftDeletingScope if the column exists.
     */
    public static function bootConditionalSoftDeletes(): void
    {
        if (static::isSoftDeleteSupported()) {
            static::addGlobalScope(new SoftDeletingScope);
        }
    }

    /**
     * Initialize the trait on each model instance.
     */
    public function initializeConditionalSoftDeletes(): void
    {
        if (!static::isSoftDeleteSupported()) {
            return;
        }

        if (!isset($this->casts[$this->getDeletedAtColumn()])) {
            $this->casts[$this->getDeletedAtColumn()] = 'datetime';
        }
    }

    /**
     * Perform the actual delete (soft or hard depending on column existence).
     */
    public function performDeleteOnModel()
    {
        if (static::isSoftDeleteSupported()) {
            $this->runSoftDelete();
            return;
        }

        $this->newModelQuery()->where($this->getKeyName(), $this->getKey())->delete();
        $this->exists = false;
    }

    /**
     * Run the soft delete on the model.
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();
        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->usesTimestamps() && !is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
        $this->fireModelEvent('trashed', false);
    }

    /**
     * Restore a soft-deleted model instance.
     */
    public function restore()
    {
        if (!static::isSoftDeleteSupported()) {
            return false;
        }

        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = null;
        $this->exists = true;
        $result = $this->save();
        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     */
    public function trashed()
    {
        if (!static::isSoftDeleteSupported()) {
            return false;
        }

        return !is_null($this->{$this->getDeletedAtColumn()});
    }

    /**
     * Determine if the model is currently force deleting.
     */
    public function isForceDeleting()
    {
        return $this->forceDeleting ?? false;
    }

    /**
     * Get the name of the "deleted at" column.
     */
    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }

    /**
     * Get the fully qualified "deleted at" column.
     */
    public function getQualifiedDeletedAtColumn()
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }

    /**
     * Check if the deleted_at column exists, with caching.
     */
    protected static function isSoftDeleteSupported(): bool
    {
        $ref = new \ReflectionClass(static::class);
        $prop = $ref->getProperty('table');
        $prop->setAccessible(true);
        $table = $prop->getDefaultValue();

        if (!isset(static::$softDeleteColumnCache[$table])) {
            try {
                static::$softDeleteColumnCache[$table] = Schema::hasColumn($table, 'deleted_at');
            } catch (\Exception $e) {
                static::$softDeleteColumnCache[$table] = false;
            }
        }

        return static::$softDeleteColumnCache[$table];
    }
}
