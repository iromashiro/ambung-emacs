<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use App\Repositories\Interfaces\StoreRepositoryInterface;
use App\Events\StoreApprovalRequested;
use App\Events\StoreApproved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class StoreService
{
    protected $storeRepository;

    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function getStoreById(int $id): ?Store
    {
        return $this->storeRepository->findById($id);
    }

    public function getActiveStores(array $filters = [])
    {
        return $this->storeRepository->getActiveStores($filters);
    }

    public function getPendingStores()
    {
        Gate::authorize('view-pending-stores');
        return $this->storeRepository->getPendingStores();
    }

    public function createStore(array $data, User $seller): Store
    {
        Gate::authorize('create', Store::class);

        return DB::transaction(function () use ($data, $seller) {
            $storeData = [
                'seller_id' => $seller->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'status' => 'pending'
            ];

            $store = $this->storeRepository->create($storeData);

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $this->handleStoreLogo($store, $data['logo']);
            }

            // Dispatch event
            event(new StoreApprovalRequested($store));

            return $store;
        });
    }

    public function updateStore(Store $store, array $data): Store
    {
        Gate::authorize('update', $store);

        return DB::transaction(function () use ($store, $data) {
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'address' => $data['address'],
                'phone' => $data['phone']
            ];

            $this->storeRepository->update($store, $updateData);

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                // Delete old logo if exists
                if ($store->logo) {
                    Storage::disk('public')->delete($store->logo);
                }

                $this->handleStoreLogo($store, $data['logo']);
            }

            return $store->fresh();
        });
    }

    public function approveStore(Store $store): bool
    {
        Gate::authorize('approve', $store);

        $updated = $this->storeRepository->updateStatus($store, 'active');

        if ($updated) {
            // Update seller status
            $store->seller->update(['status' => 'active']);

            // Dispatch event
            event(new StoreApproved($store));
        }

        return $updated;
    }

    public function rejectStore(Store $store): bool
    {
        Gate::authorize('approve', $store);

        return $this->storeRepository->updateStatus($store, 'rejected');
    }

    private function handleStoreLogo(Store $store, UploadedFile $logo): void
    {
        $filename = 'store_' . $store->id . '_' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
        $path = $logo->storeAs('stores', $filename, 'public');

        // Create thumbnail
        $img = Image::make($logo);
        $img->resize(200, 200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbPath = 'stores/thumbs';
        if (!Storage::disk('public')->exists($thumbPath)) {
            Storage::disk('public')->makeDirectory($thumbPath);
        }

        $img->save(storage_path('app/public/stores/thumbs/' . $filename));

        // Update store with logo path
        $store->update(['logo' => $path]);
    }

    public function getStoreBySlug(string $slug): ?Store
    {
        return $this->storeRepository->findBySlug($slug);
    }
}
