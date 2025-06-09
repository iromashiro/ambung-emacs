<?php

namespace App\Observers;

use App\Models\Product;
use Spatie\Activitylog\Facades\Activity as LogActivity;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        try {
            LogActivity::performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'store_id' => $product->store_id,
                ])
                ->log('product_created');
        } catch (\Exception $e) {
            // Silently fail if no authenticated user
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        try {
            LogActivity::performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'changes' => $product->getDirty(),
                ])
                ->log('product_updated');
        } catch (\Exception $e) {
            // Silently fail if no authenticated user
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        try {
            LogActivity::performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties([
                    'product_id' => $product->id,
                    'name' => $product->name,
                ])
                ->log('product_deleted');
        } catch (\Exception $e) {
            // Silently fail if no authenticated user
        }
    }
}
