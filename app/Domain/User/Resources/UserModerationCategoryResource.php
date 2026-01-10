<?php

namespace App\Domain\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserModerationCategoryResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'key'           => $this->key,
            'level'         => (int) $this->level,
            'position'      => (int) $this->position,
            'title'         => $this->title,
            'description'   => $this->description,
            'created_at'    => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,
            'updated_at'    => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,
        ];
    }
}
