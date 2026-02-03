<?php

namespace App\Domain\Admin\Models;

use App\Domain\Admin\Database\Factories\AdminAvatarFactory;
use App\Domain\User\Models\User;
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
 * @property-read \App\Domain\Admin\Models\Admin $admin
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereIsSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAvatar whereUserId($value)
 * @mixin \Eloquent
 */
class AdminAvatar extends Model
{
    /** @use HasFactory<\App\Domain\Admin\Database\Factories\AdminAvatar> */
    // use HasFactory;

    /**
     * @var string
     */
    protected $table = 'admins_avatars';

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
        return AdminAvatarFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id', 'user_id');
    }
}
