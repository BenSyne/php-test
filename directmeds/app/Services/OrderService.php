<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create an order from a cart
     */
    public function createOrderFromCart(Cart $cart, array $orderData): Order
    {
        return DB::transaction(function () use ($cart, $orderData) {
            // Validate cart one more time
            $validationErrors = $cart->validateForCheckout();
            if (!empty($validationErrors)) {
                throw new \Exception('Cart validation failed: ' . implode(', ', $validationErrors));
            }

            // Create order
            $order = Order::create(array_merge([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $cart->user_id,
                'cart_id' => $cart->id,
                'subtotal' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'shipping_amount' => $cart->shipping_amount,
                'total_amount' => $cart->total_amount,
                'source' => 'web',
            ], $orderData));

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                $orderItemData = $cartItem->toOrderItemData();
                $order->items()->create($orderItemData);
            }

            // Mark cart as converted
            $cart->markAsConverted($order);

            // Add audit trail entry
            $order->addToAuditTrail('order_created', [
                'cart_id' => $cart->id,
                'item_count' => $cart->items->count(),
                'total_amount' => $order->total_amount,
                'created_by' => auth()->id(),
            ]);

            return $order;
        });
    }

    /**
     * Process payment for an order
     */
    public function processPayment(Order $order, array $paymentDetails): array
    {
        try {
            // Simulate payment processing
            // In a real implementation, this would integrate with a payment gateway
            
            $paymentMethod = $order->payment_method;
            
            switch ($paymentMethod) {
                case 'credit_card':
                    return $this->processCreditCardPayment($order, $paymentDetails);
                
                case 'insurance':
                    return $this->processInsurancePayment($order, $paymentDetails);
                
                case 'cash':
                    return $this->processCashPayment($order, $paymentDetails);
                
                default:
                    throw new \Exception('Unsupported payment method: ' . $paymentMethod);
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'PAYMENT_FAILED',
            ];
        }
    }

    /**
     * Process credit card payment
     */
    protected function processCreditCardPayment(Order $order, array $paymentDetails): array
    {
        // Simulate credit card processing
        // In production, integrate with Stripe, Square, etc.
        
        // Basic validation
        if (empty($paymentDetails['card_number']) || empty($paymentDetails['cvv'])) {
            throw new \Exception('Missing required payment information');
        }

        // Simulate payment processing delay
        usleep(500000); // 0.5 seconds

        // Mark as paid
        $order->markAsPaid([
            'payment_gateway' => 'stripe', // Example
            'transaction_id' => 'txn_' . uniqid(),
            'amount_charged' => $order->total_amount,
            'gateway_response' => 'approved',
        ]);

        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => 'txn_' . uniqid(),
        ];
    }

    /**
     * Process insurance payment
     */
    protected function processInsurancePayment(Order $order, array $paymentDetails): array
    {
        // Simulate insurance processing
        // In production, integrate with insurance verification systems
        
        // Calculate insurance coverage for each item
        foreach ($order->items as $item) {
            // This would involve real insurance verification
            $coveragePercentage = 0.8; // 80% coverage example
            $insuranceCoverage = $item->total_price * $coveragePercentage;
            $patientCopay = $item->total_price - $insuranceCoverage;
            
            $item->update([
                'insurance_coverage' => $insuranceCoverage,
                'insurance_copay' => $patientCopay,
                'patient_pay_amount' => $patientCopay,
            ]);
        }

        // Update order totals
        $totalInsuranceCoverage = $order->items->sum('insurance_coverage');
        $totalPatientPay = $order->items->sum('patient_pay_amount');
        
        $order->update([
            'insurance_coverage' => $totalInsuranceCoverage,
            'insurance_copay' => $totalPatientPay,
        ]);

        // Mark as paid (assuming copay is processed)
        $order->markAsPaid([
            'payment_method' => 'insurance',
            'insurance_coverage' => $totalInsuranceCoverage,
            'patient_copay' => $totalPatientPay,
        ]);

        return [
            'success' => true,
            'message' => 'Insurance payment processed successfully',
            'insurance_coverage' => $totalInsuranceCoverage,
            'patient_copay' => $totalPatientPay,
        ];
    }

    /**
     * Process cash payment
     */
    protected function processCashPayment(Order $order, array $paymentDetails): array
    {
        // For cash payments, mark as pending payment
        $order->update([
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => Order::STATUS_PROCESSING,
        ]);

        $order->addToAuditTrail('cash_payment_pending', [
            'total_amount' => $order->total_amount,
            'payment_method' => 'cash',
        ]);

        return [
            'success' => true,
            'message' => 'Order created. Payment will be collected upon pickup.',
            'payment_pending' => true,
        ];
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            // Restore inventory for fulfilled items
            foreach ($order->items as $item) {
                if ($item->quantity_fulfilled > 0) {
                    $this->inventoryService->restoreInventory(
                        $item->product,
                        $item->quantity_fulfilled
                    );
                }
            }

            // Cancel the order
            $order->cancel($reason, auth()->id());

            // If payment was processed, initiate refund
            if ($order->is_paid) {
                $this->initiateRefund($order, $order->total_amount, 'Order cancellation');
            }
        });
    }

    /**
     * Process an order (start fulfillment)
     */
    public function processOrder(Order $order): void
    {
        if (!$order->readyForProcessing()) {
            throw new \Exception('Order is not ready for processing');
        }

        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'fulfillment_status' => Order::FULFILLMENT_PROCESSING,
            'processing_pharmacist_id' => auth()->id(),
        ]);

        $order->addToAuditTrail('order_processing_started', [
            'pharmacist_id' => auth()->id(),
            'started_at' => now()->toISOString(),
        ]);
    }

    /**
     * Ship an order
     */
    public function shipOrder(Order $order, string $trackingNumber, string $carrier, ?array $shippingDetails = null): void
    {
        if (!$order->canBeShipped()) {
            throw new \Exception('Order cannot be shipped at this time');
        }

        // Deduct inventory when shipping
        if (!$order->deductInventory()) {
            throw new \Exception('Insufficient inventory to fulfill order');
        }

        // Mark order as shipped
        $order->markAsShipped($trackingNumber, $carrier, $shippingDetails);

        // Update individual items as shipped
        foreach ($order->items as $item) {
            if ($item->quantity_fulfilled > 0) {
                $item->ship($item->quantity_fulfilled, $trackingNumber);
            }
        }
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(Order $order): void
    {
        $order->markAsDelivered();

        // Mark all items as delivered
        foreach ($order->items as $item) {
            $item->markAsDelivered();
        }
    }

    /**
     * Verify prescriptions for an order
     */
    public function verifyPrescriptions(Order $order): bool
    {
        return $order->verifyPrescriptions(auth()->id());
    }

    /**
     * Fulfill an order item
     */
    public function fulfillOrderItem(OrderItem $orderItem, int $quantity, array $fulfillmentData = []): bool
    {
        return $orderItem->fulfill($quantity, $fulfillmentData);
    }

    /**
     * Initiate a refund
     */
    public function initiateRefund(Order $order, float $amount, string $reason): void
    {
        // In production, this would integrate with payment gateway
        $order->refund($amount, $reason);

        $order->addToAuditTrail('refund_initiated', [
            'amount' => $amount,
            'reason' => $reason,
            'initiated_by' => auth()->id(),
            'initiated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get order analytics
     */
    public function getOrderAnalytics(string $dateFrom, string $dateTo): array
    {
        $orders = Order::whereBetween('created_at', [$dateFrom, $dateTo]);

        $analytics = [
            'summary' => [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total_amount'),
                'average_order_value' => $orders->avg('total_amount'),
                'orders_by_status' => $orders->groupBy('status')->map->count(),
                'orders_by_payment_status' => $orders->groupBy('payment_status')->map->count(),
            ],
            'trends' => [
                'daily_orders' => $this->getDailyOrderTrends($dateFrom, $dateTo),
                'daily_revenue' => $this->getDailyRevenueTrends($dateFrom, $dateTo),
            ],
            'prescription_stats' => [
                'orders_with_prescriptions' => $orders->whereHas('items', function ($q) {
                    $q->where('requires_prescription', true);
                })->count(),
                'prescription_verification_time' => $this->getAveragePrescriptionVerificationTime($dateFrom, $dateTo),
            ],
            'fulfillment_stats' => [
                'average_fulfillment_time' => $this->getAverageFulfillmentTime($dateFrom, $dateTo),
                'same_day_shipments' => $this->getSameDayShipmentsCount($dateFrom, $dateTo),
            ],
            'top_products' => $this->getTopProducts($dateFrom, $dateTo),
        ];

        return $analytics;
    }

    /**
     * Get daily order trends
     */
    protected function getDailyOrderTrends(string $dateFrom, string $dateTo): array
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get daily revenue trends
     */
    protected function getDailyRevenueTrends(string $dateFrom, string $dateTo): array
    {
        return Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('payment_status', Order::PAYMENT_PAID)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();
    }

    /**
     * Get average prescription verification time
     */
    protected function getAveragePrescriptionVerificationTime(string $dateFrom, string $dateTo): ?float
    {
        $orders = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('prescription_verification_completed_at')
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($orders as $order) {
            $verificationTime = $order->created_at->diffInMinutes($order->prescription_verification_completed_at);
            $totalMinutes += $verificationTime;
            $count++;
        }

        return $count > 0 ? round($totalMinutes / $count, 2) : null;
    }

    /**
     * Get average fulfillment time
     */
    protected function getAverageFulfillmentTime(string $dateFrom, string $dateTo): ?float
    {
        $orders = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('shipped_at')
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($orders as $order) {
            $fulfillmentTime = $order->created_at->diffInHours($order->shipped_at);
            $totalHours += $fulfillmentTime;
            $count++;
        }

        return $count > 0 ? round($totalHours / $count, 2) : null;
    }

    /**
     * Get same day shipments count
     */
    protected function getSameDayShipmentsCount(string $dateFrom, string $dateTo): int
    {
        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('shipped_at')
            ->whereRaw('DATE(created_at) = DATE(shipped_at)')
            ->count();
    }

    /**
     * Get top products by quantity sold
     */
    protected function getTopProducts(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        return OrderItem::selectRaw('product_id, product_name, SUM(quantity_ordered) as total_quantity, SUM(total_price) as total_revenue')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->where('payment_status', Order::PAYMENT_PAID);
            })
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Calculate estimated delivery date
     */
    public function calculateEstimatedDeliveryDate(string $shippingMethod, ?string $zipCode = null): Carbon
    {
        $businessDays = match(strtolower($shippingMethod)) {
            'standard' => 5,
            'express' => 3,
            'overnight' => 1,
            'two_day' => 2,
            default => 5,
        };

        // Adjust for location if zip code provided
        if ($zipCode) {
            // In production, you'd integrate with shipping APIs to get accurate estimates
            // For now, just add extra day for remote areas
            if ($this->isRemoteArea($zipCode)) {
                $businessDays += 1;
            }
        }

        return now()->addWeekdays($businessDays);
    }

    /**
     * Check if zip code is in a remote area
     */
    protected function isRemoteArea(string $zipCode): bool
    {
        // Simplified check - in production you'd have a proper lookup
        $remoteZipPrefixes = ['997', '998', '996', '995']; // Alaska, Hawaii, etc.
        
        foreach ($remoteZipPrefixes as $prefix) {
            if (str_starts_with($zipCode, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShippingCost(Cart $cart, array $shippingAddress, string $shippingMethod): float
    {
        $baseWeight = $cart->items->sum('quantity') * 0.1; // Assume 0.1 lbs per item
        $subtotal = $cart->subtotal;

        // Free shipping threshold
        if ($subtotal >= 50) {
            return 0.00;
        }

        // Base shipping rates
        $rates = [
            'standard' => 9.99,
            'express' => 19.99,
            'overnight' => 39.99,
            'two_day' => 24.99,
        ];

        $baseCost = $rates[$shippingMethod] ?? $rates['standard'];

        // Adjust for weight
        if ($baseWeight > 2) {
            $baseCost += ($baseWeight - 2) * 2.50;
        }

        // Adjust for location
        if ($this->isRemoteArea($shippingAddress['postal_code'] ?? '')) {
            $baseCost += 10.00;
        }

        return round($baseCost, 2);
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(Cart $cart, array $shippingAddress): float
    {
        $subtotal = $cart->subtotal;
        $state = $shippingAddress['state'] ?? '';

        // State tax rates (simplified)
        $taxRates = [
            'CA' => 0.0875, // California
            'NY' => 0.08,   // New York
            'TX' => 0.0825, // Texas
            'FL' => 0.06,   // Florida
            // Add more states as needed
        ];

        $taxRate = $taxRates[$state] ?? 0.05; // Default 5% if state not found

        return round($subtotal * $taxRate, 2);
    }
}