<?php

namespace App\Domain\User\Models;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Admin\Models\AdminAvatar;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Feed\Models\FeedMedia;
use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberAccessLog;
use App\Domain\Member\Models\MemberAvatar;
use App\Domain\Member\Models\MemberProfile;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property \App\Domain\User\Models\Role|null $role
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $password_changed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\User\Models\UserActivationCode|null $activationCode
 * @property-read Admin|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdminAccessLog> $adminAccessLogs
 * @property-read int|null $admin_access_logs_count
 * @property-read AdminAvatar|null $adminAvatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdminAvatar> $adminAvatars
 * @property-read int|null $admin_avatars_count
 * @property-read AdminProfile|null $adminProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FeedMedia> $feedMedia
 * @property-read int|null $feed_media_count
 * @property-read AdminAccessLog|null $latestAdminAccessLog
 * @property-read MemberAccessLog|null $latestMemberAccessLog
 * @property-read Member|null $member
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MemberAccessLog> $memberAccessLogs
 * @property-read int|null $member_access_logs_count
 * @property-read MemberAvatar|null $memberAvatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MemberAvatar> $memberAvatars
 * @property-read int|null $member_avatars_count
 * @property-read MemberProfile|null $memberProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\User\Models\UserNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \App\Domain\User\Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserFactory> */
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
        'email_verified_at',
        'password_changed_at',
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
            'password_changed_at' => 'datetime',
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
     * Factory
     */
    public static function newFactory()
    {
        return \App\Domain\User\Database\Factories\UserFactory::new();
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
        return $this->hasOne(UserActivationCode::class, 'user_id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'user_id');
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id');
    }

    public function memberAccessLogs(): HasMany
    {
        return $this->hasMany(MemberAccessLog::class, 'user_id');
    }

    public function latestMemberAccessLog(): HasOne
    {
        return $this->hasOne(MemberAccessLog::class, 'user_id')
            ->where([
                'is_terminated' => false,
                'is_expired' => false,
            ])
            ->latestOfMany('created_at');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class, 'user_id');
    }

    public function adminAccessLogs(): HasMany
    {
        return $this->hasMany(AdminAccessLog::class, 'user_id');
    }

    public function latestAdminAccessLog(): HasOne
    {
        return $this->hasOne(AdminAccessLog::class, 'user_id')
            ->where([
                'is_terminated' => false,
                'is_expired' => false,
            ])
            ->latestOfMany('created_at');
    }

    public function feedMedia(): HasMany
    {
        return $this->hasMany(FeedMedia::class, 'user_id');
    }

    public function adminAvatar(): HasOne
    {
        return $this->hasOne(AdminAvatar::class, 'user_id')
            ->where('is_selected', true);
    }

    public function adminAvatars(): HasMany
    {
        return $this->hasMany(AdminAvatar::class, 'user_id')
            ->orderBy('position', 'asc');
    }

    public function memberAvatar(): HasOne
    {
        return $this->hasOne(MemberAvatar::class, 'user_id')
            ->where('is_selected', true);
    }

    public function memberAvatars(): HasMany
    {
        return $this->hasMany(MemberAvatar::class, 'user_id')
            ->orderBy('position', 'asc');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id')
            ->orderBy('created_at', 'asc');
    }
}
