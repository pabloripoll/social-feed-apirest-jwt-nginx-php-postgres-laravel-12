<?php

namespace App\Domain\Member\Models;

use App\Domain\Member\Database\Factories\MemberAccessLogFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $is_terminated
 * @property bool $is_expired
 * @property \Illuminate\Support\Carbon $expires_at
 * @property int $refresh_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $requests_count
 * @property array<array-key, mixed>|null $payload
 * @property string $token
 * @property-read User $user
 * @method static \App\Domain\Member\Database\Factories\MemberAccessLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereIsExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereIsTerminated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereRefreshCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereRequestsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccessLog whereUserId($value)
 * @mixin \Eloquent
 */
class MemberAccessLog extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberAccessLog> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_access_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'is_expired',
        'expires_at',
        'refresh_count',
        'ip_address',
        'user_agent',
        'payload',
        'requests_count',
        'payload',
        'token',
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
            'payload' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberAccessLogFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
