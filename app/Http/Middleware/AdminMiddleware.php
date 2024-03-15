<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();

            $allowed = ["Administrator" => 1];

            if (in_array($user->role_id, $allowed)) {
                return $next($request);
            }
        }

        return response()->json(["error" => "Ne možete pristupiti ovoj lokaciji."], 403, ['status' => 'fail']);
    }
}
