<?php

namespace App\Modules\Feed\Models;

use App\Modules\Feed\Database\Factories\FeedMediaFactory;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property int $post_id
 * @property int $position
 * @property string $type type of file
 * @property string $extension
 * @property string $path
 * @property string $name
 * @property string $title
 * @property string $slug
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Modules\Feed\Models\FeedPost $post
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedMedia whereUserId($value)
 * @mixin \Eloquent
 */
class FeedMedia extends Model
{
    /** @use HasFactory<\App\Modules\Feed\Database\Factories\FeedMedia> */
    // use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_media';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'position',
        'type',
        'extension',
        'path',
        'name',
        'title',
        'url',
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
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return FeedMediaFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }
}
