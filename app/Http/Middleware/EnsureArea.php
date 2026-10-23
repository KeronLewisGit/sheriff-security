<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Role gate for a dashboard area: `area:sales`, `area:hiring` or `area:admin`. */
class EnsureArea
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->role->can($area), 403, 'Your role does not include this part of the dashboard.');

        return $next($request);
    }
}
