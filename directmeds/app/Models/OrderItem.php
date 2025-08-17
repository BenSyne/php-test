<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'prescription_id',
        'product_name',
        'product_sku',
        'ndc_number',
        'product_snapshot',
        'prescription_snapshot',
        'requires_prescription',
        'prescription_verified',
        'prescription_verified_at',
        'prescription_verified_by',
        'quantity_ordered',
        'quantity_fulfilled',
        'quantity_shipped',
        'quantity_returned',
        'unit_price',
        'total_price',
        'discount_amount',
        'insurance_copay',
        'insurance_coverage',
        'patient_pay_amount',
        'fulfillment_status',
        'lot_number',
        'expiration_date',
        'manufacturer',
        'ndc_dispensed',
        'fulfilled_at',
        'fulfilled_by',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'special_instructions',
        'pharmacist_notes',
        'compliance_checks',
        'requires_cold_storage',
        'requires_signature',
        'return_reason',
        'returned_at',
        'refund_amount',
        'refunded_at',
        'metadata',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_fulfilled' => 'integer',
        'quantity_shipped' => 'integer',
        'quantity_returned' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'insurance_copay' => 'decimal:2',
        'insurance_coverage' => 'decimal:2',
        'patient_pay_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'product_snapshot' => 'array',
        'prescription_snapshot' => 'array',
        'compliance_checks' => 'array',
        'metadata' => 'array',
        'requires_prescription' => 'boolean',
        'prescription_verified' => 'boolean',
        'requires_cold_storage' => 'boolean',
        'requires_signature' => 'boolean',
        'prescription_verified_at' => 'datetime',
        'expiration_date' => 'date',
        'fulfilled_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Fulfillment status constants
     */
    public const FULFILLMENT_PENDING = 'pending';
    public const FULFILLMENT_PROCESSING = 'processing';
    public const FULFILLMENT_FULFILLED = 'fulfilled';
    public const FULFILLMENT_PARTIALLY_FULFILLED = 'partially_fulfilled';
    public const FULFILLMENT_CANCELLED = 'cancelled';
    public const FULFILLMENT_RETURNED = 'returned';

    /**
     * Get all fulfillment statuses
     */
    public static function getFulfillmentStatuses(): array
    {
        return [
            self::FULFILLMENT_PENDING,
            self::FULFILLMENT_PROCESSING,
            self::FULFILLMENT_FULFILLED,
            self::FULFILLMENT_PARTIALLY_FULFILLED,
            self::FULFILLMENT_CANCELLED,
            self::FULFILLMENT_RETURNED,
        ];
    }

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function prescriptionVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescription_verified_by');
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    /**
     * Computed Attributes
     */
    protected function isPending(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_PENDING
        );
    }

    protected function isProcessing(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_PROCESSING
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

    protected function isCancelled(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_CANCELLED
        );
    }

    protected function isReturned(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fulfillment_status === self::FULFILLMENT_RETURNED
        );
    }

    protected function quantityRemaining(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity_ordered - $this->quantity_fulfilled
        );
    }

    protected function quantityAvailableToShip(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity_fulfilled - $this->quantity_shipped
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

    protected function needsPrescriptionVerification(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requires_prescription && !$this->prescription_verified
        );
    }

    protected function isControlledSubstance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product_snapshot['is_controlled_substance'] ?? false
        );
    }

    /**
     * Status checks
     */
    public function canBeFulfilled(): bool
    {
        return $this->is_pending 
            && (!$this->requires_prescription || $this->prescription_verified)
            && $this->product->canDispense($this->quantity_remaining);
    }

    public function canBeShipped(): bool
    {
        return $this->quantity_available_to_ship > 0;
    }

    public function canBeCancelled(): bool
    {
        return $this->is_pending || $this->is_processing;
    }

    public function canBeReturned(): bool
    {
        return $this->is_fulfilled && $this->quantity_shipped > 0;
    }

    /**
     * Fulfillment actions
     */
    public function fulfill(int $quantity, array $fulfillmentData = []): bool
    {
        if ($quantity > $this->quantity_remaining) {
            return false;
        }

        // Check product availability
        if (!$this->product->canDispense($quantity)) {
            return false;
        }

        // Deduct inventory
        $this->product->decrement('quantity_on_hand', $quantity);

        // Update fulfillment data
        $this->update(array_merge([
            'quantity_fulfilled' => $this->quantity_fulfilled + $quantity,
            'fulfillment_status' => $this->quantity_fulfilled + $quantity >= $this->quantity_ordered 
                ? self::FULFILLMENT_FULFILLED 
                : self::FULFILLMENT_PARTIALLY_FULFILLED,
            'fulfilled_at' => now(),
            'fulfilled_by' => auth()->id(),
        ], $fulfillmentData));

        return true;
    }

    public function ship(int $quantity, ?string $trackingNumber = null): bool
    {
        if ($quantity > $this->quantity_available_to_ship) {
            return false;
        }

        $this->update([
            'quantity_shipped' => $this->quantity_shipped + $quantity,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ]);

        return true;
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'delivered_at' => now(),
        ]);
    }

    public function cancel(string $reason): void
    {
        // Restore inventory if already fulfilled
        if ($this->quantity_fulfilled > 0) {
            $this->product->increment('quantity_on_hand', $this->quantity_fulfilled);
        }

        $this->update([
            'fulfillment_status' => self::FULFILLMENT_CANCELLED,
            'pharmacist_notes' => $reason,
        ]);
    }

    public function returnItem(int $quantity, string $reason): bool
    {
        if ($quantity > $this->quantity_shipped) {
            return false;
        }

        // Restore inventory
        $this->product->increment('quantity_on_hand', $quantity);

        $this->update([
            'quantity_returned' => $this->quantity_returned + $quantity,
            'fulfillment_status' => $this->quantity_returned >= $this->quantity_shipped 
                ? self::FULFILLMENT_RETURNED 
                : self::FULFILLMENT_PARTIALLY_FULFILLED,
            'return_reason' => $reason,
            'returned_at' => now(),
        ]);

        return true;
    }

    public function refund(float $amount, string $reason): void
    {
        $this->update([
            'refund_amount' => $amount,
            'return_reason' => $reason,
            'refunded_at' => now(),
        ]);
    }

    /**
     * Prescription verification
     */
    public function verifyPrescription(?int $pharmacistId = null): bool
    {
        if (!$this->requires_prescription) {
            return true;
        }

        if (!$this->prescription || !$this->prescription->isVerified()) {
            return false;
        }

        $this->update([
            'prescription_verified' => true,
            'prescription_verified_at' => now(),
            'prescription_verified_by' => $pharmacistId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Compliance checks
     */
    public function addComplianceCheck(string $type, array $data): void
    {
        $checks = $this->compliance_checks ?? [];
        
        $checks[] = [
            'type' => $type,
            'data' => $data,
            'checked_by' => auth()->id(),
            'checked_at' => now()->toISOString(),
        ];

        $this->update(['compliance_checks' => $checks]);
    }

    public function hasComplianceCheck(string $type): bool
    {
        $checks = $this->compliance_checks ?? [];
        
        foreach ($checks as $check) {
            if ($check['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get product information from snapshot or current product
     */
    public function getProductName(): string
    {
        return $this->product_name 
            ?? $this->product_snapshot['name'] 
            ?? $this->product->name 
            ?? 'Unknown Product';
    }

    public function getProductBrandName(): ?string
    {
        return $this->product_snapshot['brand_name'] ?? $this->product->brand_name;
    }

    public function getProductGenericName(): ?string
    {
        return $this->product_snapshot['generic_name'] ?? $this->product->generic_name;
    }

    public function getProductStrength(): ?string
    {
        return $this->product_snapshot['strength'] ?? $this->product->strength;
    }

    public function getProductDosageForm(): ?string
    {
        return $this->product_snapshot['dosage_form'] ?? $this->product->dosage_form;
    }

    /**
     * Calculate patient pay amount
     */
    public function calculatePatientPayAmount(): float
    {
        $totalPrice = $this->total_price;
        $insuranceCoverage = $this->insurance_coverage ?? 0;
        $insuranceCopay = $this->insurance_copay ?? 0;

        // Patient pays the copay plus any amount not covered by insurance
        $patientPay = $insuranceCopay + max(0, $totalPrice - $insuranceCoverage - $insuranceCopay);

        return round($patientPay, 2);
    }

    /**
     * Get fulfillment progress percentage
     */
    public function getFulfillmentProgress(): int
    {
        if ($this->quantity_ordered == 0) {
            return 0;
        }

        return (int) round(($this->quantity_fulfilled / $this->quantity_ordered) * 100);
    }

    /**
     * Get shipping progress percentage
     */
    public function getShippingProgress(): int
    {
        if ($this->quantity_fulfilled == 0) {
            return 0;
        }

        return (int) round(($this->quantity_shipped / $this->quantity_fulfilled) * 100);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Calculate patient pay amount if not set
            if (!$model->patient_pay_amount) {
                $model->patient_pay_amount = $model->calculatePatientPayAmount();
            }
        });

        static::updating(function ($model) {
            // Recalculate patient pay amount if insurance info changed
            if ($model->isDirty(['total_price', 'insurance_coverage', 'insurance_copay'])) {
                $model->patient_pay_amount = $model->calculatePatientPayAmount();
            }
        });
    }
}