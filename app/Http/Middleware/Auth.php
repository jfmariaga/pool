<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth as CheckAuth;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (CheckAuth::check()) {
            $user = CheckAuth::user();
            if ($user->status == 0) {
                return redirect('/login')->with('status', 'Tu cuenta está inactiva. Por favor, contacta al administrador.');
            }
            return $next($request);
        }
        return redirect('/login');
    }
}
