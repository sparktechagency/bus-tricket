<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCompany;


class Driver extends Model
{
    use HasFactory, BelongsToCompany;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'license_expiry_date' => 'date',
            'date_of_birth' => 'date',
            'rating' => 'decimal:2',
        ];
    }
    //hidden attributes
    protected $hidden = [
        'pin_code', // Sensitive information, should not be exposed
    ];

    /**
     * Get the user that owns the driver.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
