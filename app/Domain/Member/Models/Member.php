<?php

namespace App\Domain\Member\Models;

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Database\Factories\MemberFactory;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property int|null $continent_id
 * @property int|null $region_id
 * @property bool $is_active
 * @property bool $is_banned
 * @property-read int|null $following_count
 * @property-read int|null $followers_count
 * @property int $feed_posts_count
 * @property int $feed_posts_thumbs_up_count
 * @property int $feed_posts_thumbs_down_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Member\Models\MemberAccessLog> $accessLogs
 * @property-read int|null $access_logs_count
 * @property-read \App\Domain\Member\Models\MemberAvatar|null $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Member\Models\MemberAvatar> $avatars
 * @property-read int|null $avatars_count
 * @property-read GeoContinent|null $continent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Member\Models\MemberFollower> $followers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Member\Models\MemberFollower> $following
 * @property-read \App\Domain\Member\Models\MemberAccessLog|null $latestAccessLog
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Domain\Member\Models\MemberProfile|null $profile
 * @property-read GeoRegion|null $region
 * @property-read User $user
 * @method static \App\Domain\Member\Database\Factories\MemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereContinentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFeedPostsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFeedPostsThumbsDownCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFeedPostsThumbsUpCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFollowersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFollowingCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereIsBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUserId($value)
 * @mixin \Eloquent
 */
class Member extends Model
{
    /** @use HasFactory<\App\Domain\Member\Models\MemberFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'continent_id',
        'region_id',
        'is_active',
        'is_banned',
        'feed_posts_count',
        'feed_posts_thumbs_up_count',
        'feed_posts_thumbs_down_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * On creating register auto-generated values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique 9-digit integer UID
            do {
                $uid = random_int(100000, 999999); // 6 digits
            } while (self::where('uid', $uid)->exists());

            $model->uid = $uid;
        });
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id', 'user_id');
    }

    public function accessLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            MemberAccessLog::class,
            User::class,
            'id',        // Foreign key on users table...
            'user_id',   // Foreign key on member_access_logs table...
            'user_id',   // Local key on members table...
            'id'         // Local key on users table...
        );
    }

    public function latestAccessLog(): HasOneThrough
    {
        return $this->hasOneThrough(
            MemberAccessLog::class,
            User::class,
            'id',        // Foreign key on users table...
            'user_id',   // Foreign key on admins_access_logs table...
            'user_id',   // Local key on admins table...
            'id'         // Local key on users table...
        )->orderByDesc('members_access_logs.created_at');
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(GeoContinent::class, 'continent_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(GeoRegion::class, 'region_id', 'id');
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(MemberAvatar::class, 'user_id', 'user_id')
            ->where('is_selected', true);
    }

    public function avatars(): HasMany
    {
        return $this->hasMany(MemberAvatar::class, 'user_id', 'user_id')
            ->orderBy('position', 'asc');
    }

    public function following(): HasMany
    {
        return $this->hasMany(MemberFollower::class, 'user_id', 'user_id')
            ->orderBy('created_at', 'asc');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(MemberFollower::class, 'user_id', 'following_user_id')
            ->orderBy('created_at', 'asc');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'user_id')
            ->orderBy('created_at', 'asc');
    }
}
