<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_id',
        'id_type',
        'id_number',
        'phone_number',
        'marketing_opt_in',
        'whatsapp_opt_in',
        'phone_opt_in',
        'email_opt_in',
        'sms_opt_in',
        'data_treatment_accepted',
        'terms_accepted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'marketing_opt_in' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
            'phone_opt_in' => 'boolean',
            'email_opt_in' => 'boolean',
            'sms_opt_in' => 'boolean',
            'data_treatment_accepted' => 'boolean',
            'terms_accepted' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }
}
