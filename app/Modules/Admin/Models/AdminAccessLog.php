<?php

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Database\Factories\AdminAccessLogFactory;
use App\Modules\User\Models\User;
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
 * @method static \App\Modules\Admin\Database\Factories\AdminAccessLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereIsExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereIsTerminated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereRefreshCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereRequestsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAccessLog whereUserId($value)
 * @mixin \Eloquent
 */
class AdminAccessLog extends Model
{
    /** @use HasFactory<\App\Modules\Admin\Database\Factories\AdminAccessLog> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'admins_access_logs';

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
        return AdminAccessLogFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
