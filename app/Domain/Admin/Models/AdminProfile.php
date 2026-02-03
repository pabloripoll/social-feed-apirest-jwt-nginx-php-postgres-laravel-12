<?php

namespace App\Domain\Admin\Models;

use App\Domain\Admin\Database\Factories\AdminProfileFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $nickname
 * @property string|null $name
 * @property int $age
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \App\Domain\Admin\Database\Factories\AdminProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminProfile whereUserId($value)
 * @mixin \Eloquent
 */
class AdminProfile extends Model
{
    /** @use HasFactory<\App\Domain\Admin\Database\Factories\AdminProfileFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'admins_profile';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nickname',
        'name',
        'age',
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
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return AdminProfileFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
