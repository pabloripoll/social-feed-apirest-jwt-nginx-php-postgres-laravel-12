<?php

namespace App\Modules\Feed\Models;

use App\Modules\Feed\Database\Factories\FeedPostFactory;
use App\Modules\Geo\Models\GeoContinent;
use App\Modules\Geo\Models\GeoRegion;
use App\Modules\Member\Models\Member;
use App\Modules\Member\Models\MemberAvatar;
use App\Modules\Member\Models\MemberProfile;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property int|null $continent_id
 * @property int|null $region_id
 * @property int|null $category_id
 * @property bool $is_sketch
 * @property bool $is_draft
 * @property bool $is_active
 * @property bool $is_banned
 * @property int $reports_count
 * @property int $thumbs_up_count
 * @property int $thumbs_down_count
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $summary
 * @property string|null $article
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MemberAvatar|null $avatar
 * @property-read \App\Modules\Feed\Models\FeedCategory|null $category
 * @property-read GeoContinent|null $continent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Feed\Models\FeedMedia> $media
 * @property-read int|null $media_count
 * @property-read Member $member
 * @property-read MemberProfile $profile
 * @property-read GeoRegion|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Feed\Models\FeedPostThumb> $thumbs
 * @property-read int|null $thumbs_count
 * @property-read User $user
 * @method static \App\Modules\Feed\Database\Factories\FeedPostFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereContinentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereIsBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereIsSketch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereReportsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereThumbsDownCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereThumbsUpCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPost whereUserId($value)
 * @mixin \Eloquent
 */
class FeedPost extends Model
{
    /** @use HasFactory<\App\Modules\Feed\Database\Factories\FeedPostFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'region_id',
        'category_id',
        'is_sketch',
        'is_draft',
        'is_active',
        'is_banned',
        'reports_count',
        'thumbs_up_count',
        'thumbs_down_count',
        'title',
        'slug',
        'summary',
        'article',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     * Casts for DB columns + calculated boolean flags we will select in query
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sketch' => 'boolean',
            'is_draft' => 'boolean',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'reports_count' => 'integer',
            'thumbs_up_count' => 'integer',
            'thumbs_down_count' => 'integer',

            // These attributes will be added by the SELECT subqueries in controller.
            'is_thumb_up_by_me' => 'boolean',
            'is_thumb_down_by_me' => 'boolean',
            'is_post_from_following' => 'boolean', // auth user follows post owner
            'is_post_from_follower' => 'boolean',  // post owner follows auth user
        ];
    }

    /**
     * On creating register auto-generated values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // If caller already supplied a uid, keep it
            if (! empty($model->uid)) {
                return;
            }
            // Generate a unique 7-digit integer UID
            do {
                $uid = random_int(1000000, 9999999);
            } while (self::where('uid', $uid)->exists());

            $model->uid = $uid;
        });
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return FeedPostFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(MemberProfile::class, 'user_id', 'user_id');
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(MemberAvatar::class, 'user_id', 'user_id')
            ->where('is_selected', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeedCategory::class, 'category_id', 'id');
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(GeoContinent::class, 'continent_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(GeoRegion::class, 'region_id', 'id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(FeedMedia::class, 'post_id');
    }

    // All thumbs for this post
    public function thumbs(): HasMany
    {
        return $this->hasMany(FeedPostThumb::class, 'post_id');
    }

    // Convenience: to eager-load the current user's thumb, constrain in the query:
    // FeedPost::with(['thumbs' => fn($q) => $q->where('user_id', $currentUserId)])
    // or use the controller subselect approach shown below.
}
