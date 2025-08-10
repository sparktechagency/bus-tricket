<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // First, check if a user is authenticated and is a SuperAdmin.
        if (Auth::check() && Auth::user()->hasRole('SuperAdmin')) {
            return;
        }

        // For all other cases, use our new tenant() helper function.
        // The IdentifyCompany middleware has already done the hard work of finding the tenant.
        if (tenant()) {
            $builder->where($model->getTable() . '.company_id', tenant('id'));
        }
    }
}
