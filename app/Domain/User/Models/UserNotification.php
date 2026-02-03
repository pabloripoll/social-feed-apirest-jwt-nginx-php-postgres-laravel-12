<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $uid
 * @property int $type_id
 * @property int $receiver_id
 * @property int|null $performer_id
 * @property int|null $moderation_id
 * @property int|null $communication_id
 * @property int $notify_count
 * @property bool $is_opened
 * @property \Illuminate\Support\Carbon|null $opened_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed> $payload
 * @property-read \App\Domain\User\Models\UserNotificationType $type
 * @property-read \App\Domain\User\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereCommunicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereIsOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereModerationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereNotifyCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification wherePerformerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserNotification extends Model
{
    /**
     * @var string
     */
    protected $table = 'users_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'type_id',
        'receiver_id',
        'performer_id',
        'moderation_id',
        'communication_id',
        'notify_count',
        'is_opened',
        'payload',
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
            'opened_at' => 'datetime',
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
            // Generate a unique 6-digit integer UID
            do {
                $uid = random_int(100000, 999999); // 6 digits
            } while (self::where('uid', $uid)->exists());

            $model->uid = $uid;
        });
    }

    /**
     * Relations
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(UserNotificationType::class, 'type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
