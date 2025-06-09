<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $request->validate([
            'cart_item_ids' => 'required|array',
            'cart_item_ids.*' => 'integer|exists:carts,id'
        ]);

        try {
            $cartItemIds = $request->cart_item_ids;

            // Validate cart items
            $validation = $this->cartService->validateCartItems($cartItemIds);

            if (!$validation['valid']) {
                return redirect()->route('cart.index')
                    ->with('error', 'Some items in your cart are no longer available: ' . implode(', ', $validation['errors']));
            }

            $cartItemsByStore = $this->cartService->getCartItemsByStoreFiltered($cartItemIds);

            // Get user addresses if available
            $addresses = auth()->check() ? auth()->user()->addresses : collect([]);
            $selectedAddress = $addresses->where('is_default', true)->first() ?? $addresses->first();

            $summary = $this->cartService->getCartSummary($cartItemIds);
            $shippingFeePerStore = 10000; // Rp 10,000 per store

            return view('buyer.checkout.index', compact(
                'cartItemsByStore',
                'addresses',
                'selectedAddress',
                'cartItemIds',
                'summary',
                'shippingFeePerStore'
            ));
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    public function direct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            // Add product to cart temporarily for direct checkout
            $this->cartService->addToCart($request->product_id, $request->quantity);

            // Get the cart item that was just added
            $cartItems = $this->cartService->getUserCart();
            $cartItem = $cartItems->where('product_id', $request->product_id)->first();

            // Redirect to checkout with this item
            return redirect()->route('checkout.index', ['cart_item_ids' => [$cartItem->id]]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
