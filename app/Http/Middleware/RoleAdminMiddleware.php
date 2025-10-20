<?php

namespace App\Http\Middleware;

use App\Domain\User\Models\Role;
use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleAdminMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user || $user->role != Role::ADMIN) {
            return response()->json(['message' => 'Access to this resource by non-administrator users is forbidden.'], JsonResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
