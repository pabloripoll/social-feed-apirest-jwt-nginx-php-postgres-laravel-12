<?php

namespace App\Modules\Feed\Controller;

use App\Modules\Feed\Models\FeedPost;
use App\Modules\Feed\Models\FeedPostThumb;
use App\Modules\User\Dto\UserNotificationDto;
use App\Modules\User\Service\UserNotificationService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedPostThumbController
{
    /**
     * Handle feed post thumb up counting relations
     */
    protected function thumbUpSumOnRelations(FeedPost $post): void
    {
        $post->thumbs_up_count = $post->thumbs_up_count + 1;
        $post->save();

        $post->category->posts_thumbs_up_count = $post->category->posts_thumbs_up_count + 1;
        $post->category->save();

        $post->member->feed_posts_thumbs_up_count = $post->member->feed_posts_thumbs_up_count + 1;
        $post->member->save();
    }

    /**
     * Handle feed post thumb up counting relations
     */
    protected function thumbUpDiscountOnRelations(FeedPost $post): void
    {
        $post->thumbs_up_count = max(0, $post->thumbs_up_count - 1);
        $post->save();

        $post->category->posts_thumbs_up_count = max(0, $post->category->posts_thumbs_up_count - 1);
        $post->category->save();

        $post->member->feed_posts_thumbs_up_count = max(0, $post->member->feed_posts_thumbs_up_count - 1);
        $post->member->save();
    }

    /**
     * Handle feed post thumb up counting relations
     */
    protected function thumbDownSumOnRelations(FeedPost $post): void
    {
        $post->thumbs_down_count = $post->thumbs_down_count + 1;
        $post->save();

        $post->category->posts_thumbs_down_count = $post->category->posts_thumbs_down_count + 1;
        $post->category->save();

        $post->member->feed_posts_thumbs_down_count = $post->member->feed_posts_thumbs_down_count + 1;
        $post->member->save();
    }

    /**
     * Handle feed post thumb up counting relations
     */
    protected function thumbDownDiscountOnRelations(FeedPost $post): void
    {
        $post->thumbs_down_count = max(0, $post->thumbs_down_count - 1);
        $post->save();

        $post->category->posts_thumbs_down_count = max(0, $post->category->posts_thumbs_down_count - 1);
        $post->category->save();

        $post->member->feed_posts_thumbs_down_count = max(0, $post->member->feed_posts_thumbs_down_count - 1);
        $post->member->save();
    }

    /**
     * Handle feed post thumb up or down action
     */
    protected function handlePostThumbVoting(string $thumb, string $action, int $uid): array
    {
        /** @var \App\Modules\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile', 'memberAvatar']);

        $post = FeedPost::query()
            ->with(['user', 'member', 'profile', 'avatar', 'category', 'continent', 'region', 'media'])
            ->where('uid', $uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return [
                'error' => 'post_not_found',
                'message' => 'Feed post not found.',
                'http_code' => JsonResponse::HTTP_NOT_FOUND,
            ];
        }

        if ($post->user_id == $user->id) {
            return [
                'error' => 'own_post',
                'message' => 'You cannot thumb '.$thumb.' your own feed post.',
                'http_code' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            ];
        }

        if ($post->is_banned) {
            return [
                'error' => 'post_banned',
                'message' => 'Feed post cannot be voted with thumb up or down.',
                'http_code' => JsonResponse::HTTP_UNAUTHORIZED,
            ];
        }

        // Delete thumb up or down voting
        if ($action == 'delete') {
            $postThumbVote = FeedPostThumb::query()
                ->where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();
            if (! $postThumbVote) {
                return [
                    'error' => 'thumb_vote_not_found',
                    'message' => 'Feed post thumb vote register not found.',
                    'http_code' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            $postThumbVote->delete();

            // discount thumb up or down vote counter on relations
            $postThumbVote->up ? $this->thumbUpDiscountOnRelations($post) : $this->thumbDownDiscountOnRelations($post);

            return [
                'message' => 'Feed post thumb '.$thumb.' deleted.',
                'http_code' => JsonResponse::HTTP_ACCEPTED,
            ];
        }

        // Create or set thumb up or down voting
        $postThumbVote = FeedPostThumb::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();
        if (! $postThumbVote) {
            $postThumbVote = new FeedPostThumb;
            $postThumbVote->user_id = $user->id;
            $postThumbVote->post_id = $post->id;
        }

        if (($postThumbVote->up === true && $thumb == 'up') || ($postThumbVote->down === true && $thumb == 'down')) {
            return [
                'message' => 'Feed post thumb '.$thumb.' already sent.',
                'http_code' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            ];
        }

        // if register exists, either type has been already set
        if ($postThumbVote->up == true || $postThumbVote->down == true) {
            // rest thumb up or down voting counter on relations before continue updating
            $postThumbVote->up == true ? $this->thumbUpDiscountOnRelations($post) : $this->thumbDownDiscountOnRelations($post);
        }

        // update or set register
        if ($thumb == 'up') {
            $postThumbVote->up = true;
            $postThumbVote->down = false;
        } else {
            $postThumbVote->up = false;
            $postThumbVote->down = true;
        }
        $postThumbVote->refresh_count = $postThumbVote->refresh_count + 1;
        $postThumbVote->save();

        // sum thumb up or down voting counter on relations
        if ($thumb == 'up') {
            $this->thumbUpSumOnRelations($post);

            $dto = new UserNotificationDto(
                performerId: $user->id,
                performerData: [
                    'uid' => $user->member->uid,
                    'nickname' => $user->memberProfile->nickname,
                    'avatar' => $user->memberAvatar?->url,
                ],
                receiverId: $post->user_id,
                receiverData: [
                    'uid' => $post->member->uid,
                    'nickname' => $post->profile->nickname,
                    'avatar' => $post->avatar?->url ?? null,
                ],
            );
            (new UserNotificationService)->newFeedPostThumbUp($dto);

        } else {
            $this->thumbDownSumOnRelations($post);
        }

        return [
            'message' => 'Feed post thumb '.$thumb.' vote successfully sent.',
            'post' => [
                'uid' => $post->uid,
                'thumbs_up_count' => $post->thumbs_up_count,
                'thumbs_down_count' => $post->thumbs_down_count,
            ],
            'http_code' => JsonResponse::HTTP_OK,
        ];
    }

    /**
     * POST /api/v1/feed/posts/{uid}/thumbs/up
     */
    public function createThumbUp(int $uid): JsonResponse
    {
        $handler = $this->handlePostThumbVoting('up', 'create', $uid);

        $response = [];
        $response['message'] = $handler['message'];
        ! isset($handler['error']) ?: $response['error'] = $handler['error'];
        ! isset($handler['post']) ?: $response['post'] = $handler['post'];

        return response()->json($response, $handler['http_code']);
    }

    /**
     * DELETE /api/v1/feed/posts/{uid}/thumbs/up
     */
    public function deleteThumbUp(int $uid): JsonResponse
    {
        $handler = $this->handlePostThumbVoting('up', 'delete', $uid);

        $response = [];
        $response['message'] = $handler['message'];
        ! isset($handler['error']) ?: $response['error'] = $handler['error'];
        ! isset($handler['post']) ?: $response['post'] = $handler['post'];

        return response()->json($response, $handler['http_code']);
    }

    /**
     * POST /api/v1/feed/posts/{uid}/thumbs/down
     */
    public function createThumbDown(int $uid): JsonResponse
    {
        $handler = $this->handlePostThumbVoting('down', 'create', $uid);

        $response = [];
        $response['message'] = $handler['message'];
        ! isset($handler['error']) ?: $response['error'] = $handler['error'];
        ! isset($handler['post']) ?: $response['post'] = $handler['post'];

        return response()->json($response, $handler['http_code']);
    }

    /**
     * DELETE /api/v1/feed/posts/{uid}/thumbs/down
     */
    public function deleteThumbDown(int $uid): JsonResponse
    {
        $handler = $this->handlePostThumbVoting('down', 'delete', $uid);

        $response = [];
        $response['message'] = $handler['message'];
        ! isset($handler['error']) ?: $response['error'] = $handler['error'];
        ! isset($handler['post']) ?: $response['post'] = $handler['post'];

        return response()->json($response, $handler['http_code']);
    }

    /**
     * Handle feed post thumb up or down action
     */
    public function readPostThumbs(int $uid): JsonResponse
    {
        /** @var \App\Modules\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile']);

        $post = FeedPost::query()
            ->with(['user', 'member', 'category', 'continent', 'region', 'media'])
            ->where('uid', $uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                'error' => 'post_not_found',
                'message' => 'Feed post not found.',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($post->is_banned) {
            return response()->json([
                'error' => 'post_banned',
                'message' => 'Feed post cannot be voted with thumb up or down.',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $userThumbVote = FeedPostThumb::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        return response()->json([
            'message' => 'Feed post successfully read.',
            'post' => [
                'uid' => $post->uid,
                'thumbs_up_count' => $post->thumbs_up_count,
                'thumbs_down_count' => $post->thumbs_down_count,
            ],
            'user' => [
                'has_vote' => ! $userThumbVote ? false : true,
                'thumb_up' => ! $userThumbVote ? false : $userThumbVote->up,
                'thumb_down' => ! $userThumbVote ? false : $userThumbVote->down,
            ],
        ],
            JsonResponse::HTTP_NOT_FOUND
        );
    }
}
