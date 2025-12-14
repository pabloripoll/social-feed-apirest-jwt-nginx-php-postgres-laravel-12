<?php

namespace App\Domain\Feed\Controller;

use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Requests\FeedPostRequest;
use App\Domain\Feed\Resources\FeedPostResource;
use App\Domain\Feed\Service\FeedPostService;

class FeedPostController
{
    /**
     * GET /api/v1/feed/posts
     */
    public function posts(Request $request): JsonResponse
    {
        $filters = [];

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
            ->where('is_active', true);

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
            'result' => FeedPostResource::collection($posts),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
