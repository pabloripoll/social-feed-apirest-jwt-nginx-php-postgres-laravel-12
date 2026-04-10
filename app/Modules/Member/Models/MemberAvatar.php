<?php

namespace App\Modules\Member\Models;

use App\Modules\Member\Database\Factories\MemberAvatarFactory;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property bool $is_selected
 * @property int $position
 * @property string $extension
 * @property string $path
 * @property string $name
 * @property string $title
 * @property string $slug
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Modules\Member\Models\Member $member
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereIsSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAvatar whereUserId($value)
 * @mixin \Eloquent
 */
class MemberAvatar extends Model
{
    /** @use HasFactory<\App\Modules\Member\Database\Factories\MemberAvatar> */
    // use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_avatars';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'is_selected',
        'position',
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
        return MemberAvatarFactory::new();
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
}
