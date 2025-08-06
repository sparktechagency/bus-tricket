<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant;

class Company extends Model implements Tenant
{
    protected $gruarded = ['id'];


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
