<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_number',
        'user_id',
        'cart_id',
        'status',
        'payment_status',
        'fulfillment_status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'customer_info',
        'billing_address',
        'shipping_address',
        'payment_method',
        'payment_reference',
        'payment_details',
        'payment_processed_at',
        'insurance_info',
        'insurance_copay',
        'insurance_coverage',
        'shipping_method',
        'tracking_number',
        'carrier',
        'shipped_at',
        'delivered_at',
        'shipping_details',
        'processing_pharmacist_id',
        'fulfillment_pharmacist_id',
        'prescription_verification_completed_at',
        'customer_notes',
        'pharmacy_notes',
        'special_instructions',
        'compliance_checks',
        'audit_trail',
        'requires_signature',
        'signature_required_by',
        'estimated_delivery_date',
        'requested_delivery_date',
        'cancelled_at',
        'cancellation_reason',
        'metadata',
        'source',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'insurance_copay' => 'decimal:2',
        'insurance_coverage' => 'decimal:2',
        'customer_info' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'payment_details' => 'array',
        'insurance_info' => 'array',
        'shipping_details' => 'array',
        'compliance_checks' => 'array',
        'audit_trail' => 'array',
        'metadata' => 'array',
        'payment_processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'prescription_verification_completed_at' => 'datetime',
        'estimated_delivery_date' => 'datetime',
        'requested_delivery_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'signature_required_by' => 'datetime',
        'requires_signature' => 'boolean',
    ];

    /**
     * Order status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Payment status constants
     */
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PROCESSING = 'processing';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * Fulfillment status constants
     */
    public const FULFILLMENT_PENDING = 'pending';
    public const FULFILLMENT_PROCESSING = 'processing';
    public const FULFILLMENT_PARTIALLY_FULFILLED = 'partially_fulfilled';
    public const FULFILLMENT_FULFILLED = 'fulfilled';
    public const FULFILLMENT_CANCELLED = 'cancelled';

    /**
     * Get all order statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
        ];
    }

    /**
     * Get all payment statuses
     */
    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING,
            self::PAYMENT_PROCESSING,
            self::PAYMENT_PAID,
            self::PAYMENT_FAILED,
            self::PAYMENT_REFUNDED,
        ];
    }

    /**
     * Get all fulfillment statuses
     */
    public static function getFulfillmentStatuses(): array
    {
        return [
            self::FULFILLMENT_PENDING,
            self::FULFILLMENT_PROCESSING,
            self::FULFILLMENT_PARTIALLY_FULFILLED,
            self::FULFILLMENT_FULFILLED,
            self::FULFILLMENT_CANCELLED,
        ];
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $year = date('Y');
        $month = date('m');
        
        // Get the last order number for this month
        $lastOrder = static::where('order_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('order_number', 'desc')
            ->first();
        
        if ($lastOrder) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastOrder->order_number, -6);
            $nextNumber = str_pad(intval($lastNumber) + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '000001';
        }
        
        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processingPharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processing_pharmacist_id');
    }

    public function fulfillmentPharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfillment_pharmacist_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scopes
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing(Builder $query): void
    {
        $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeShipped(Builder $query): void
    {
        $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeDelivered(Builder $query): void
    {
        $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopePaymentPending(Builder $query): void
    {
        $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function scopeRequiringPrescriptionVerification(Builder $query): void
    {
        $query->whereHas('items', function ($q) {
            $q->where('requires_prescription', true)
              ->where('prescription_verified', false);
        });
    }

    public function scopeReadyForFulfillment(Builder $query): void
    {
        $query->where('payment_status', self::PAYMENT_PAID)
              ->where('fulfillment_status', self::FULFILLMENT_PENDING)
              ->whereDoesntHave('items', function ($q) {
                  $q->where('requires_prescription', true)
                    ->where('prescription_verified', false);
              });
    }

    /**
     * Computed Attributes
     */
    protected function isPending(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_PENDING
        );
    }

    protected function isProcessing(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_PROCESSING
        );
    }

    protected function isShipped(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_SHIPPED
        );
    }

    protected function isDelivered(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_DELIVERED
        );
    }

    protected function isCancelled(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_CANCELLED
        );
    }

    protected function isRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_REFUNDED
        );
    }

    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payment_status === self::PAYMENT_PAID
        );
    }

    protected function isPaymentPending(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payment_status === self::PAYMENT_PENDING
        );
    }

    protected function isPaymentFailed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->payment_status === self::PAYMENT_FAILED
        );
    }

    protected function isFulfilled(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_FULFILLED
        );
    }

    protected function isPartiallyFulfilled(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_PARTIALLY_FULFILLED
        );
    }

    protected function requiresPrescriptionVerification(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->items()
                    ->where('requires_prescription', true)
                    ->where('prescription_verified', false)
                    ->exists();
            }
        );
    }

    protected function itemCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->sum('quantity_ordered')
        );
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->total_amount, 2)
        );
    }

    protected function customerName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->customer_info['name'] ?? 'Unknown Customer'
        );
    }

    protected function customerEmail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->customer_info['email'] ?? null
        );
    }

    protected function shippingFullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $address = $this->shipping_address;
                if (!$address) return null;
                
                return trim(implode(', ', array_filter([
                    $address['street_address'] ?? null,
                    $address['city'] ?? null,
                    $address['state'] ?? null,
                    $address['postal_code'] ?? null,
                ])));
            }
        );
    }

    /**
     * Status checks
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]) 
            && !$this->is_shipped;
    }

    public function canBeRefunded(): bool
    {
        return $this->is_paid && !$this->is_refunded;
    }

    public function canBeShipped(): bool
    {
        return $this->is_paid 
            && $this->fulfillment_status === self::FULFILLMENT_PROCESSING
            && !$this->requires_prescription_verification;
    }

    public function readyForProcessing(): bool
    {
        return $this->is_paid && !$this->requires_prescription_verification;
    }

    /**
     * Order actions
     */
    public function markAsPaid(array $paymentDetails = []): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_PAID,
            'payment_processed_at' => now(),
            'payment_details' => array_merge($this->payment_details ?? [], $paymentDetails),
            'status' => self::STATUS_PROCESSING,
        ]);

        $this->addToAuditTrail('payment_processed', [
            'payment_method' => $this->payment_method,
            'amount' => $this->total_amount,
            'processed_at' => now()->toISOString(),
        ]);
    }

    public function markAsShipped(string $trackingNumber, string $carrier, ?array $shippingDetails = null): void
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'fulfillment_status' => self::FULFILLMENT_FULFILLED,
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'shipped_at' => now(),
            'shipping_details' => $shippingDetails,
            'estimated_delivery_date' => $this->calculateEstimatedDeliveryDate($carrier),
        ]);

        $this->addToAuditTrail('order_shipped', [
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'shipped_at' => now()->toISOString(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        $this->addToAuditTrail('order_delivered', [
            'delivered_at' => now()->toISOString(),
        ]);
    }

    public function cancel(string $reason, ?int $cancelledBy = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'fulfillment_status' => self::FULFILLMENT_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        $this->addToAuditTrail('order_cancelled', [
            'reason' => $reason,
            'cancelled_by' => $cancelledBy ?? auth()->id(),
            'cancelled_at' => now()->toISOString(),
        ]);
    }

    public function refund(float $amount, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'payment_status' => self::PAYMENT_REFUNDED,
        ]);

        $this->addToAuditTrail('order_refunded', [
            'amount' => $amount,
            'reason' => $reason,
            'refunded_at' => now()->toISOString(),
        ]);
    }

    /**
     * Prescription verification
     */
    public function verifyPrescriptions(?int $pharmacistId = null): bool
    {
        $unverifiedItems = $this->items()
            ->where('requires_prescription', true)
            ->where('prescription_verified', false)
            ->get();

        $allVerified = true;
        
        foreach ($unverifiedItems as $item) {
            if ($item->prescription && $item->prescription->isVerified()) {
                $item->update([
                    'prescription_verified' => true,
                    'prescription_verified_at' => now(),
                    'prescription_verified_by' => $pharmacistId ?? auth()->id(),
                ]);
            } else {
                $allVerified = false;
            }
        }

        if ($allVerified) {
            $this->update([
                'prescription_verification_completed_at' => now(),
                'processing_pharmacist_id' => $pharmacistId ?? auth()->id(),
            ]);

            $this->addToAuditTrail('prescriptions_verified', [
                'verified_by' => $pharmacistId ?? auth()->id(),
                'verified_at' => now()->toISOString(),
            ]);
        }

        return $allVerified;
    }

    /**
     * Inventory management
     */
    public function deductInventory(): bool
    {
        foreach ($this->items as $item) {
            $product = $item->product;
            
            if ($product->quantity_on_hand < $item->quantity_ordered) {
                return false; // Insufficient inventory
            }
            
            $product->decrement('quantity_on_hand', $item->quantity_ordered);
            
            $item->update([
                'quantity_fulfilled' => $item->quantity_ordered,
                'fulfilled_at' => now(),
                'fulfilled_by' => auth()->id(),
                'fulfillment_status' => 'fulfilled',
            ]);
        }

        $this->addToAuditTrail('inventory_deducted', [
            'deducted_by' => auth()->id(),
            'deducted_at' => now()->toISOString(),
        ]);

        return true;
    }

    /**
     * Calculate estimated delivery date
     */
    protected function calculateEstimatedDeliveryDate(string $carrier): \Carbon\Carbon
    {
        // Simple estimation - add business days based on carrier
        $businessDays = match(strtolower($carrier)) {
            'ups', 'fedex' => 3,
            'usps' => 5,
            'dhl' => 2,
            default => 5,
        };

        return now()->addWeekdays($businessDays);
    }

    /**
     * Add entry to audit trail
     */
    public function addToAuditTrail(string $action, array $data = []): void
    {
        $auditTrail = $this->audit_trail ?? [];
        
        $auditTrail[] = [
            'action' => $action,
            'data' => $data,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['audit_trail' => $auditTrail]);
    }

    /**
     * Create order from cart
     */
    public static function createFromCart(Cart $cart, array $orderData): Order
    {
        // Validate cart
        $validationErrors = $cart->validateForCheckout();
        if (!empty($validationErrors)) {
            throw new \Exception('Cart validation failed: ' . implode(', ', $validationErrors));
        }

        // Create order
        $order = static::create(array_merge([
            'order_number' => static::generateOrderNumber(),
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
            $order->items()->create($cartItem->toOrderItemData());
        }

        // Mark cart as converted
        $cart->markAsConverted($order);

        $order->addToAuditTrail('order_created', [
            'cart_id' => $cart->id,
            'item_count' => $cart->items->count(),
            'total_amount' => $order->total_amount,
        ]);

        return $order;
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'order_number',
                'status',
                'payment_status',
                'fulfillment_status',
                'total_amount',
                'tracking_number',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Order {$eventName}: {$this->order_number}");
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->order_number) {
                $model->order_number = static::generateOrderNumber();
            }
        });
    }
}