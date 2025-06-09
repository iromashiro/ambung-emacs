<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreApprovalController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $pendingStores = $this->storeService->getPendingStores();

        return view('admin.stores.pending', compact('pendingStores'));
    }

    public function approve(Store $store)
    {
        try {
            $result = $this->storeService->approveStore($store);

            if ($result) {
                return redirect()->route('admin.stores.pending')
                    ->with('success', 'Store approved successfully');
            } else {
                return redirect()->route('admin.stores.pending')
                    ->with('error', 'Failed to approve store');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.stores.pending')
                ->with('error', $e->getMessage());
        }
    }

    public function reject(Store $store)
    {
        try {
            $result = $this->storeService->rejectStore($store);

            if ($result) {
                return redirect()->route('admin.stores.pending')
                    ->with('success', 'Store rejected successfully');
            } else {
                return redirect()->route('admin.stores.pending')
                    ->with('error', 'Failed to reject store');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.stores.pending')
                ->with('error', $e->getMessage());
        }
    }

    public function show(Store $store)
    {
        return view('admin.stores.show', compact('store'));
    }
}
