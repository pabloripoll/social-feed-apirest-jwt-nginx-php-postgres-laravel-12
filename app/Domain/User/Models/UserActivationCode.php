<?php

namespace App\Domain\User\Models;

use App\Domain\User\Database\Factories\UserActivationCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $user_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $code
 * @property-read \App\Domain\User\Models\User|null $user
 * @method static \App\Domain\User\Database\Factories\UserActivationCodeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivationCode whereUserId($value)
 * @mixin \Eloquent
 */
class UserActivationCode extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserActivationCodeFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_activation_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'is_active',
        'code',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * On creating register auto-generated values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique 32 alphanumeric code
            do {
                $code = Str::random(32);
            } while (self::where('code', $code)->exists());

            $model->code = $code;
        });
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return UserActivationCodeFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
