<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFilamentAccess
{
    /**
     * Handle an incoming request.
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

            // Jika user adalah RT atau RW dan mencoba mengakses panel warga
            if ($user->role && ($user->role->isRT() || $user->role->isRW()) && $request->is('warga*')) {
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
