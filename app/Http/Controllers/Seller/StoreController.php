<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:seller');
        $this->middleware('store.owner'); // Products require active store
    }

    /**
     * Show the form for creating a new store.
     */
    public function create(): View
    {
        // Check if user already has a store
        if (auth()->user()->store) {
            return redirect()->route('seller.store.edit')
                ->with('error', 'You already have a store');
        }

        return view('seller.store.create');
    }

    /**
     * Store a newly created store.
     */
    public function store(CreateStoreRequest $request): RedirectResponse
    {
        try {
            $store = $this->storeService->createStore(
                auth()->user(),
                $request->validated(),
                $request->file('logo')
            );

            return redirect()->route('seller.dashboard')
                ->with('success', 'Store created successfully. Please wait for admin approval.');
        } catch (\Exception $e) {
            \Log::error('Store creation error: ' . $e->getMessage());
            return redirect()->route('seller.store.create')
                ->with('error', 'Failed to create store. Please try again.');
        }
    }

    /**
     * Show the form for editing the store.
     */
    public function edit(): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        return view('seller.store.edit', [
            'store' => $store,
        ]);
    }

    /**
     * Update the store.
     */
    public function update(UpdateStoreRequest $request): RedirectResponse
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        try {
            $store = $this->storeService->updateStore(
                $store,
                $request->validated(),
                $request->file('logo')
            );

            return redirect()->route('seller.store.edit')
                ->with('success', 'Store updated successfully');
        } catch (\Exception $e) {
            \Log::error('Store update error: ' . $e->getMessage());
            return redirect()->route('seller.store.edit')
                ->with('error', 'Failed to update store. Please try again.');
        }
    }

    /**
     * Show store status for pending approval
     */
    public function status(): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        return view('seller.store.status', [
            'store' => $store,
        ]);
    }

    /**
     * Show store details
     */
    public function show(): View
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('seller.store.create')
                ->with('error', 'You need to create a store first');
        }

        return view('seller.store.show', [
            'store' => $store,
        ]);
    }

    /**
     * Show store setup form (for new sellers)
     */
    public function setup(): View
    {
        // Check if user already has a store
        if (auth()->user()->store) {
            return redirect()->route('seller.store.show')
                ->with('info', 'You already have a store');
        }

        return view('seller.store.setup');
    }

    /**
     * Store setup for new sellers
     */
    public function storeSetup(CreateStoreRequest $request): RedirectResponse
    {
        // Check if user already has a store
        if (auth()->user()->store) {
            return redirect()->route('seller.store.show')
                ->with('info', 'You already have a store');
        }

        try {
            $store = $this->storeService->createStore(
                auth()->user(),
                $request->validated(),
                $request->file('logo')
            );

            return redirect()->route('seller.store.status')
                ->with('success', 'Store setup completed! Please wait for admin approval.');
        } catch (\Exception $e) {
            \Log::error('Store setup error: ' . $e->getMessage());
            return redirect()->route('seller.store.setup')
                ->with('error', 'Failed to setup store. Please try again.');
        }
    }
}
