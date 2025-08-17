<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders for the authenticated user
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Order::with(['items.product', 'processingPharmacist', 'fulfillmentPharmacist']);

        // Filter by authenticated user if patient
        if (auth()->user()->isPatient()) {
            $query->where('user_id', auth()->id());
        }

        // Search filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhereJsonContains('customer_info->name', $request->search)
                  ->orWhereJsonContains('customer_info->email', $request->search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('fulfillment_status')) {
            $query->where('fulfillment_status', $request->fulfillment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'orders' => $orders,
                'filters' => [
                    'statuses' => Order::getStatuses(),
                    'payment_statuses' => Order::getPaymentStatuses(),
                    'fulfillment_statuses' => Order::getFulfillmentStatuses(),
                ],
            ]);
        }

        return view('orders.index', [
            'orders' => $orders,
            'filters' => [
                'statuses' => Order::getStatuses(),
                'payment_statuses' => Order::getPaymentStatuses(),
                'fulfillment_statuses' => Order::getFulfillmentStatuses(),
            ],
        ]);
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order): View|JsonResponse
    {
        // Authorization check
        if (auth()->user()->isPatient() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load([
            'items.product',
            'items.prescription',
            'items.prescriptionVerifiedBy',
            'items.fulfilledBy',
            'user',
            'processingPharmacist',
            'fulfillmentPharmacist',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'order' => $order,
            ]);
        }

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Show order confirmation page
     */
    public function confirmation(Request $request, Order $order): View|RedirectResponse
    {
        // Authorization check
        if (auth()->user()->isPatient() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load(['items.product', 'items.prescription']);

        return view('orders.confirmation', [
            'order' => $order,
        ]);
    }

    /**
     * Track order status
     */
    public function track(Request $request, string $orderNumber): View|JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Authorization check for patients
        if (auth()->check() && auth()->user()->isPatient() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load(['items.product']);

        $trackingInfo = [
            'order' => $order,
            'tracking_events' => $this->getTrackingEvents($order),
            'estimated_delivery' => $order->estimated_delivery_date,
            'current_status' => $order->status,
        ];

        if ($request->expectsJson()) {
            return response()->json($trackingInfo);
        }

        return view('orders.track', $trackingInfo);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Authorization check
        if (auth()->user()->isPatient() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$order->canBeCancelled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled at this time.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order cannot be cancelled at this time.']);
        }

        try {
            $this->orderService->cancelOrder($order, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully.',
                    'order' => $order->fresh(),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to cancel order: ' . $e->getMessage()]);
        }
    }

    /**
     * Process order (Pharmacy staff only)
     */
    public function process(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Authorization check - only pharmacy staff
        if (!auth()->user()->isPharmacist() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$order->readyForProcessing()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not ready for processing.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order is not ready for processing.']);
        }

        try {
            $this->orderService->processOrder($order);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order processing started.',
                    'order' => $order->fresh(),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order processing started.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process order: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to process order: ' . $e->getMessage()]);
        }
    }

    /**
     * Ship order (Pharmacy staff only)
     */
    public function ship(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Authorization check - only pharmacy staff
        if (!auth()->user()->isPharmacist() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'carrier' => 'required|string|max:50',
            'shipping_details' => 'nullable|array',
        ]);

        if (!$order->canBeShipped()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be shipped at this time.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order cannot be shipped at this time.']);
        }

        try {
            $this->orderService->shipOrder(
                $order,
                $request->tracking_number,
                $request->carrier,
                $request->input('shipping_details')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order shipped successfully.',
                    'order' => $order->fresh(),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order shipped successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to ship order: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to ship order: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark order as delivered
     */
    public function delivered(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Authorization check - only pharmacy staff
        if (!auth()->user()->isPharmacist() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$order->is_shipped) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be shipped before marking as delivered.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order must be shipped before marking as delivered.']);
        }

        try {
            $this->orderService->markAsDelivered($order);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order marked as delivered.',
                    'order' => $order->fresh(),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order marked as delivered.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to update order: ' . $e->getMessage()]);
        }
    }

    /**
     * Verify prescriptions for order (Pharmacy staff only)
     */
    public function verifyPrescriptions(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        // Authorization check - only pharmacy staff
        if (!auth()->user()->isPharmacist() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$order->requires_prescription_verification) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not require prescription verification.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order does not require prescription verification.']);
        }

        try {
            $allVerified = $this->orderService->verifyPrescriptions($order);

            $message = $allVerified 
                ? 'All prescriptions verified successfully.'
                : 'Some prescriptions could not be verified.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $allVerified,
                    'message' => $message,
                    'order' => $order->fresh(['items.prescription']),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with($allVerified ? 'success' : 'warning', $message);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify prescriptions: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to verify prescriptions: ' . $e->getMessage()]);
        }
    }

    /**
     * Fulfill order item (Pharmacy staff only)
     */
    public function fulfillItem(Request $request, OrderItem $orderItem): JsonResponse|RedirectResponse
    {
        // Authorization check - only pharmacy staff
        if (!auth()->user()->isPharmacist() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string|max:50',
            'expiration_date' => 'nullable|date|after:today',
            'manufacturer' => 'nullable|string|max:100',
            'ndc_dispensed' => 'nullable|string|max:20',
            'pharmacist_notes' => 'nullable|string|max:1000',
        ]);

        if (!$orderItem->canBeFulfilled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item cannot be fulfilled at this time.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Order item cannot be fulfilled at this time.']);
        }

        try {
            $fulfillmentData = $request->only([
                'lot_number',
                'expiration_date',
                'manufacturer',
                'ndc_dispensed',
                'pharmacist_notes',
            ]);

            $success = $this->orderService->fulfillOrderItem($orderItem, $request->quantity, $fulfillmentData);

            if (!$success) {
                throw new \Exception('Failed to fulfill order item - insufficient inventory.');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order item fulfilled successfully.',
                    'order_item' => $orderItem->fresh(),
                ]);
            }

            return redirect()->route('orders.show', $orderItem->order)
                ->with('success', 'Order item fulfilled successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fulfill order item: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => 'Failed to fulfill order item: ' . $e->getMessage()]);
        }
    }

    /**
     * Get order analytics (Admin/Pharmacy staff only)
     */
    public function analytics(Request $request): JsonResponse|View
    {
        // Authorization check - only admin or pharmacy staff
        if (!auth()->user()->isAdmin() && !auth()->user()->isPharmacist()) {
            abort(403, 'Unauthorized access.');
        }

        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $analytics = $this->orderService->getOrderAnalytics($dateFrom, $dateTo);

        if ($request->expectsJson()) {
            return response()->json($analytics);
        }

        return view('orders.analytics', [
            'analytics' => $analytics,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Get tracking events for an order
     */
    protected function getTrackingEvents(Order $order): array
    {
        $events = [];

        // Order created
        $events[] = [
            'status' => 'Order Placed',
            'description' => 'Your order has been successfully placed.',
            'timestamp' => $order->created_at,
            'completed' => true,
        ];

        // Payment processed
        if ($order->payment_processed_at) {
            $events[] = [
                'status' => 'Payment Processed',
                'description' => 'Payment has been successfully processed.',
                'timestamp' => $order->payment_processed_at,
                'completed' => true,
            ];
        }

        // Prescription verification
        if ($order->prescription_verification_completed_at) {
            $events[] = [
                'status' => 'Prescriptions Verified',
                'description' => 'All prescriptions have been verified by our pharmacy team.',
                'timestamp' => $order->prescription_verification_completed_at,
                'completed' => true,
            ];
        }

        // Order processing
        if ($order->is_processing) {
            $events[] = [
                'status' => 'Order Processing',
                'description' => 'Your order is being prepared by our pharmacy team.',
                'timestamp' => $order->updated_at,
                'completed' => true,
            ];
        }

        // Order shipped
        if ($order->shipped_at) {
            $events[] = [
                'status' => 'Order Shipped',
                'description' => "Your order has been shipped via {$order->carrier}.",
                'timestamp' => $order->shipped_at,
                'completed' => true,
                'tracking_number' => $order->tracking_number,
            ];
        }

        // Order delivered
        if ($order->delivered_at) {
            $events[] = [
                'status' => 'Order Delivered',
                'description' => 'Your order has been successfully delivered.',
                'timestamp' => $order->delivered_at,
                'completed' => true,
            ];
        }

        return $events;
    }
}