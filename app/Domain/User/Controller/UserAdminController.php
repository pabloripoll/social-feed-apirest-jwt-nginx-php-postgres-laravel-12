<?php

namespace App\Domain\User\Controller;

use App\Domain\Admin\Models\Admin;
use Illuminate\Http\Request;
use App\Support\Paginate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Requests\UserAdminRequest;

class UserAdminController extends Controller
{
    /**
     * Feed Post Filters
     */
    public static function filters(): array
    {
        return [
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
            ],
        ];
    }

    /**
     * /api/v1/users/admins
     */
    public function listing(Request $request): JsonResponse
    {
        $formRequest = new UserAdminRequest;
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

        $query = Admin::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'continent',
                'region',
            ]);

        if (isset($filters->sort_by)) {
            $query = $filters->sort_by == 'oldest' ? $query->oldest() : $query->latest();
        }

        $listing = Paginate::listing($query->count(), $filters);

        $admins = Paginate::result($query, $listing);

        $response = [
            'filters' => $this->filters(),
            'listing' => $listing,
            'result'  => $admins,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * /api/v1/users/admins/{uid}
     */
    public function baseData(int $uid): JsonResponse
    {
        $admin = Admin::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'accessLogs' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->where('uid', $uid)
            ->first();

        $response = [
            'email'     => $admin->user->email,
            'uid'       => $admin->uid,
            'nickname'  => $admin->profile->nickname,
            'avatar'    => $admin->avatar?->url ?? null,
            'since'     => $admin->created_at->format('Y-m-d H:i:s') ?? null,
            'last-seen' => $admin->accessLogs?->updated_at->format('Y-m-d H:i:s') ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * /api/v1/users/admins/{uid}/profile
     */
    public function profile(int $uid): JsonResponse
    {
        $admin = Admin::query()
            ->with([
                'profile',
                'avatar',
            ])
            ->where('uid', $uid)
            ->first();

        $response = [
            'email'     => $admin->user->email,
            'uid'       => $admin->uid,
            'nickname'  => $admin->profile->nickname,
            'avatar'    => $admin->avatar?->url ?? null,
            'since'     => $admin->created_at->format('Y-m-d H:i:s') ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
