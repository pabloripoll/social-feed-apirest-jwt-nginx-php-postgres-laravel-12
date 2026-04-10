<?php

namespace App\Modules\Feed\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedCategoryResource extends JsonResource
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
            'id' => $this->id,
            'key' => $this->key,
            'position' => (int) ($this->position ?? 0),
            'posts_count' => (int) ($this->posts_count ?? 0),
            'posts_thumbs_up_count' => (int) ($this->posts_thumbs_up_count ?? 0),
            'posts_thumbs_down_count' => (int) ($this->posts_thumbs_down_count ?? 0),
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
