<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectTamuToWargaPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user memiliki role tamu, arahkan ke panel warga
        if ($user && $user->role && $user->role->name === 'tamu') {
            // Jika user mencoba mengakses halaman admin, arahkan ke panel warga
            if ($request->is('admin*') || $request->is('rt*') || $request->is('rw*')) {
                return redirect()->to('/warga');
            }
        }

        return $next($request);
    }
}
