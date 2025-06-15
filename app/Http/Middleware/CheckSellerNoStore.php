<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSellerNoStore
{
    /**
     * Handle an incoming request - only allow sellers without store
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'seller') {
            abort(403, 'Access denied. Seller role required.');
        }

        // If user already has a store, redirect to store management
        if ($user->store) {
            return redirect()->route('seller.store.show')
                ->with('info', 'You already have a store.');
        }

        return $next($request);
    }
}
