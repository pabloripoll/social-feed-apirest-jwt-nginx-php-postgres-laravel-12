<?php

namespace App\Models;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberAccessLog;
use App\Domain\Member\Models\MemberActivationCode;
use App\Domain\Member\Models\MemberProfile;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'email',
        'password',
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
        ];
    }

    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    /**
     * Relations
     */
    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'role');
    }

    public function activationCode(): HasOne
    {
        return $this->hasOne(MemberActivationCode::class, 'user_id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'user_id');
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class, 'user_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(MemberAccessLog::class, 'user_id');
    }
}
