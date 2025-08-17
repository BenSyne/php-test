<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show checkout form
     */
    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->getActiveCart($request);

        // Validate cart has items
        if ($cart->is_empty) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add items before checkout.');
        }

        // Validate cart for checkout
        $validationErrors = $cart->validateForCheckout();
        if (!empty($validationErrors)) {
            return redirect()->route('cart.index')
                ->withErrors(['checkout' => implode(' ', $validationErrors)]);
        }

        // Get user's saved addresses if authenticated
        $savedAddresses = [];
        if (auth()->check() && auth()->user()->profile) {
            $profile = auth()->user()->profile;
            if ($profile->address) {
                $savedAddresses[] = [
                    'type' => 'primary',
                    'name' => 'Primary Address',
                    'street_address' => $profile->address,
                    'city' => $profile->city,
                    'state' => $profile->state,
                    'postal_code' => $profile->postal_code,
                    'country' => $profile->country ?? 'US',
                ];
            }
        }

        return view('checkout.index', [
            'cart' => $cart->load(['items.product', 'items.prescription']),
            'user' => auth()->user(),
            'savedAddresses' => $savedAddresses,
        ]);
    }

    /**
     * Process checkout and create order
     */
    public function process(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            // Customer information
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            
            // Billing address
            'billing_address.street_address' => 'required|string|max:255',
            'billing_address.apartment' => 'nullable|string|max:100',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.state' => 'required|string|max:50',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:2',
            
            // Shipping address
            'shipping_same_as_billing' => 'boolean',
            'shipping_address.street_address' => 'required_if:shipping_same_as_billing,false|string|max:255',
            'shipping_address.apartment' => 'nullable|string|max:100',
            'shipping_address.city' => 'required_if:shipping_same_as_billing,false|string|max:100',
            'shipping_address.state' => 'required_if:shipping_same_as_billing,false|string|max:50',
            'shipping_address.postal_code' => 'required_if:shipping_same_as_billing,false|string|max:20',
            'shipping_address.country' => 'required_if:shipping_same_as_billing,false|string|max:2',
            
            // Payment information
            'payment_method' => 'required|string|in:credit_card,insurance,cash',
            'payment_details.card_number' => 'required_if:payment_method,credit_card|string|max:20',
            'payment_details.expiry_month' => 'required_if:payment_method,credit_card|integer|min:1|max:12',
            'payment_details.expiry_year' => 'required_if:payment_method,credit_card|integer|min:2024|max:2034',
            'payment_details.cvv' => 'required_if:payment_method,credit_card|string|min:3|max:4',
            'payment_details.cardholder_name' => 'required_if:payment_method,credit_card|string|max:255',
            
            // Insurance information (if applicable)
            'insurance_info.provider' => 'nullable|string|max:255',
            'insurance_info.policy_number' => 'nullable|string|max:100',
            'insurance_info.group_number' => 'nullable|string|max:100',
            'insurance_info.member_id' => 'nullable|string|max:100',
            
            // Shipping preferences
            'shipping_method' => 'required|string|in:standard,express,overnight',
            'special_instructions' => 'nullable|string|max:1000',
            'requested_delivery_date' => 'nullable|date|after:today',
        ]);

        $cart = $this->getActiveCart($request);

        // Final cart validation
        $validationErrors = $cart->validateForCheckout();
        if (!empty($validationErrors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart validation failed.',
                    'errors' => $validationErrors,
                ], 422);
            }
            
            return back()->withErrors(['checkout' => implode(' ', $validationErrors)]);
        }

        try {
            DB::beginTransaction();

            // Prepare shipping address
            $shippingAddress = $request->boolean('shipping_same_as_billing') 
                ? $request->input('billing_address')
                : $request->input('shipping_address');

            // Create order data
            $orderData = [
                'customer_info' => [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                ],
                'billing_address' => $request->input('billing_address'),
                'shipping_address' => $shippingAddress,
                'payment_method' => $request->payment_method,
                'payment_details' => $this->processPaymentDetails($request),
                'insurance_info' => $request->input('insurance_info'),
                'shipping_method' => $request->shipping_method,
                'special_instructions' => $request->special_instructions,
                'requested_delivery_date' => $request->requested_delivery_date,
                'source' => 'web',
            ];

            // Create order using the service
            $order = $this->orderService->createOrderFromCart($cart, $orderData);

            // Process payment
            $paymentResult = $this->orderService->processPayment($order, $request->input('payment_details'));
            
            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order' => [
                        'order_number' => $order->order_number,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                    ],
                    'redirect_url' => route('orders.confirmation', $order),
                ]);
            }

            return redirect()->route('orders.confirmation', $order)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process order: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['checkout' => 'Failed to process order: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate checkout data
     */
    public function validate(Request $request): JsonResponse
    {
        $cart = $this->getActiveCart($request);

        // Validate cart
        $cartErrors = $cart->validateForCheckout();

        // Validate each cart item
        $itemErrors = [];
        foreach ($cart->items as $item) {
            $errors = $item->validate();
            if (!empty($errors)) {
                $itemErrors[$item->id] = $errors;
            }
        }

        // Check inventory availability
        $inventoryErrors = [];
        foreach ($cart->items as $item) {
            if (!$item->product->canDispense($item->quantity)) {
                $inventoryErrors[] = "Product '{$item->getProductName()}' is not available in the requested quantity";
            }
        }

        $allErrors = array_merge($cartErrors, array_values($itemErrors), $inventoryErrors);

        return response()->json([
            'valid' => empty($allErrors),
            'errors' => [
                'cart' => $cartErrors,
                'items' => $itemErrors,
                'inventory' => $inventoryErrors,
                'all' => $allErrors,
            ],
            'cart_summary' => [
                'item_count' => $cart->item_count,
                'total_amount' => $cart->total_amount,
                'requires_prescription_verification' => $cart->requires_prescription_verification,
            ],
        ]);
    }

    /**
     * Calculate shipping costs
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_method' => 'required|string|in:standard,express,overnight',
        ]);

        $cart = $this->getActiveCart($request);
        
        // Update cart with shipping method and recalculate
        $metadata = $cart->metadata ?? [];
        $metadata['shipping_method'] = $request->shipping_method;
        $metadata['shipping_address'] = $request->shipping_address;
        
        $cart->update(['metadata' => $metadata]);
        $cart->updateTotals();

        return response()->json([
            'shipping_cost' => $cart->shipping_amount,
            'tax_amount' => $cart->tax_amount,
            'total_amount' => $cart->total_amount,
            'estimated_delivery' => $this->calculateEstimatedDelivery($request->shipping_method),
        ]);
    }

    /**
     * Apply discount code
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'discount_code' => 'required|string|max:50',
        ]);

        // TODO: Implement discount code logic
        return response()->json([
            'success' => false,
            'message' => 'Discount functionality not yet implemented.',
        ], 422);
    }

    /**
     * Save order as draft
     */
    public function saveDraft(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to save draft.',
            ], 401);
        }

        $cart = $this->getActiveCart($request);

        // Save form data to cart metadata
        $metadata = $cart->metadata ?? [];
        $metadata['draft_checkout_data'] = [
            'customer_info' => $request->only(['customer_name', 'customer_email', 'customer_phone']),
            'billing_address' => $request->input('billing_address'),
            'shipping_address' => $request->input('shipping_address'),
            'shipping_same_as_billing' => $request->boolean('shipping_same_as_billing'),
            'shipping_method' => $request->shipping_method,
            'insurance_info' => $request->input('insurance_info'),
            'special_instructions' => $request->special_instructions,
            'saved_at' => now()->toISOString(),
        ];

        $cart->update(['metadata' => $metadata]);

        return response()->json([
            'success' => true,
            'message' => 'Checkout information saved as draft.',
        ]);
    }

    /**
     * Load draft checkout data
     */
    public function loadDraft(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $cart = $this->getActiveCart($request);
        $draftData = $cart->metadata['draft_checkout_data'] ?? null;

        return response()->json([
            'success' => true,
            'draft_data' => $draftData,
        ]);
    }

    /**
     * Process payment details (sanitize and encrypt sensitive data)
     */
    protected function processPaymentDetails(Request $request): array
    {
        $paymentDetails = $request->input('payment_details', []);
        
        if ($request->payment_method === 'credit_card') {
            // Mask credit card number (keep only last 4 digits)
            if (isset($paymentDetails['card_number'])) {
                $cardNumber = preg_replace('/\D/', '', $paymentDetails['card_number']);
                $paymentDetails['card_last_four'] = substr($cardNumber, -4);
                $paymentDetails['card_number'] = '****-****-****-' . substr($cardNumber, -4);
            }
            
            // Remove CVV for security
            unset($paymentDetails['cvv']);
        }

        return $paymentDetails;
    }

    /**
     * Calculate estimated delivery date
     */
    protected function calculateEstimatedDelivery(string $shippingMethod): string
    {
        $businessDays = match($shippingMethod) {
            'standard' => 5,
            'express' => 3,
            'overnight' => 1,
            default => 5,
        };

        $estimatedDate = now()->addWeekdays($businessDays);
        
        return $estimatedDate->format('M j, Y');
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
}