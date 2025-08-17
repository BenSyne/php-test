<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     */
    public function index(Request $request): View|JsonResponse
    {
        $cart = $this->getActiveCart($request);
        
        if ($request->expectsJson()) {
            return response()->json([
                'cart' => $cart->load(['items.product', 'items.prescription']),
                'summary' => [
                    'item_count' => $cart->item_count,
                    'unique_item_count' => $cart->unique_item_count,
                    'subtotal' => $cart->subtotal,
                    'tax_amount' => $cart->tax_amount,
                    'shipping_amount' => $cart->shipping_amount,
                    'total_amount' => $cart->total_amount,
                    'requires_prescription_verification' => $cart->requires_prescription_verification,
                ],
            ]);
        }

        return view('cart.index', [
            'cart' => $cart->load(['items.product', 'items.prescription']),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($request->product_id);
        $prescription = $request->prescription_id 
            ? Prescription::findOrFail($request->prescription_id) 
            : null;

        // Validate product availability
        if (!$product->canDispense($request->quantity)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available in the requested quantity.',
                ], 422);
            }
            
            return back()->withErrors(['quantity' => 'Product is not available in the requested quantity.']);
        }

        // Validate prescription requirements
        if ($product->requires_prescription && !$prescription) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product requires a valid prescription.',
                ], 422);
            }
            
            return back()->withErrors(['prescription' => 'This product requires a valid prescription.']);
        }

        // Validate prescription if provided
        if ($prescription) {
            if (!$prescription->isVerified()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Prescription must be verified before adding to cart.',
                    ], 422);
                }
                
                return back()->withErrors(['prescription' => 'Prescription must be verified before adding to cart.']);
            }

            if ($prescription->isExpired()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Prescription has expired.',
                    ], 422);
                }
                
                return back()->withErrors(['prescription' => 'Prescription has expired.']);
            }
        }

        $cart = $this->getActiveCart($request);
        
        $cartItem = $cart->addItem($product, $request->quantity, $prescription, [
            'special_instructions' => $request->special_instructions,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully.',
                'cart_item' => $cartItem->load(['product', 'prescription']),
                'cart_summary' => [
                    'item_count' => $cart->item_count,
                    'total_amount' => $cart->total_amount,
                ],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Item added to cart successfully.');
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        // Ensure cart item belongs to current user's cart
        $cart = $this->getActiveCart($request);
        if ($cartItem->cart_id !== $cart->id) {
            abort(403, 'Unauthorized access to cart item.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        // Validate product availability
        if (!$cartItem->product->canDispense($request->quantity)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available in the requested quantity.',
                ], 422);
            }
            
            return back()->withErrors(['quantity' => 'Product is not available in the requested quantity.']);
        }

        $success = $cart->updateItemQuantity($cartItem, $request->quantity);

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update cart item.',
                ], 422);
            }
            
            return back()->withErrors(['quantity' => 'Failed to update cart item.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully.',
                'cart_item' => $cartItem->fresh()->load(['product', 'prescription']),
                'cart_summary' => [
                    'item_count' => $cart->item_count,
                    'total_amount' => $cart->total_amount,
                ],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Cart item updated successfully.');
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        // Ensure cart item belongs to current user's cart
        $cart = $this->getActiveCart($request);
        if ($cartItem->cart_id !== $cart->id) {
            abort(403, 'Unauthorized access to cart item.');
        }

        $productName = $cartItem->getProductName();
        $success = $cart->removeItem($cartItem);

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove item from cart.',
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Failed to remove item from cart.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$productName}' removed from cart.",
                'cart_summary' => [
                    'item_count' => $cart->item_count,
                    'total_amount' => $cart->total_amount,
                ],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', "'{$productName}' removed from cart.");
    }

    /**
     * Clear all items from cart
     */
    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $cart = $this->getActiveCart($request);
        
        $success = $cart->clear();

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear cart.',
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Failed to clear cart.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully.',
                'cart_summary' => [
                    'item_count' => 0,
                    'total_amount' => 0,
                ],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared successfully.');
    }

    /**
     * Get cart count for header display
     */
    public function count(Request $request): JsonResponse
    {
        $cart = $this->getActiveCart($request);
        
        return response()->json([
            'count' => $cart->item_count,
            'unique_count' => $cart->unique_item_count,
        ]);
    }

    /**
     * Validate cart for checkout
     */
    public function validateForCheckout(Request $request): JsonResponse
    {
        $cart = $this->getActiveCart($request);
        $errors = $cart->validateForCheckout();

        // Additional validation for each cart item
        foreach ($cart->items as $item) {
            $itemErrors = $item->validate();
            $errors = array_merge($errors, $itemErrors);
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'cart_summary' => [
                'item_count' => $cart->item_count,
                'total_amount' => $cart->total_amount,
                'requires_prescription_verification' => $cart->requires_prescription_verification,
            ],
        ]);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $cart = $this->getActiveCart($request);
        
        // TODO: Implement coupon validation and application logic
        // For now, return a placeholder response
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon functionality not yet implemented.',
            ], 422);
        }

        return back()->withErrors(['coupon_code' => 'Coupon functionality not yet implemented.']);
    }

    /**
     * Update shipping method
     */
    public function updateShipping(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'shipping_method' => 'required|string|in:standard,express,overnight',
        ]);

        $cart = $this->getActiveCart($request);
        
        // Update cart metadata with shipping method
        $metadata = $cart->metadata ?? [];
        $metadata['shipping_method'] = $request->shipping_method;
        
        $cart->update(['metadata' => $metadata]);
        $cart->updateTotals(); // Recalculate shipping costs

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shipping method updated.',
                'cart_summary' => [
                    'shipping_amount' => $cart->shipping_amount,
                    'total_amount' => $cart->total_amount,
                ],
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Shipping method updated.');
    }

    /**
     * Restore abandoned cart
     */
    public function restore(Request $request, Cart $abandonedCart): RedirectResponse
    {
        // Ensure cart belongs to current user
        if (auth()->check() && $abandonedCart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to cart.');
        }

        if (!$abandonedCart->is_abandoned) {
            return redirect()->route('cart.index')
                ->with('info', 'Cart is already active.');
        }

        // Get current active cart
        $activeCart = $this->getActiveCart($request);

        // Move items from abandoned cart to active cart
        foreach ($abandonedCart->items as $item) {
            $activeCart->addItem(
                $item->product,
                $item->quantity,
                $item->prescription,
                [
                    'special_instructions' => $item->special_instructions,
                    'metadata' => $item->metadata,
                ]
            );
        }

        // Mark abandoned cart as restored
        $abandonedCart->restore();

        return redirect()->route('cart.index')
            ->with('success', 'Cart items restored successfully.');
    }

    /**
     * Get the active cart for the current user or session
     */
    protected function getActiveCart(Request $request): Cart
    {
        if (auth()->check()) {
            return Cart::getActiveCartForUser(auth()->user());
        }
        
        // For guest users, use session ID
        $sessionId = $request->session()->getId();
        return Cart::getActiveCartForSession($sessionId);
    }

    /**
     * Merge guest cart with user cart after login
     */
    public function mergeGuestCart(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'User must be authenticated.',
            ], 401);
        }

        $sessionId = $request->session()->getId();
        $guestCart = Cart::where('session_id', $sessionId)
            ->where('status', Cart::STATUS_ACTIVE)
            ->first();

        if (!$guestCart || $guestCart->is_empty) {
            return response()->json([
                'success' => true,
                'message' => 'No guest cart to merge.',
                'cart_summary' => [
                    'item_count' => 0,
                    'total_amount' => 0,
                ],
            ]);
        }

        $userCart = Cart::mergeGuestCartWithUserCart($guestCart, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Guest cart merged successfully.',
            'cart_summary' => [
                'item_count' => $userCart->item_count,
                'total_amount' => $userCart->total_amount,
            ],
        ]);
    }

    /**
     * Get abandoned carts for user
     */
    public function abandonedCarts(Request $request): View|JsonResponse
    {
        if (!auth()->check()) {
            abort(403, 'Authentication required.');
        }

        $abandonedCarts = Cart::where('user_id', auth()->id())
            ->where('status', Cart::STATUS_ABANDONED)
            ->with(['items.product'])
            ->orderBy('abandoned_at', 'desc')
            ->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'abandoned_carts' => $abandonedCarts,
            ]);
        }

        return view('cart.abandoned', [
            'abandonedCarts' => $abandonedCarts,
        ]);
    }
}