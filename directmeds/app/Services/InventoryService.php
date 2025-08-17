<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Check if sufficient inventory is available for an order
     */
    public function checkAvailability(Order $order): array
    {
        $unavailableItems = [];

        foreach ($order->items as $item) {
            if (!$this->isAvailable($item->product, $item->quantity_ordered)) {
                $unavailableItems[] = [
                    'item_id' => $item->id,
                    'product_name' => $item->getProductName(),
                    'requested_quantity' => $item->quantity_ordered,
                    'available_quantity' => $item->product->quantity_on_hand,
                ];
            }
        }

        return [
            'available' => empty($unavailableItems),
            'unavailable_items' => $unavailableItems,
        ];
    }

    /**
     * Check if sufficient inventory is available for a single product
     */
    public function isAvailable(Product $product, int $quantity): bool
    {
        return $product->canDispense($quantity);
    }

    /**
     * Reserve inventory for an order
     */
    public function reserveInventory(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (!$this->reserveProduct($item->product, $item->quantity_ordered)) {
                    throw new \Exception("Insufficient inventory for product: {$item->getProductName()}");
                }
            }
            return true;
        });
    }

    /**
     * Reserve inventory for a specific product
     */
    public function reserveProduct(Product $product, int $quantity): bool
    {
        if (!$this->isAvailable($product, $quantity)) {
            return false;
        }

        // In a more sophisticated system, you might create inventory reservations
        // For now, we'll just check availability at the time of fulfillment
        return true;
    }

    /**
     * Deduct inventory when order is fulfilled
     */
    public function deductInventory(OrderItem $orderItem, int $quantity): bool
    {
        return DB::transaction(function () use ($orderItem, $quantity) {
            $product = $orderItem->product;

            if (!$this->isAvailable($product, $quantity)) {
                return false;
            }

            // Deduct from inventory
            $product->decrement('quantity_on_hand', $quantity);

            // Log inventory movement
            $this->logInventoryMovement($product, 'deduction', $quantity, [
                'reason' => 'order_fulfillment',
                'order_id' => $orderItem->order_id,
                'order_item_id' => $orderItem->id,
                'performed_by' => auth()->id(),
            ]);

            // Check if product needs reordering
            if ($product->needsReorder()) {
                $this->triggerReorderAlert($product);
            }

            return true;
        });
    }

    /**
     * Restore inventory when order is cancelled or returned
     */
    public function restoreInventory(Product $product, int $quantity, string $reason = 'cancellation'): void
    {
        DB::transaction(function () use ($product, $quantity, $reason) {
            $product->increment('quantity_on_hand', $quantity);

            $this->logInventoryMovement($product, 'addition', $quantity, [
                'reason' => $reason,
                'performed_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Adjust inventory levels
     */
    public function adjustInventory(Product $product, int $newQuantity, string $reason): void
    {
        DB::transaction(function () use ($product, $newQuantity, $reason) {
            $oldQuantity = $product->quantity_on_hand;
            $difference = $newQuantity - $oldQuantity;

            $product->update(['quantity_on_hand' => $newQuantity]);

            $movementType = $difference > 0 ? 'addition' : 'deduction';
            $quantity = abs($difference);

            $this->logInventoryMovement($product, $movementType, $quantity, [
                'reason' => $reason,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'performed_by' => auth()->id(),
            ]);

            if ($product->needsReorder()) {
                $this->triggerReorderAlert($product);
            }
        });
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(): Collection
    {
        return Product::lowStock()
            ->active()
            ->available()
            ->with(['manufacturer', 'category'])
            ->get();
    }

    /**
     * Get products expiring soon
     */
    public function getExpiringSoonProducts(int $days = 30): Collection
    {
        return Product::expiringSoon($days)
            ->active()
            ->available()
            ->with(['manufacturer', 'category'])
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::where('quantity_on_hand', '<=', 0)
            ->active()
            ->available()
            ->with(['manufacturer', 'category'])
            ->get();
    }

    /**
     * Bulk update inventory
     */
    public function bulkUpdateInventory(array $updates): array
    {
        $results = [];

        DB::transaction(function () use ($updates, &$results) {
            foreach ($updates as $update) {
                try {
                    $product = Product::findOrFail($update['product_id']);
                    $this->adjustInventory(
                        $product,
                        $update['quantity'],
                        $update['reason'] ?? 'bulk_update'
                    );
                    
                    $results[] = [
                        'product_id' => $product->id,
                        'success' => true,
                        'message' => 'Inventory updated successfully',
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'product_id' => $update['product_id'],
                        'success' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(): array
    {
        $totalProducts = Product::active()->count();
        $inStockProducts = Product::inStock()->active()->count();
        $lowStockProducts = $this->getLowStockProducts()->count();
        $outOfStockProducts = $this->getOutOfStockProducts()->count();
        $expiringSoonProducts = $this->getExpiringSoonProducts()->count();

        $totalValue = Product::active()->sum(DB::raw('quantity_on_hand * cost_price'));

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'in_stock_products' => $inStockProducts,
                'low_stock_products' => $lowStockProducts,
                'out_of_stock_products' => $outOfStockProducts,
                'expiring_soon_products' => $expiringSoonProducts,
                'total_inventory_value' => $totalValue,
                'stock_percentage' => $totalProducts > 0 ? round(($inStockProducts / $totalProducts) * 100, 2) : 0,
            ],
            'alerts' => [
                'low_stock' => $this->getLowStockProducts(),
                'out_of_stock' => $this->getOutOfStockProducts(),
                'expiring_soon' => $this->getExpiringSoonProducts(),
            ],
            'movements' => $this->getRecentInventoryMovements(),
        ];
    }

    /**
     * Get inventory movements for a product
     */
    public function getProductInventoryHistory(Product $product, int $limit = 50): Collection
    {
        // In a full implementation, you'd have a dedicated inventory_movements table
        // For now, we'll simulate this with activity logs
        return collect(); // Placeholder
    }

    /**
     * Check inventory for cart checkout
     */
    public function validateCartInventory(\App\Models\Cart $cart): array
    {
        $errors = [];

        foreach ($cart->items as $item) {
            if (!$this->isAvailable($item->product, $item->quantity)) {
                $errors[] = "Product '{$item->getProductName()}' is not available in the requested quantity. Available: {$item->product->quantity_on_hand}";
            }
        }

        return $errors;
    }

    /**
     * Auto-reorder products that are below minimum stock
     */
    public function autoReorderProducts(): array
    {
        $reorderProducts = $this->getLowStockProducts();
        $reorders = [];

        foreach ($reorderProducts as $product) {
            if ($this->shouldAutoReorder($product)) {
                $reorderQuantity = $this->calculateReorderQuantity($product);
                
                // In production, this would integrate with supplier APIs
                $reorders[] = [
                    'product' => $product,
                    'reorder_quantity' => $reorderQuantity,
                    'estimated_cost' => $reorderQuantity * $product->cost_price,
                    'supplier' => $product->manufacturer?->name ?? 'Unknown',
                ];
            }
        }

        return $reorders;
    }

    /**
     * Check if product should be auto-reordered
     */
    protected function shouldAutoReorder(Product $product): bool
    {
        // Check if auto-reorder is enabled for this product
        // In production, this would be a product setting
        return $product->quantity_on_hand <= $product->minimum_stock_level;
    }

    /**
     * Calculate optimal reorder quantity
     */
    protected function calculateReorderQuantity(Product $product): int
    {
        // Simple reorder logic - bring up to maximum stock level
        $reorderQuantity = $product->maximum_stock_level - $product->quantity_on_hand;
        
        // Minimum reorder quantity
        return max($reorderQuantity, 10);
    }

    /**
     * Log inventory movement (placeholder for audit trail)
     */
    protected function logInventoryMovement(Product $product, string $type, int $quantity, array $metadata = []): void
    {
        // In a full implementation, you'd have an inventory_movements table
        // For now, we'll use the activity log
        activity('inventory_movement')
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties(array_merge([
                'movement_type' => $type,
                'quantity' => $quantity,
                'new_stock_level' => $product->quantity_on_hand,
                'timestamp' => now()->toISOString(),
            ], $metadata))
            ->log("Inventory {$type}: {$quantity} units of {$product->name}");
    }

    /**
     * Trigger reorder alert
     */
    protected function triggerReorderAlert(Product $product): void
    {
        // In production, this would send notifications to relevant staff
        activity('inventory_alert')
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties([
                'alert_type' => 'low_stock',
                'current_quantity' => $product->quantity_on_hand,
                'minimum_level' => $product->minimum_stock_level,
                'suggested_reorder' => $this->calculateReorderQuantity($product),
            ])
            ->log("Low stock alert for {$product->name}");
    }

    /**
     * Get recent inventory movements
     */
    protected function getRecentInventoryMovements(int $limit = 20): Collection
    {
        // In a full implementation, query the inventory_movements table
        // For now, return placeholder data
        return collect();
    }

    /**
     * Calculate ABC analysis for inventory management
     */
    public function calculateAbcAnalysis(): array
    {
        // Get products with their sales data
        $products = Product::with(['manufacturer', 'category'])
            ->selectRaw('products.*, COALESCE(SUM(order_items.total_price), 0) as total_revenue')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.created_at', '>=', now()->subYear())
                     ->where('orders.payment_status', '=', Order::PAYMENT_PAID);
            })
            ->groupBy('products.id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $products->sum('total_revenue');
        $runningTotal = 0;

        $aClass = [];
        $bClass = [];
        $cClass = [];

        foreach ($products as $product) {
            $runningTotal += $product->total_revenue;
            $percentage = $totalRevenue > 0 ? ($runningTotal / $totalRevenue) * 100 : 0;

            if ($percentage <= 80) {
                $aClass[] = $product;
                $product->abc_class = 'A';
            } elseif ($percentage <= 95) {
                $bClass[] = $product;
                $product->abc_class = 'B';
            } else {
                $cClass[] = $product;
                $product->abc_class = 'C';
            }
        }

        return [
            'a_class' => $aClass, // High value products (80% of revenue)
            'b_class' => $bClass, // Medium value products (15% of revenue)
            'c_class' => $cClass, // Low value products (5% of revenue)
            'summary' => [
                'a_class_count' => count($aClass),
                'b_class_count' => count($bClass),
                'c_class_count' => count($cClass),
                'total_products' => $products->count(),
                'total_revenue' => $totalRevenue,
            ],
        ];
    }
}