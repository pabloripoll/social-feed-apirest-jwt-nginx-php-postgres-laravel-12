<?php

namespace App\Domain\Feed\Controller;

use App\Domain\Feed\Models\FeedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Feed\Requests\FeedPostCreateRequest;
use App\Domain\Member\Service\MemberService;
use App\Domain\Feed\Resources\FeedPostResource;

class FeedPostController
{
    /**
     * POST /api/v1/account/feed/posts
     */
    public function createPost(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $memberStatus = (new MemberService)->checkAccess($user);
        if (! $memberStatus) {
            return response()->json([
                    'message' => $memberStatus->message,
                    'error' => $memberStatus->error,
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        FeedPost::query()
            ->where('user_id', $user->id)
            ->where('is_sketch', true)
            ->delete();

        $post = new FeedPost;
        $post->user_id = $user->id;
        $post->is_sketch = true;
        $post->save();

        $response = [
            'message' => 'post sketch has been succefully created.',
            'post_uid' => $post->uid,
            'user_id' => $post->user_id,
        ];

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * PUT /api/v1/account/feed/posts/{post_uid}
     */
    public function editPost(Request $request, int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $memberStatus = (new MemberService)->checkAccess($user);
        if (! $memberStatus) {
            return response()->json([
                    'message' => $memberStatus->message,
                    'error' => $memberStatus->error,
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $postScketched = FeedPost::query()
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $postScketched) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($postScketched->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'not_editable',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        if (! $postScketched->is_sketch) {
            return response()->json([
                    'message' => 'Feed post has been already edited.',
                    'error' => 'already_edited',
                ],
                JsonResponse::HTTP_CONFLICT
            );
        }

        $postScketched->delete();

        $formRequest = new FeedPostCreateRequest;

        $validator = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $formRequest->messages()
        );
        if ($validator->fails()) {
            $errors = (array) $validator->errors()->messages();
            $field = array_key_first($errors);

            return response()->json(['message' => $errors[$field][0], 'error' => $field], JsonResponse::HTTP_NOT_ACCEPTABLE);
        }
        $validated = $validator->validated();

        $post = new FeedPost;
        $post->uid          = $post_uid;
        $post->user_id      = $user->id;
        $post->category_id  = $validated['category_id'];
        $post->region_id    = $user->member->region_id;
        $post->is_sketch    = false;
        $post->is_draft     = $validated['status'] == 'draft' ? true : false;
        $post->is_active    = $validated['status'] == 'broadcast' ? true : false;
        $post->is_banned    = false;
        $post->title        = $validated['title'];
        $post->slug         = Str::limit(Str::slug($validated['title']), 128);
        $post->summary      = Str::limit(trim(strip_tags($validated['article'])), 128);
        $post->article      = $validated['article'];
        $post->save();

        $statusText = $validated['status'] == 'broadcast' ? 'published.' : 'saved as draft.';
        $response = [
            'message' => 'Feed post has successfully ' . $statusText,
            'post' => new FeedPostResource($post)
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * GET /api/v1/account/feed/posts/{post_uid}
     */
    public function readPost(int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $memberStatus = (new MemberService)->checkAccess($user);
        if (! $memberStatus) {
            return response()->json([
                    'message' => $memberStatus->message,
                    'error' => $memberStatus->error,
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $post = FeedPost::query()
            ->with(['user.member', 'category', 'region.continent', 'media'])
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return response()->json(new FeedPostResource($post), JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/account/feed/posts/{post_uid}
     */
    public function updatePost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/account/feed/posts/{post_uid}
     */
    public function deletePost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
