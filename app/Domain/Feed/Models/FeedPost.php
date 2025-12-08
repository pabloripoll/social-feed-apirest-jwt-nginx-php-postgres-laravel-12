<?php

namespace App\Domain\Feed\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPost extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\PostFactory> */
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
        'is_active',
        'is_draft',
        'is_banned',
        'reports_count',
        'thumbs_up_count',
        'thumbs_down_count',
        'favorites_count',
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
     * Relations
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FeedCategory::class, 'category_id', 'id');
    }
}
