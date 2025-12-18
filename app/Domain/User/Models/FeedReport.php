<?php

namespace App\Domain\Feed\Models;

use App\Domain\User\Models\User;
use App\Domain\User\Models\UserModeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedReport extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedReport> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'type_id',
        'reporter_user_id',
        'reporter_message',
        'in_review',
        'in_review_since',
        'is_closed',
        'closed_at',
        'moderation_id',
        'member_user_id',
        'member_feed_post_id',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * Relations
     */

    public function type(): BelongsTo
    {
        return $this->belongsTo(FeedReportType::class, 'type_id');
    }

    public function moderation(): BelongsTo
    {
        return $this->belongsTo(UserModeration::class, 'moderation_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'member_feed_post_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }
}
