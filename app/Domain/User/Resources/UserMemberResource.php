<?php

namespace App\Domain\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMemberResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('user');
        $profile = $this->whenLoaded('profile');
        $avatar = $this->whenLoaded('avatar');
        $continent = $this->whenLoaded('continent');
        $region = $this->whenLoaded('region');

        return [
            'id' => $user->id,
            'uid' => $this->uid,
            'email' => $user->email,
            'nickname' => $profile->nickname,
            'avatar' => $avatar->url ?? null,
            'continent_id' => $continent->id ?? null,
            'continent_name' => $continent->name ?? null,
            'region_id' => $region->id ?? null,
            'region_name' => $region->name ?? null,
            'is_active' => (bool) $this->is_active,
            'is_banned' => (bool) $this->is_banned,

            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,
            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,
        ];
    }
}
