<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->company_id) {
            return response()->json([
                'message' => 'User is not associated with a company.',
            ], 403);
        }

        if (!$user->company) {
            return response()->json([
                'message' => 'Company not found.',
            ], 403);
        }

        if ($user->company->status !== 'active') {
            return response()->json([
                'message' => 'Company account is inactive.',
            ], 403);
        }

        return $next($request);
    }
}
