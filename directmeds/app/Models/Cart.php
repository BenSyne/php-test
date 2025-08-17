<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'metadata',
        'last_activity_at',
        'abandoned_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
        'abandoned_at' => 'datetime',
    ];

    /**
     * Cart status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_CONVERTED = 'converted';

    /**
     * Get all cart statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_ABANDONED,
            self::STATUS_CONVERTED,
        ];
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAbandoned(Builder $query): void
    {
        $query->where('status', self::STATUS_ABANDONED);
    }

    public function scopeConverted(Builder $query): void
    {
        $query->where('status', self::STATUS_CONVERTED);
    }

    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeForSession(Builder $query, string $sessionId): void
    {
        $query->where('session_id', $sessionId);
    }

    public function scopeAbandonedAfter(Builder $query, int $hours = 24): void
    {
        $query->where('last_activity_at', '<', now()->subHours($hours))
              ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Computed Attributes
     */
    protected function itemCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->sum('quantity')
        );
    }

    protected function uniqueItemCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->count()
        );
    }

    protected function isEmpty(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->count() === 0
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_ACTIVE
        );
    }

    protected function isAbandoned(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_ABANDONED
        );
    }

    protected function isConverted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_CONVERTED
        );
    }

    protected function hasActivePrescriptions(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->whereNotNull('prescription_id')->exists()
        );
    }

    protected function requiresPrescriptionVerification(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->items()
                    ->whereHas('product', function ($query) {
                        $query->where('requires_prescription', true);
                    })
                    ->whereDoesntHave('prescription')
                    ->exists();
            }
        );
    }

    /**
     * Add item to cart
     */
    public function addItem(Product $product, int $quantity, ?Prescription $prescription = null, array $options = []): CartItem
    {
        // Check if item already exists
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('prescription_id', $prescription?->id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
                'total_price' => ($existingItem->quantity + $quantity) * $existingItem->unit_price,
            ]);
            
            $this->updateTotals();
            return $existingItem;
        }

        // Create new cart item
        $cartItem = $this->items()->create([
            'product_id' => $product->id,
            'prescription_id' => $prescription?->id,
            'quantity' => $quantity,
            'unit_price' => $product->retail_price,
            'total_price' => $quantity * $product->retail_price,
            'product_snapshot' => $this->createProductSnapshot($product),
            'prescription_snapshot' => $prescription ? $this->createPrescriptionSnapshot($prescription) : null,
            'special_instructions' => $options['special_instructions'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);

        $this->updateTotals();
        $this->touch('last_activity_at');

        return $cartItem;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity(CartItem $item, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($item);
        }

        $item->update([
            'quantity' => $quantity,
            'total_price' => $quantity * $item->unit_price,
        ]);

        $this->updateTotals();
        $this->touch('last_activity_at');

        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $item): bool
    {
        $result = $item->delete();
        
        if ($result) {
            $this->updateTotals();
            $this->touch('last_activity_at');
        }

        return $result;
    }

    /**
     * Clear all items from cart
     */
    public function clear(): bool
    {
        $result = $this->items()->delete();
        
        if ($result) {
            $this->updateTotals();
            $this->touch('last_activity_at');
        }

        return $result;
    }

    /**
     * Update cart totals
     */
    public function updateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $taxAmount = $this->calculateTax($subtotal);
        $shippingAmount = $this->calculateShipping();
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Calculate tax amount
     */
    protected function calculateTax(float $subtotal): float
    {
        // Implement tax calculation logic based on shipping address
        // For now, return a simple 8.25% tax rate
        return $subtotal * 0.0825;
    }

    /**
     * Calculate shipping amount
     */
    protected function calculateShipping(): float
    {
        // Implement shipping calculation logic
        // For now, return free shipping for orders over $50
        if ($this->subtotal >= 50) {
            return 0.00;
        }
        
        return 9.99;
    }

    /**
     * Validate cart for checkout
     */
    public function validateForCheckout(): array
    {
        $errors = [];

        if ($this->is_empty) {
            $errors[] = 'Cart is empty';
        }

        // Check product availability
        foreach ($this->items as $item) {
            if (!$item->product->canDispense($item->quantity)) {
                $errors[] = "Product '{$item->product->name}' is not available in the requested quantity";
            }
        }

        // Check prescription requirements
        if ($this->requires_prescription_verification) {
            $errors[] = 'Some items require valid prescriptions';
        }

        return $errors;
    }

    /**
     * Mark cart as abandoned
     */
    public function markAsAbandoned(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'abandoned_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'abandonment_reason' => $reason,
                'abandoned_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Mark cart as converted
     */
    public function markAsConverted(Order $order): void
    {
        $this->update([
            'status' => self::STATUS_CONVERTED,
            'metadata' => array_merge($this->metadata ?? [], [
                'converted_to_order_id' => $order->id,
                'converted_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Restore abandoned cart
     */
    public function restore(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'abandoned_at' => null,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Create product snapshot
     */
    protected function createProductSnapshot(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'brand_name' => $product->brand_name,
            'generic_name' => $product->generic_name,
            'ndc_number' => $product->ndc_number,
            'strength' => $product->strength,
            'dosage_form' => $product->dosage_form,
            'requires_prescription' => $product->requires_prescription,
            'is_controlled_substance' => $product->is_controlled_substance,
            'retail_price' => $product->retail_price,
            'insurance_price' => $product->insurance_price,
            'manufacturer' => $product->manufacturer?->name,
            'category' => $product->category?->name,
            'captured_at' => now()->toISOString(),
        ];
    }

    /**
     * Create prescription snapshot
     */
    protected function createPrescriptionSnapshot(Prescription $prescription): array
    {
        return [
            'id' => $prescription->id,
            'prescription_number' => $prescription->prescription_number,
            'medication_name' => $prescription->medication_name,
            'prescriber_name' => $prescription->prescriber_name,
            'verification_status' => $prescription->verification_status,
            'processing_status' => $prescription->processing_status,
            'quantity_prescribed' => $prescription->quantity_prescribed,
            'refills_remaining' => $prescription->refills_remaining,
            'date_written' => $prescription->date_written?->toISOString(),
            'expiration_date' => $prescription->expiration_date?->toISOString(),
            'captured_at' => now()->toISOString(),
        ];
    }

    /**
     * Get or create active cart for user
     */
    public static function getActiveCartForUser(User $user): Cart
    {
        return static::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => self::STATUS_ACTIVE,
            ],
            [
                'last_activity_at' => now(),
            ]
        );
    }

    /**
     * Get or create active cart for session
     */
    public static function getActiveCartForSession(string $sessionId): Cart
    {
        return static::firstOrCreate(
            [
                'session_id' => $sessionId,
                'status' => self::STATUS_ACTIVE,
            ],
            [
                'last_activity_at' => now(),
            ]
        );
    }

    /**
     * Merge guest cart with user cart
     */
    public static function mergeGuestCartWithUserCart(Cart $guestCart, User $user): Cart
    {
        $userCart = self::getActiveCartForUser($user);

        foreach ($guestCart->items as $item) {
            $userCart->addItem(
                $item->product,
                $item->quantity,
                $item->prescription,
                [
                    'special_instructions' => $item->special_instructions,
                    'metadata' => $item->metadata,
                ]
            );
        }

        // Mark guest cart as converted
        $guestCart->markAsConverted($userCart->order ?? new Order());

        return $userCart;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->last_activity_at = now();
        });

        static::updating(function ($model) {
            if ($model->isDirty(['subtotal', 'tax_amount', 'shipping_amount', 'total_amount'])) {
                $model->last_activity_at = now();
            }
        });
    }
}