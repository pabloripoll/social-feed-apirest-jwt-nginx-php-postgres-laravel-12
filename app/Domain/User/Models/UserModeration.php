<?php

namespace App\Domain\User\Models;

use App\Domain\Admin\Models\Admin;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Member\Models\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModeration extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserModeration> */
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
        return \App\Domain\User\Database\Factories\UserModerationFactory::new();
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
