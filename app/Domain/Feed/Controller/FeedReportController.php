<?php

namespace App\Domain\Feed\Controller;

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Requests\FeedReportRequest;
use App\Domain\User\Models\UserModeration;
use App\Domain\User\Models\UserModerationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedReportController
{
    /**
     * POST /api/v1/feed/posts/{post_uid}/reports
     */
    public function createReport(Request $request, int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $formRequest = new FeedReportRequest;
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

        $modCategory = UserModerationCategory::where('key', $validated['key'])->first();
        if (! $modCategory) {
            return response()->json([
                'message' => 'Report category not found.',
                'error' => 'category_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $post = FeedPost::query()
            ->where('uid', $post_uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                'message' => 'Post not found.',
                'error' => 'post_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($user->id == $post->user_id) {
            return response()->json([
                'message' => 'You cannot report your own feed post.',
                'error' => 'own_report',
            ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $reported = UserModeration::query()
            ->where('reporter_user_id', $user->id)
            ->where('feed_post_id', $post->id)
            ->first();
        if ($reported) {
            return response()->json([
                'message' => 'Report already sent.',
                'error' => 'report_already_sent',
            ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $moderation = new UserModeration;
        $moderation->user_id = $post->user_id;
        $moderation->reporter_user_id = $user->id;
        $moderation->is_opened = true;
        $moderation->category_id = $modCategory->id;
        $moderation->feed_post_id = $post->id;
        $moderation->save();

        $post->reports_count = $post->reports_count + 1;
        $post->save();

        $response = [
            'message' => 'Feed post has been reported successfully.',
            'report' => [
                'uid' => $moderation->uid,
                'category_key' => $modCategory->key,
                'category_title' => $modCategory->title,
                'created_at' => $moderation->created_at->format('Y-m-d H:i:s'),
            ],
        ];

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * Read report only not by other than by its creator
     *
     * GET /api/v1/feed/posts/{post_uid}/reports
     */
    public function readReport(int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $post = FeedPost::query()
            ->where('uid', $post_uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                'message' => 'Feed post not available.',
                'error' => 'post_not_available',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $moderation = UserModeration::query()
            ->with(['category'])
            ->where('is_opened', true)
            ->where('reporter_user_id', $user->id)
            ->where('feed_post_id', $post->id)
            ->first();
        if (! $moderation) {
            return response()->json([
                'message' => 'Report not longer opened to be read.',
                'error' => 'report_is_not_opened',
            ],
                JsonResponse::HTTP_NO_CONTENT
            );
        }

        $status = $moderation->in_review ? 'is in review by a moderator.' : 'is going to be in review soon.';

        $response = [
            'message' => 'Feed post has been reported successfully and '.$status,
            'report' => [
                'uid' => $moderation->uid,
                'category_key' => $moderation->category->key,
                'category_title' => $moderation->category->title,
                'created_at' => $moderation->created_at->format('Y-m-d H:i:s'),
            ],
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * Update report not by other than by its creator
     *
     * PATCH /api/v1/feed/posts/{post_uid}/reports
     */
    public function updateReport(Request $request, int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $formRequest = new FeedReportRequest;
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

        $category = UserModerationCategory::where('key', $validated['key'])->first();
        if (! $category) {
            return response()->json([
                'message' => 'Report category not found.',
                'error' => 'category_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $post = FeedPost::query()
            ->where('uid', $post_uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                'message' => 'Post not available.',
                'error' => 'post_not_available',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $moderation = UserModeration::query()
            ->where('reporter_user_id', $user->id)
            ->where('feed_post_id', $post->id)
            ->first();
        if (! $moderation) {
            return response()->json([
                'message' => 'Report not found.',
                'error' => 'report_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($moderation->in_review || $moderation->is_closed) {
            return response()->json([
                'message' => 'Report no longer available for update.',
                'error' => 'report_not_available',
            ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $moderation->category_id = $category->id;
        $moderation->save();

        $response = [
            'message' => 'Feed post has been updated successfully.',
            'report' => [
                'uid' => $moderation->uid,
                'category_key' => $moderation->category->key,
                'category_title' => $moderation->category->title,
                'created_at' => $moderation->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $moderation->updated_at->format('Y-m-d H:i:s'),
            ],
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * Delete report not by other than by its creator
     *
     * DELETE /api/v1/feed/posts/{post_uid}/reports
     */
    public function deleteReport(int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $post = FeedPost::query()
            ->where('uid', $post_uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                'message' => 'Feed post not available.',
                'error' => 'post_not_available',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $moderation = UserModeration::query()
            ->where('reporter_user_id', $user->id)
            ->where('feed_post_id', $post->id)
            ->first();
        if (! $moderation) {
            return response()->json([
                'message' => 'Report not found.',
                'error' => 'report_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($moderation->in_review || $moderation->is_closed) {
            return response()->json([
                'message' => 'Report no longer available for delete.',
                'error' => 'report_not_available_for_delete',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $legacyModeration = [
            'uid' => $moderation->uid,
            'category_key' => $moderation->category->key,
            'category_title' => $moderation->category->title,
            'created_at' => $moderation->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $moderation->updated_at->format('Y-m-d H:i:s'),
        ];

        $moderation->delete();

        $post->reports_count = max(0, $post->reports_count - 1);
        $post->save();

        $response = [
            'message' => 'Feed post report has been successfully deleted.',
            'report' => $legacyModeration,
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }
}
