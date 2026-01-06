<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'phone',
        'slug',
        'public_key',
        'public_slug',
        'social_links',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
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
            'social_links' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'owner_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'owner_id');
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class, 'owner_id');
    }

    public function viewingRequests()
    {
        return $this->hasMany(ViewingRequest::class, 'owner_id');
    }

    public function getShowroomUrlAttribute()
    {
        $key = $this->public_slug ?: $this->public_key;

        return route('showroom.profile', ['key' => $key]);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->public_key)) {
                $user->public_key = (string) Str::uuid();
            }
        });
    }
}
