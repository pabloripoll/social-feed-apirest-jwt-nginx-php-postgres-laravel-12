<?php

namespace App\Domain\User\Resources;

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
        // Safely access relations if loaded
        $user = $this->whenLoaded('user') ? $this->user : null;
        $userMember = $this->whenLoaded('user.member') ? $this->userMember : null;
        $userMemberProfile = $this->whenLoaded('user.memberProfile') ? $this->userMemberProfile : null;
        $reporter = $this->whenLoaded('reporter') ? $this->reporter : null;
        $reporterMember = $this->whenLoaded('reporter.member') ? $this->reporterMember : null;
        $reporterMemberProfile = $this->whenLoaded('reporter.memberProfile') ? $this->reporterMemberProfile : null;
        $moderator = $this->whenLoaded('moderator') ? $this->moderator : null;
        $moderatorAdmin = $this->whenLoaded('moderator.admin') ? $this->moderatorAdmin : null;
        $moderatorAdminProfile = $this->whenLoaded('moderator.adminProfile') ? $this->moderatorAdminProfile : null;
        $sanction = $this->whenLoaded('sanction') ? $this->sanction : null;
        $category = $this->whenLoaded('category') ? $this->category : null;
        $feedPost = $this->whenLoaded('feedPost') ? $this->feedPost : null;

        return [
            'id'                => $this->id,
            'uid'               => $this->uid,
            'user'              => [
                'uid'       => ! $userMember ? null : $userMember->uid,
                'email'     => ! $user ? null : $user->email,
                'nickname'  => ! $userMemberProfile ? null : $userMemberProfile->nickname,
            ],
            'reporter'            => [
                'uid'       => ! $reporterMember ? null : $reporterMember->uid,
                'email'     => ! $reporter ? null : $reporter->email,
                'nickname'  => ! $reporterMemberProfile ? null : $reporterMemberProfile->nickname,
            ],
            'moderator'            => [
                'uid'       => ! $moderatorAdmin ? null : $moderatorAdmin->uid,
                'email'     => ! $moderator ? null : $moderator->email,
                'nickname'  => ! $moderatorAdminProfile ? null : $moderatorAdminProfile->nickname,
            ],

            'is_opened'         => (bool) ($this->is_opened ?? false),
            'in_review'         => (bool) ($this->in_review ?? false),
            'in_review_since'   => $this->in_review_since instanceof \DateTimeInterface
                ? $this->in_review_since->format('Y-m-d H:i:s')
                : $this->in_review_since,
            'is_resolved' => (bool) ($this->is_resolved ?? false),
            'resolved_at' => $this->resolved_at instanceof \DateTimeInterface
                ? $this->resolved_at->format('Y-m-d H:i:s')
                : $this->resolved_at,
            'is_closed' => (bool) ($this->is_closed ?? false),
            'closed_at' => $this->closed_at instanceof \DateTimeInterface
                ? $this->closed_at->format('Y-m-d H:i:s')
                : $this->closed_at,

            'category_id'       => $category ? ($category->id ?? null) : null,
            'category_key'      => $category ? ($category->key ?? null) : null,
            'category_title'    => $category ? ($category->title ?? null) : null,
            'sanction_id'       => $sanction ? ($sanction->id ?? null) : null,
            'sanction_key'      => $sanction ? ($sanction->key ?? null) : null,
            'sanction_title'    => $sanction ? ($sanction->title ?? null) : null,
            'has_sanction_active' => (bool) ($this->has_sanction_active ?? false),
            'sanction_expires_at' => $this->sanction_expires_at instanceof \DateTimeInterface
                ? $this->sanction_expires_at->format('Y-m-d H:i:s')
                : $this->sanction_expires_at,

            'feed_post_id'      => $this->feed_post_id,
            'feed_post_uid'     => $feedPost ? ($feedPost->uid ?? null) : null,
            'feed_post_title'   => $feedPost ? ($feedPost->title ?? null) : null,

            'created_at'        => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,

            'updated_at'        => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,

            'messages'  => $this->whenLoaded('messages', function () {
                return $this->messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'user_id' => $m->user_id ?? null,
                        'content' => $m->content ?? null,
                    ];
                })->all();
            }, []),
        ];
    }
}
