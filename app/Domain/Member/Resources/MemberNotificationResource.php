<?php

namespace App\Domain\Member\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberNotificationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     * The columns are reserved for admins: receiver_id, performer_id, moderation_id, communication_id
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Safely access relations if loaded
        $type = $this->whenLoaded('type') ? $this->type : null;

        $payload = json_decode($this->payload);

        $followerProfile = $this->type->key != 'new-follower' ? null : '/api/v1/members/' . $payload->performer->uid . '/profile';

        return [
            'uid'               => $this->uid,
            'type_id'           => $this->type_id,
            'opened'            => (bool) ($this->opened ?? false),
            'opened_at'         => $this->opened_at instanceof \DateTimeInterface
                ? $this->opened_at->format('Y-m-d H:i:s')
                : $this->opened_at,
            'notify_count'      => (int) ($this->notify_count ?? 0),

            'title'             => $payload->title,
            'summary'           => $payload->summary,

            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,

            'follower_profile'  => $followerProfile,
        ];
    }
}
