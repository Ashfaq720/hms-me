<?php

namespace App\Traits;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

/**
 * Auto-scope a model's queries to the current user's branch.
 *
 * Attach to any model whose table has organization_id + branch_id columns.
 * Saves automatically apply the current branch context unless already set.
 */
trait BranchScoped
{
    public static function bootBranchScoped(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function (Model $model) {
            if (! auth()->check()) {
                return;
            }
            $user = auth()->user();

            if (empty($model->getAttribute('organization_id')) && $user->current_organization_id) {
                $model->setAttribute('organization_id', $user->current_organization_id);
            }
            if (empty($model->getAttribute('branch_id')) && $user->current_branch_id) {
                $model->setAttribute('branch_id', $user->current_branch_id);
            }
        });
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        if ($branchId === null) {
            return $query;
        }
        return $query->where($this->getTable() . '.branch_id', $branchId);
    }

    public function scopeForOrganization($query, ?int $orgId)
    {
        if ($orgId === null) {
            return $query;
        }
        return $query->where($this->getTable() . '.organization_id', $orgId);
    }
}
