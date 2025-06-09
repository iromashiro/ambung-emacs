<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isSeller()) {
            abort(403, 'Only sellers can access this area.');
        }

        $store = $user->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first.');
        }

        if (!$store->isActive()) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is not active. Please wait for admin approval.');
        }

        return $next($request);
    }
}
