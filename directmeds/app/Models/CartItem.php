<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'prescription_id',
        'quantity',
        'unit_price',
        'total_price',
        'product_snapshot',
        'prescription_snapshot',
        'special_instructions',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'product_snapshot' => 'array',
        'prescription_snapshot' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Computed Attributes
     */
    protected function requiresPrescription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->requires_prescription
        );
    }

    protected function hasPrescription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prescription_id !== null
        );
    }

    protected function isControlledSubstance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->is_controlled_substance
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->canDispense($this->quantity)
        );
    }

    protected function formattedUnitPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->unit_price, 2)
        );
    }

    protected function formattedTotalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->total_price, 2)
        );
    }

    protected function productDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product->getDisplayName()
        );
    }

    /**
     * Get the product name from snapshot or current product
     */
    public function getProductName(): string
    {
        return $this->product_snapshot['name'] ?? $this->product->name ?? 'Unknown Product';
    }

    /**
     * Get the product brand name from snapshot or current product
     */
    public function getProductBrandName(): ?string
    {
        return $this->product_snapshot['brand_name'] ?? $this->product->brand_name;
    }

    /**
     * Get the product generic name from snapshot or current product
     */
    public function getProductGenericName(): ?string
    {
        return $this->product_snapshot['generic_name'] ?? $this->product->generic_name;
    }

    /**
     * Get the product strength from snapshot or current product
     */
    public function getProductStrength(): ?string
    {
        return $this->product_snapshot['strength'] ?? $this->product->strength;
    }

    /**
     * Get the product dosage form from snapshot or current product
     */
    public function getProductDosageForm(): ?string
    {
        return $this->product_snapshot['dosage_form'] ?? $this->product->dosage_form;
    }

    /**
     * Check if product details have changed since adding to cart
     */
    public function hasProductChanged(): bool
    {
        if (!$this->product_snapshot) {
            return false;
        }

        $currentSnapshot = $this->cart->createProductSnapshot($this->product);
        
        // Compare key fields that affect pricing or availability
        $keyFields = ['retail_price', 'is_available', 'is_discontinued', 'requires_prescription'];
        
        foreach ($keyFields as $field) {
            if (($this->product_snapshot[$field] ?? null) !== ($currentSnapshot[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update quantity
     */
    public function updateQuantity(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $this->update([
            'quantity' => $quantity,
            'total_price' => $quantity * $this->unit_price,
        ]);

        // Update cart totals
        $this->cart->updateTotals();

        return true;
    }

    /**
     * Validate cart item
     */
    public function validate(): array
    {
        $errors = [];

        // Check if product is still available
        if (!$this->product->is_available || $this->product->is_discontinued) {
            $errors[] = "Product '{$this->getProductName()}' is no longer available";
        }

        // Check stock availability
        if (!$this->product->canDispense($this->quantity)) {
            $errors[] = "Product '{$this->getProductName()}' does not have sufficient stock for the requested quantity";
        }

        // Check prescription requirements
        if ($this->requires_prescription && !$this->has_prescription) {
            $errors[] = "Product '{$this->getProductName()}' requires a valid prescription";
        }

        // Check prescription validity if present
        if ($this->has_prescription && $this->prescription) {
            if (!$this->prescription->isVerified()) {
                $errors[] = "Prescription for '{$this->getProductName()}' is not verified";
            }

            if ($this->prescription->isExpired()) {
                $errors[] = "Prescription for '{$this->getProductName()}' has expired";
            }

            if (!$this->prescription->canBeRefilled() && $this->prescription->isDispensed()) {
                $errors[] = "Prescription for '{$this->getProductName()}' cannot be refilled";
            }
        }

        // Check for price changes
        if ($this->hasProductChanged()) {
            $errors[] = "Product '{$this->getProductName()}' details have changed since being added to cart";
        }

        return $errors;
    }

    /**
     * Convert to order item data
     */
    public function toOrderItemData(): array
    {
        return [
            'product_id' => $this->product_id,
            'prescription_id' => $this->prescription_id,
            'product_name' => $this->getProductName(),
            'product_sku' => $this->product->slug ?? null,
            'ndc_number' => $this->product->ndc_number,
            'product_snapshot' => $this->product_snapshot,
            'prescription_snapshot' => $this->prescription_snapshot,
            'requires_prescription' => $this->requires_prescription,
            'prescription_verified' => $this->has_prescription && $this->prescription->isVerified(),
            'quantity_ordered' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'special_instructions' => $this->special_instructions,
            'requires_cold_storage' => $this->product->storage_requirements === 'refrigerated',
            'requires_signature' => $this->is_controlled_substance,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Calculate total price if not set
            if (!$model->total_price) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });

        static::updating(function ($model) {
            // Recalculate total price if quantity or unit price changed
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });

        static::saved(function ($model) {
            // Update cart totals when cart item is saved
            $model->cart->updateTotals();
        });

        static::deleted(function ($model) {
            // Update cart totals when cart item is deleted
            if ($model->cart) {
                $model->cart->updateTotals();
            }
        });
    }
}