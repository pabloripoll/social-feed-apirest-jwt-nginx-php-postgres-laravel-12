<?php

namespace App\Modules\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserModerationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Load primary relations
        $user = $this->whenLoaded('user');
        $reporter = $this->whenLoaded('reporter');
        $moderator = $this->whenLoaded('moderator');
        $category = $this->whenLoaded('category');
        $sanction = $this->whenLoaded('sanction');
        $feedPost = $this->whenLoaded('feedPost');

        // Load nested relations through parent (FIXED)
        $userMember = $user && $user->relationLoaded('member') ? $user->member : null;
        $userMemberProfile = $user && $user->relationLoaded('memberProfile') ? $user->memberProfile : null;

        $reporterMember = $reporter && $reporter->relationLoaded('member') ? $reporter->member : null;
        $reporterMemberProfile = $reporter && $reporter->relationLoaded('memberProfile') ? $reporter->memberProfile : null;

        $moderatorAdmin = $moderator && $moderator->relationLoaded('admin') ? $moderator->admin : null;
        $moderatorAdminProfile = $moderator && $moderator->relationLoaded('adminProfile') ? $moderator->adminProfile : null;

        return [
            'id' => $this->id,
            'uid' => $this->uid,

            'user' => [
                'uid' => $userMember?->uid,
                'email' => $user?->email,
                'nickname' => $userMemberProfile?->nickname,
            ],

            'reporter' => [
                'uid' => $reporterMember?->uid,
                'email' => $reporter?->email,
                'nickname' => $reporterMemberProfile?->nickname,
            ],

            'moderator' => [
                'uid' => $moderatorAdmin?->uid,
                'email' => $moderator?->email,
                'nickname' => $moderatorAdminProfile?->nickname,
            ],

            'is_opened' => (bool) $this->is_opened,

            'in_review' => (bool) $this->in_review,
            'in_review_since' => $this->in_review_since instanceof \DateTimeInterface
                ? $this->in_review_since->format('Y-m-d H:i:s')
                : $this->in_review_since,

            'is_resolved' => (bool) $this->is_resolved,
            'resolved_at' => $this->resolved_at instanceof \DateTimeInterface
                ? $this->resolved_at->format('Y-m-d H:i:s')
                : $this->resolved_at,

            'is_closed' => (bool) $this->is_closed,
            'closed_at' => $this->closed_at instanceof \DateTimeInterface
                ? $this->closed_at->format('Y-m-d H:i:s')
                : $this->closed_at,

            'category_id' => $category?->id,
            'category_key' => $category?->key,
            'category_title' => $category?->title,

            'sanction_id' => $sanction?->id,
            'sanction_key' => $sanction?->key,
            'sanction_title' => $sanction?->title,
            'has_sanction_active' => (bool) $this->has_sanction_active,
            'sanction_expires_at' => $this->sanction_expires_at instanceof \DateTimeInterface
                ? $this->sanction_expires_at->format('Y-m-d H:i:s')
                : $this->sanction_expires_at,

            'feed_post_id' => $this->feed_post_id,
            'feed_post_uid' => $feedPost?->uid,
            'feed_post_title' => $feedPost?->title,

            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,

            'messages' => $this->when(
                $this->relationLoaded('messages'),
                fn () => $this->messages->map(fn ($m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'content' => $m->content,
                ])->values()
            ),
        ];
    }
}
