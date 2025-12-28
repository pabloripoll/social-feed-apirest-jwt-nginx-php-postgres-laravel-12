<?php

namespace App\Domain\Feed\Models;

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Models\Member;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\Feed\Database\Factories\FeedPostFactory;
use App\Domain\Member\Models\MemberAvatar;
use App\Domain\Member\Models\MemberProfile;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedPost extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedPostFactory> */
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
