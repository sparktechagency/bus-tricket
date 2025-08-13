<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use BelongsToCompany;
    protected $guarded = ['id'];

    //relationships with route and company
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
