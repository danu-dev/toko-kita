<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request with strict role isolation.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Strict role separation (Admin only manages platform ops, cannot access private seller store management)
        if ($role === 'admin' && !$user->hasRole('admin')) {
            abort(403, 'Akses khusus Administrator.');
        }

        if ($role === 'seller' && !$user->hasRole('seller')) {
            abort(403, 'Akses khusus Mitra Penjual.');
        }

        return $next($request);
    }
}
