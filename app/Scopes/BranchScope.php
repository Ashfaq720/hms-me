<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope: restrict queries to the authenticated user's current branch.
 *
 * Users with no branch context (e.g. superadmin without a branch selected)
 * see all rows. Headless contexts (CLI/jobs) also see all rows so seeders
 * and background jobs can operate across branches.
 */
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();
        if (empty($user->current_branch_id)) {
            return;
        }

        $table = $model->getTable();
        $builder->where($table . '.branch_id', $user->current_branch_id);
    }
}
