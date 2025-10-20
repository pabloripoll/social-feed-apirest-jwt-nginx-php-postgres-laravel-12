<?php

namespace App\Http\Middleware;

use App\Domain\User\Models\Role;
use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleMemberMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user || $user->role != Role::MEMBER) {
            return response()->json(['message' => 'Access to this resource by non-member users is forbidden.'], JsonResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
