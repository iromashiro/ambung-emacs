<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'seller') {
            abort(403, 'Unauthorized action.');
        }

        $store = $request->user()->store;

        if (!$store) {
            return redirect()->route('seller.stores.create')
                ->with('error', 'You need to create a store first.');
        }

        if ($store->status !== 'active') {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your store is pending approval.');
        }

        return $next($request);
    }
}
