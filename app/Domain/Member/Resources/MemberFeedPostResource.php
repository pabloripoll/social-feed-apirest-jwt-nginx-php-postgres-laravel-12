<?php

namespace App\Domain\Member\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberFeedPostResource extends JsonResource
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
        $continent = $this->whenLoaded('continent') ? $this->continent : null;
        $region = $this->whenLoaded('region') ? $this->region : null;
        $category = $this->whenLoaded('category') ? $this->category : null;

        return [
            'uid' => $this->uid,

            'continent_id' => $this->continent_id,
            'continent_name' => $continent ? $continent->name : null,

            'region_id' => $this->region_id,
            'region_name' => $region ? $region->name : null,

            'category_id' => $this->category_id,
            'category_key' => $category ? ($category->key ?? null) : null,
            'category_title' => $category ? ($category->title ?? null) : null,

            'is_sketch' => (bool) ($this->is_sketch ?? false),
            'is_draft' => (bool) ($this->is_draft ?? false),
            'is_active' => (bool) ($this->is_active ?? false),
            'is_banned' => (bool) ($this->is_banned ?? false),

            'reports_count' => (int) ($this->reports_count ?? 0),
            'thumbs_up_count' => (int) ($this->thumbs_up_count ?? 0),
            'thumbs_down_count' => (int) ($this->thumbs_down_count ?? 0),

            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'article' => $this->article,

            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format('Y-m-d H:i:s')
                : $this->created_at,

            'updated_at' => $this->updated_at instanceof \DateTimeInterface
                ? $this->updated_at->format('Y-m-d H:i:s')
                : $this->updated_at,

            // media: when loaded, map to minimal array; otherwise empty array to keep shape consistent
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($m) {
                    return [
                        'uid' => $m->uid,
                        'type' => $m->type ?? null,
                        'url' => $m->url ?? null,
                        'meta' => $m->meta ?? $this->title,
                    ];
                })->all();
            }, []),
        ];
    }
}
