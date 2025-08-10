<?php

// File: app/Models/Concerns/BelongsToCompany.php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * The "booted" method of the model.
     * This method automatically applies the TenantScope to the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        // Automatically set company_id when creating a new record.
        // The IdentifyCompany middleware ensures tenant() is available.
        static::creating(function ($model) {
            if (tenant()) {
                $model->company_id = tenant('id');
            }
        });
    }

    /**
     * Defines the relationship to the Company model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
