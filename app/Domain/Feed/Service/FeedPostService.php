<?php

namespace App\Domain\Feed\Service;

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Models\FeedCategory;

class FeedPostService
{
    /**
     * Feed Post Filters
     */
    public static function filters(): array
    {
        $categories = [];
        $feedCategories = FeedCategory::select(['key','title'])
            ->orderBy('position', 'asc')
            ->get();

        foreach ($feedCategories as $row) {
            $categories[$row['key']] = $row['title'];
        }

        return [
            'categories' => $categories,
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
                'thumbs-up' => 'Thumbs Up',
                'thumbs-down' => 'Thumbs Down',
            ],
        ];
    }

    public static function listing(object $params, ?User $user = null)
    {
        $query = FeedPost::query()
            ->select('feed_posts.*') // make sure the model has all its columns selected
            ->with(['user', 'member', 'avatar', 'category', 'continent', 'region', 'media'])
            ->where('is_active', true);

        // If user is authenticated, add boolean flags via subqueries
        if ($user) {
            $authUserId = (int) $user->id;

            if (isset($params->following)) {
                $query->whereExists(function ($subquery) use ($authUserId) {
                    $subquery->select(DB::raw(1))
                        ->from('members_following')
                        ->whereColumn('members_following.following_user_id', 'feed_posts.user_id')
                        ->where('members_following. user_id', $authUserId);
                });
            }

            // whether auth user has thumbed up this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$authUserId}
                and feed_posts_thumbs.up = true
            ) as is_thumb_up_by_me"));

            // whether auth user has thumbed down this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$authUserId}
                and feed_posts_thumbs.down = true
            ) as is_thumb_down_by_me"));

            // whether auth user follows the post owner (auth -> following -> post owner)
            $query->addSelect(DB::raw("EXISTS (
                select 1 from members_following
                where members_following.user_id = {$authUserId}
                and members_following.following_user_id = feed_posts.user_id
            ) as is_post_from_following"));

            // whether post owner follows the auth user (post owner -> following -> auth)
            $query->addSelect(DB::raw("EXISTS (
                select 1 from members_following
                where members_following.user_id = feed_posts.user_id
                and members_following.following_user_id = {$authUserId}
            ) as is_post_from_follower"));
        } else {
            // when not authenticated ensure flags exist and are false (optional)
            $query->addSelect(DB::raw('false as is_thumb_up_by_me'));
            $query->addSelect(DB::raw('false as is_thumb_down_by_me'));
            $query->addSelect(DB::raw('false as is_post_from_following'));
            $query->addSelect(DB::raw('false as is_post_from_follower'));
        }

        if (isset($params->category)) {
            $filters['category'] = $params->category;

            $query->whereHas('category', function ($q) use ($params) {
                $q->where('key', $params->category);
            });
        }

        $sortDirection = 'desc';
        $sortReference = 'created_at';
        if (isset($params->sort_by)) {
            $ref = $params->sort_by;
            $filters['sort-by'] = $params->sort_by;

            $sortReference = $ref != 'thumbs-up' ? $sortReference : 'thumbs_up_count';
            $sortReference = $ref != 'thumbs-down' ? $sortReference : 'thumbs_down_count';

            $sortDirection = $ref == 'oldest' ? 'asc' : $sortDirection;
        }

        $query->orderBy($sortReference, $sortDirection);

        return $query;
    }
}
