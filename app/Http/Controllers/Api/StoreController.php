<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
        $this->middleware('auth:api')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $stores = $this->storeService->getActiveStores($filters);

        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    }

    public function show($id)
    {
        $store = $this->storeService->getStoreById($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $store
        ]);
    }

    public function store(CreateStoreRequest $request)
    {
        try {
            $store = $this->storeService->createStore(
                $request->validated(),
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Store created successfully and pending approval',
                'data' => $store
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        try {
            $updatedStore = $this->storeService->updateStore(
                $store,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Store updated successfully',
                'data' => $updatedStore
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
