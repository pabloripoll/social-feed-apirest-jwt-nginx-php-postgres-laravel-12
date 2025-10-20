<?php

namespace App\Domain\Member\Models;

use App\Domain\User\Models\User;
use App\Domain\Member\Database\Factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberProfileFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_profile';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nickname',
        'avatar',
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
        return MemberProfileFactory::new();
    }

    /**
     * Relations
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
