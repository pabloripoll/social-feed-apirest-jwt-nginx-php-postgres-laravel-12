<?php

namespace App\Modules\User\Models;

use App\Modules\Admin\Models\Admin;
use App\Modules\Feed\Models\FeedPost;
use App\Modules\Member\Models\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property int|null $reporter_user_id
 * @property int|null $moderator_user_id
 * @property bool $is_opened
 * @property bool $in_review
 * @property \Illuminate\Support\Carbon|null $in_review_since
 * @property bool $is_resolved
 * @property string|null $resolved_at
 * @property bool $is_closed
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int $category_id
 * @property int|null $sanction_id
 * @property bool $has_sanction_active
 * @property \Illuminate\Support\Carbon|null $sanction_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $feed_post_id
 * @property-read \App\Modules\User\Models\UserModerationCategory $category
 * @property-read FeedPost|null $feedPost
 * @property-read Member $member
 * @property-read \App\Modules\User\Models\User|null $moderator
 * @property-read Admin|null $moderatorAdmin
 * @property-read \App\Modules\User\Models\User|null $reporter
 * @property-read Member|null $reporterMember
 * @property-read \App\Modules\User\Models\UserModerationSanction|null $sanction
 * @property-read \App\Modules\User\Models\User $user
 * @method static \App\Modules\User\Database\Factories\UserModerationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereFeedPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereHasSanctionActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereInReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereInReviewSince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereIsClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereIsOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereIsResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereModeratorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereReporterUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereSanctionExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereSanctionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModeration whereUserId($value)
 * @mixin \Eloquent
 */
class UserModeration extends Model
{
    /** @use HasFactory<\App\Modules\User\Database\Factories\UserModerationFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_moderations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'reporter_user_id',
        'moderator_user_id',
        'is_opened',
        'in_review',
        'in_review_since',
        'is_resolved',
        'resolved_at',
        'is_closed',
        'closed_at',
        'category_id',
        'sanction_id',
        'has_sanction_active',
        'sanction_expires_at',
        'feed_post_id',
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
        return [
            'in_review_since' => 'datetime',
            'closed_at' => 'datetime',
            'sanction_expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Factory
     */
    public static function newFactory()
    {
        return \App\Modules\User\Database\Factories\UserModerationFactory::new();
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
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function reporterMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'reporter_user_id', 'user_id');
    }

    public function moderatorAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'moderator_user_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(UserModerationCategory::class, 'category_id');
    }

    public function sanction(): BelongsTo
    {
        return $this->belongsTo(UserModerationSanction::class, 'sanction_id');
    }

    public function feedPost(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'feed_post_id');
    }
}
