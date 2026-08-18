<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user sudah login
        if (Auth::check()) {
            $user = Auth::user();

            // Jika user memiliki role warga dan mencoba mengakses panel admin
            if ($user->role && $user->role->isWarga() && $request->is('admin*')) {
                return redirect('/warga');
            }
        }

        return $next($request);
    }
}
