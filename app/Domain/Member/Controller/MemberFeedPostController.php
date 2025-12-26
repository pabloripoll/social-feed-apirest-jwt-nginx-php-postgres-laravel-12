<?php

namespace App\Domain\Member\Controller;

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Feed\Models\FeedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Member\Requests\MemberFeedPostEditRequest;
use App\Domain\Member\Resources\MemberFeedPostResource;
use App\Domain\Member\Models\Member;
use App\Support\Paginate;
use App\Domain\Feed\Service\FeedPostService;
use App\Domain\Feed\Requests\FeedPostRequest;
use App\Http\Services\Storage\Local\StorageService;

class MemberFeedPostController
{
    /**
     * POST /api/v1/account/feed/posts
     */
    public function createPost(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $sketchPosts = FeedPost::query()
            ->with(['media'])
            ->where('user_id', $user->id)
            ->where('is_sketch', true)
            ->get();
        if ($sketchPosts->isNotEmpty()) {
            foreach ($sketchPosts as $post) {
                if ($post->media->isNotEmpty()) {
                    foreach ($post->media as $media) {
                        (new StorageService)->delete($media);
                        $media->delete();
                    }
                }
                $post->delete();
            }
        }

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
        $user->load(['member', 'memberProfile']);

        $post = FeedPost::query()
            ->with(['media'])
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'post_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if (! $post->is_sketch) {
            return response()->json([
                    'message' => 'Feed post has been already edited.',
                    'error' => 'post_already_edited',
                ],
                JsonResponse::HTTP_CONFLICT
            );
        }

        $formRequest = new MemberFeedPostEditRequest;

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

        $post->user_id      = $user->id;
        $post->category_id  = $validated['category_id'];
        $post->continent_id = $user->member->continent_id;
        $post->region_id    = $user->member->region_id;
        $post->is_sketch    = false;
        $post->is_draft     = $validated['status'] == 'draft' ? true : false;
        $post->is_active    = $validated['status'] == 'broadcast' ? true : false;
        $post->is_banned    = false;
        $post->title        = $validated['title'];
        $post->slug         = Str::limit(Str::slug($validated['title']), 128);
        $post->summary      = Str::limit(trim(strip_tags($validated['article'])), 128);
        $post->article      = $validated['article'];
        $post->created_at   = now();
        $post->save();

        // Dependencies
        $member = Member::where('user_id', $user->id)->first();
        $member->feed_posts_count = $member->feed_posts_count + 1;
        $member->save();

        $category = FeedCategory::find($post->category_id);
        $category->posts_count = $category->posts_count + 1;
        $category->save();

        $statusText = $validated['status'] == 'broadcast' ? 'published.' : 'saved as draft.';
        $response = [
            'message' => 'Feed post has successfully ' . $statusText,
            'post' => new MemberFeedPostResource($post)
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

        $post = FeedPost::query()
            ->with(['user', 'member', 'category', 'continent', 'region', 'media'])
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

        $statusText = 'Feed post has no status!';
        $statusText = $post->is_active !== true ? $statusText : 'Feed post set as active and is available for all users.';
        $statusText = $post->is_draft !== true ? $statusText : 'Feed post is a draft - Only creator can access it.';
        $statusText = $post->is_banned !== true ? $statusText : 'Feed post set as deactivated because has been banned - Only creator can access it.';
        $response = [
            'message' => 'Feed post has successfully read. ' . $statusText,
            'post' => new MemberFeedPostResource($post)
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/account/feed/posts/sketch
     */
    public function readSketchPost(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $post = FeedPost::query()
            ->with(['user', 'member', 'category', 'continent', 'region', 'media'])
            ->where('user_id', $user->id)
            ->where('is_sketch', true)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'No feed sketch post found.',
                    'error' => 'not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = [
            'message' => 'Feed sketch post has successfully read.',
            'post' => new MemberFeedPostResource($post)
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/account/feed/posts/{post_uid}
     */
    public function updatePost(Request $request, int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile']);

        $post = FeedPost::query()
            ->with(['media'])
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

        if ($post->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'not_editable',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $formRequest = new MemberFeedPostEditRequest;

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

        $post->category_id  = $validated['category_id'];
        $post->is_draft     = $validated['status'] == 'draft' ? true : false;
        $post->is_active    = $validated['status'] == 'broadcast' ? true : false;
        $post->title        = $validated['title'];
        $post->slug         = Str::limit(Str::slug($validated['title']), 128);
        $post->summary      = Str::limit(trim(strip_tags($validated['article'])), 128);
        $post->article      = $validated['article'];
        $post->save();

        // Dependencies
        if ($post->category_id != $validated['category_id']) {
            $legCategory = FeedCategory::find($post->category_id);
            $legCategory->posts_count = max(0, $legCategory->posts_count - 1);
            $legCategory->save();

            $newCategory = FeedCategory::find($validated['category_id']);
            $newCategory->posts_count = $newCategory->posts_count + 1;
            $newCategory->save();
        }

        $statusText = $validated['status'] == 'broadcast' ? 'publish updated.' : 'updated as draft.';
        $response = [
            'message' => 'Feed post has successfully ' . $statusText,
            'post' => new MemberFeedPostResource($post)
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * DELETE /api/v1/account/feed/posts/{post_uid}
     */
    public function deletePost(int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile']);

        $post = FeedPost::query()
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

        if ($post->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'not_editable',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $legacyPost = [
            'uid' => $post->uid,
            'user_id' => $post->user_id,
            'title' => $post->title,
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
        ];

        // Dependencies
        $member = Member::where('user_id', $user->id)->first();
        $member->feed_posts_count = max(0, $member->feed_posts_count - 1);
        $member->save();

        $category = FeedCategory::find($post->category_id);
        $category->posts_count = max(0, $category->posts_count - 1);
        $category->save();

        // Delete post
        $post->delete();

        $response = [
            'message' => 'post sketch has been succefully deleted.',
            'post' => $legacyPost,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts
     */
    public function posts(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile']);

        $formRequest = new FeedPostRequest;
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

        $query = FeedPost::query()
            ->with(['user', 'member', 'category', 'continent', 'region', 'media'])
            ->where('user_id', $user->id)
            ->where('is_active', true);

        $filters = [];

        if (isset($validated['category'])) {
            $filters['category'] = $validated['category'];

            $query->whereHas('category', function ($q) use ($validated) {
                $q->where('key', $validated['category']);
            });
        }

        $sortReference = 'created_at';
        $sortDirection = 'desc';
        if (isset($validated['sort-by'])) {
            $ref = $validated['sort-by'];
            $filters['sort-by'] = $validated['sort-by'];

            $sortReference = $ref != 'thumbs-up' ? $sortReference : 'thumbs_up_count';
            $sortReference = $ref != 'thumbs-down' ? $sortReference : 'thumbs_down_count';

            $sortDirection = $ref == 'oldest' ? 'asc' : $sortDirection;
        }
        $query->orderBy($sortReference, $sortDirection);

        // Pagination
        $listing = Paginate::listing($query->count(), $filters);

        $posts = $query->paginate($listing->limit, ['*'], 'page', $listing->page);

        $response = [
            'filters' => FeedPostService::filters(),
            'listing' => $listing,
            'result' => MemberFeedPostResource::collection($posts),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
