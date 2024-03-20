<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsInQuizMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::checkAuth(Auth::class);

        if ($user->is_currently_in_quiz) return response()->json(["error" => "Već ste u kvizu, ne možete ući u neki drugi kviz."], 400, ['status' => 'fail']);

        return $next($request);
    }
}
