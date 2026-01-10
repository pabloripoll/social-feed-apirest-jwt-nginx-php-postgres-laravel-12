<?php

namespace App\Domain\User\Controller;

use Carbon\Carbon;
use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Models\UserModeration;
use App\Domain\User\Models\UserModerationCategory;
use App\Domain\User\Models\UserModerationSanction;
use App\Domain\User\Requests\UserModerationRequest;
use App\Domain\User\Requests\UserModerationUpdateRequest;
use App\Domain\User\Resources\UserModerationResource;
use App\Domain\User\Service\UserModerationService;

class UserModerationController extends Controller
{
    /**
     * GET /api/v1/moderations/filters
     */
    public function filters(): JsonResponse
    {
        return response()->json(UserModerationService::filters(), JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/moderations
     */
    public function listing(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $admin */
        $admin = Auth::user();

        $formRequest = new UserModerationRequest;
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

        $filters = (object) $validator->validated();
        $query = UserModeration::query()
            ->with([
                'user',
                'user.member',
                'user.memberProfile',
                'reporter',
                'reporter.member',
                'reporter.memberProfile',
                'moderator',
                'moderator.admin',
                'moderator.adminProfile',
                'category',
                'sanction',
                'feedPost'
            ]);

        if (isset($filters->moderator) && $filters->moderator == 'me') {
            $query->where('moderator_id', $admin->id);
        }

        if (isset($filters->category)) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('key', $filters->category);
            });
        }

        if (isset($filters->status)) {
            $status = 'is_opened';
            $status = $filters->status != 'reviewing' ? $status : 'in_review';
            $status = $filters->status != 'resolved' ? $status : 'is_resolved';
            $status = $filters->status != 'closed' ? $status : 'is_closed';

            $query->where($status, true);
        }

        if (isset($filters->sanction)) {
            $query->whereHas('sanction', function ($q) use ($filters) {
                $q->where('key', $filters->sanction);
            });
        }

        // Options: recent, oldest
        $sortDirection = 'desc';
        $orderDirection = ! isset($filters->sort_by) ? $sortDirection : ($filters->sort_by != 'oldest' ? $sortDirection : 'asc');
        $query->orderBy('created_at', $orderDirection);

        $listing = Paginate::listing($query->count(), $filters);

        $moderations = Paginate::result($query, $listing);

        $response = [
            'filters' => UserModerationService::filters(),
            'listing' => $listing,
            'result'  => UserModerationResource::collection($moderations),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/moderations/{id}
     */
    public function read(int $id): JsonResponse
    {
        $moderation = UserModeration::query()
            ->with([
                'user',
                'user.member',
                'user.memberProfile',
                'reporter',
                'reporter.member',
                'reporter.memberProfile',
                'moderator',
                'moderator.admin',
                'moderator.adminProfile',
                'category',
                'sanction',
                'feedPost'
            ])
            ->where('id', $id)
            ->first();
        if (! $moderation) {
            return response()->json([
                    'message' => 'Moderation was not found.',
                    'error' => 'not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = new UserModerationResource($moderation);

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/moderations/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $admin */
        $admin = Auth::user();

        $formRequest = new UserModerationUpdateRequest;
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

        $moderation = UserModeration::query()
            ->with([
                'user',
                'user.member',
                'user.memberProfile',
                'reporter',
                'reporter.member',
                'reporter.memberProfile',
                'moderator',
                'moderator.admin',
                'moderator.adminProfile',
                'category',
                'sanction',
                'feedPost'
            ])
            ->where('id', $id)
            ->first();
        if (! $moderation) {
            return response()->json([
                    'message' => 'Moderation was not found.',
                    'error' => 'moderation_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($moderation->is_closed) {
            return response()->json([
                    'message' => 'Moderation was closed and is no longer editable.',
                    'error' => 'moderation_not_editable',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        if (isset($validated['category'])) {
            $category = UserModerationCategory::where('key', $validated['key'])->first();
            if ($category) {
                $moderation->category_id = $category->id;
            }
        }

        if (isset($validated['sanction'])) {
            $sanction = UserModerationSanction::where('key', $validated['key'])->first();
            if ($sanction) {
                $moderation->sanction_id = $sanction->id;
            }
        }

        $status = $validated['status'] ?? null;

        if ($status == 'review') {
            $moderation->moderator_user_id = $admin->id;
            ! $category ? : $moderation->category_id = $category->id;
            $moderation->in_review ? : $moderation->in_review = true;
            $moderation->in_review ? : $moderation->in_review_since = Carbon::now();
            $moderation->is_resolved = false;
            $moderation->resolved_at = null;
            $moderation->save();
        }

        if ($status == 'resolve') {
            if ($moderation->is_resolved) {
                return response()->json([
                        'message' => 'Moderation is already set as resolved.',
                        'error' => 'moderation_already_resolved',
                    ],
                    JsonResponse::HTTP_NOT_ACCEPTABLE
                );
            } else {
                $moderation->moderator_user_id = $admin->id;
                $moderation->in_review = false;
                $moderation->is_resolved = true;
                $moderation->resolved_at = Carbon::now();
                $moderation->save();
            }
        }

        if ($status == 'close') {
            if (! $sanction) {
                return response()->json([
                        'message' => 'Moderation must have a sanction in order to be closed.',
                        'error' => 'moderation_sanction_missing',
                    ],
                    JsonResponse::HTTP_NOT_ACCEPTABLE
                );
            } else {
                $moderation->moderator_user_id = $admin->id;
                $moderation->is_opened = false;
                $moderation->in_review = false;
                $moderation->is_resolved = true;
                $moderation->resolved_at = Carbon::now();
                $moderation->is_closed = true;
                $moderation->closed_at = Carbon::now();
                $moderation->has_sanction_active = true;
                $moderation->sanction_expires_at = ! $validated['sanction_ends'] ? null : $validated['sanction_ends'] . date('H:i:s');
                $moderation->save();
            }
        }

        $response = new UserModerationResource($moderation);

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * DELETE /api/v1/moderations/{id}
     */
    public function delete(int $id): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $admin */
        $admin = Auth::user();

        $moderation = UserModeration::query()
            ->with([
                'feedPost'
            ])
            ->where('id', $id)
            ->first();

        if (! $moderation) {
            return response()->json([
                    'message' => 'Moderation was not found.',
                    'error' => 'moderation_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($moderation->moderator_id != $admin->id) {
            return response()->json([
                    'message' => 'Moderation can only be deleted by the assignated moderator.',
                    'error' => 'unauthorized_moderator',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        if ($moderation->is_closed && $moderation->has_sanction_active && isset($moderation->feedPost) && $moderation->feedPost->is_banned) {
            return response()->json([
                    'message' => 'Moderation cannot be deleted as it has a banned feed post.',
                    'error' => 'moderation_has_feed_post_banned',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $moderation->delete();

        $response = [
            'message' => 'Moderation has been successfully deleted.',
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }
}
