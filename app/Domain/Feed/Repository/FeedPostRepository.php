<?php

namespace App\Domain\Feed\Repository;

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use App\Domain\Feed\Models\FeedPost;

class FeedPostRepository
{
    /**
     * Feed Post listing for public access and authorized user requests
     */
    public static function listing(object $filters, ?User $userAuth = null)
    {
        $query = FeedPost::query()
            ->select('feed_posts.*')
            ->with(['user', 'member', 'avatar', 'category', 'continent', 'region', 'media'])
            ->where('is_active', true);

        // Search terms filter - Options cross db engines
        // A) $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
        // B) $q->where(DB::raw('LOWER(title)'), 'LIKE', "%{$search}%")
        if (isset($filters->search) && ! empty($filters->search)) {
            $search = trim($filters->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('summary', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters->user_id)) {
            $query = $query->where('user_id', $filters->user_id);
        }

        $rel = ! $userAuth || (isset($filters->user_id) && $filters->user_id == $userAuth->id) ? false : true;

        if ($rel) {
            $userAuthId = (int) $userAuth->id;

            // posts from user authenticated following list
            if (isset($filters->following)) {
                $query->whereExists(function ($subquery) use ($userAuthId) {
                    $subquery->select(DB::raw(1))
                        ->from('members_followers')
                        ->whereColumn('members_followers.following_user_id', 'feed_posts.user_id')
                        ->where('members_followers. user_id', $userAuthId);
                });
            }

            // user authenticated has thumbed up this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$userAuthId}
                and feed_posts_thumbs.up = true
            ) as is_thumb_up_by_me"));

            // user authenticated has thumbed down this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$userAuthId}
                and feed_posts_thumbs.down = true
            ) as is_thumb_down_by_me"));

            // user authenticated follows the post owner (auth -> following -> post owner)
            $query->addSelect(DB::raw("EXISTS (
                select 1 from members_followers
                where members_followers.user_id = {$userAuthId}
                and members_followers.following_user_id = feed_posts.user_id
            ) as is_post_from_following"));

        } else {
            // when not user authenticated ensure flags exist and are false (optional)
            $query->addSelect(DB::raw('false as is_thumb_up_by_me'));
            $query->addSelect(DB::raw('false as is_thumb_down_by_me'));
            $query->addSelect(DB::raw('false as is_post_from_following'));
        }

        if (isset($filters->category)) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('key', $filters->category);
            });
        }

        // Options: thumbs-up, thumbs-down, recent, oldest
        $sortDirection = 'desc';
        $sortReference = 'created_at';
        if (isset($filters->sort_by)) {
            $sortReference = $filters->sort_by != 'thumbs-up' ? $sortReference : 'thumbs_up_count';
            $sortReference = $filters->sort_by != 'thumbs-down' ? $sortReference : 'thumbs_down_count';

            $sortDirection = $filters->sort_by == 'oldest' ? 'asc' : $sortDirection;
        }
        $query->orderBy($sortReference, $sortDirection);

        return $query;
    }
}
