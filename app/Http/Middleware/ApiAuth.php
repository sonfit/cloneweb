<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd(auth()->user());
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng tra cứu'
            ], 401);
        }

        return $next($request);
    }
}
