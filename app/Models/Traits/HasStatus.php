<?php

namespace App\Models\Traits;

use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Boot the trait.
     */
    public static function bootHasStatus(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.status', '!=', Status::DELETED);
        });
    }

    /**
     * Scope to include deleted records.
     */
    public function scopeWithDeleted(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('notDeleted');
    }

    /**
     * Scope to only deleted records.
     */
    public function scopeOnlyDeleted(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('notDeleted')->where($builder->getModel()->getTable() . '.status', Status::DELETED);
    }

    /**
     * Scope to active records only.
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where($builder->getModel()->getTable() . '.status', Status::ACTIVE);
    }

    /**
     * Scope to inactive records only.
     */
    public function scopeInactive(Builder $builder): Builder
    {
        return $builder->where($builder->getModel()->getTable() . '.status', Status::INACTIVE);
    }

    /**
     * Soft delete the model.
     */
    public function softDelete(): bool
    {
        return $this->update(['status' => Status::DELETED]);
    }

    /**
     * Restore a soft deleted model.
     */
    public function restore(): bool
    {
        return $this->update(['status' => Status::ACTIVE]);
    }

    /**
     * Check if the model is active.
     */
    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    /**
     * Check if the model is deleted.
     */
    public function isDeleted(): bool
    {
        return $this->status === Status::DELETED;
    }
}
