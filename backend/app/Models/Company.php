<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant;
use Spatie\Permission\Traits\HasRoles;

class Company extends Tenant
{
    use HasRoles;

    protected $guarded = ['id'];


    //tenant key name methods
     public function getTenantKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the tenant key for the model.
     *
     * @return mixed
     */
    public function getTenantKey()
    {
        return $this->id;
    }
}
