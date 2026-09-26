<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Applications\StatusEnum;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'api';

    public $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

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
            'mobile_verified_at' => 'datetime',
            'whatsapp_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => StatusEnum::class,
            'is_employee' => 'boolean',
        ];
    }

    public function scopeEmployee($query, $value = 1)
    {
        return $query->where('is_employee', $value);
    }

    public function getProfilePercentageAttribute(): int
    {
        $point = 0;
        $point += ! empty($this->mobile) && ! empty($this->mobile_verified_at) ? 10 : 0;
        $point += ! empty($this->email) && ! empty($this->email_verified_at) ? 10 : 0;
        $point += ! empty($this->whatsapp) && ! empty($this->whatsapp_verified_at) ? 10 : 0;

        if ($this->addressBooks()->verified()->exists()) {
            $point += 20;
        } elseif ($this->addressBooks()->exists()) {
            $point += 10;
        }

        if ($this->userIdentities()->exists()) {
            $point += 20;
        }

        return ($point * 100) / 100;
    }

    public static function generateUniqueId(int $length = 10): string
    {
        do {
            $uniqueKey = Str::random($length);
        } while (self::where('unique_id', $uniqueKey)->exists());

        return $uniqueKey;
    }

    public function findForPassport(string $uniqueKey): User
    {
        return $this->where('unique_id', $uniqueKey)->first();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return $this->firstParty();
    }

    public function phoneBooks()
    {
        return $this->hasMany(PhoneBook::class);
    }

    public function userIdentities()
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function addressBooks(): HasMany
    {
        return $this->hasMany(AddressBook::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }



}
