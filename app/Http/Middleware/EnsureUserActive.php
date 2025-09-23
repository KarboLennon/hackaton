<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->status !== 'active') {
            return response()->json([
                'error' => 'Account is not active yet. Please complete 1 approved submission.'
            ], 403);
        }
        return $next($request);
    }
}
