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
            return redirect()->route('seller.store.create')
                ->with('error', $e->getMessage());
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
            return redirect()->route('seller.store.edit')
                ->with('error', $e->getMessage());
        }
    }
}