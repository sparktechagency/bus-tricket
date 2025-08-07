<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // Use the correct trait here
    use HasFactory, Notifiable, HasRoles, UsesTenantConnection, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'verification_token',
        'otp_expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    //avatar url
    protected function avatar(): Attribute
    {
        return Attribute    ::make(
            get: function (?string $value) {

                if ($value) {
                    return Storage::disk('public')->url($value);
                }
                return 'https://ui-avatars.com/api/?background=random&format=svg&name=' . urlencode($this->name);

            },
        );
    }

    /**
     * A user has one wallet.
     */
    public function wallet()
    {
        return $this->hasOne(\App\Models\PassengerWallet::class);
    }
}
