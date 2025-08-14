<?php
namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory, BelongsToCompany;
    protected $guarded = ['id'];

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function fares(): HasMany
    {
        return $this->hasMany(Fare::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
