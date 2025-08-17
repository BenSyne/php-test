<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'uuid',
        'payment_number',
        'user_id',
        'order_id',
        'payment_method_id',
        'type',
        'status',
        'gateway',
        'gateway_transaction_id',
        'gateway_payment_intent_id',
        'gateway_charge_id',
        'amount',
        'amount_authorized',
        'amount_captured',
        'amount_refunded',
        'amount_fee',
        'amount_net',
        'currency',
        'insurance_copay',
        'insurance_coverage',
        'insurance_claim_number',
        'insurance_processed',
        'insurance_processed_at',
        'payment_method_snapshot',
        'payment_method_type',
        'card_last_four',
        'card_brand',
        'flow_type',
        'parent_payment_id',
        'is_partial',
        'installment_number',
        'total_installments',
        'authorized_at',
        'captured_at',
        'expires_at',
        'auto_capture',
        'capture_scheduled_at',
        'pci_compliance_level',
        'requires_3ds',
        '3ds_data',
        'passed_3ds',
        'fraud_check_result',
        'fraud_score',
        'manual_review_required',
        'manual_review_passed',
        'manual_review_at',
        'reviewed_by',
        'failure_code',
        'failure_message',
        'gateway_response',
        'retry_count',
        'last_retry_at',
        'next_retry_at',
        'refund_reason',
        'refund_notes',
        'refunded_by',
        'refunded_at',
        'refund_reference',
        'description',
        'customer_notes',
        'internal_notes',
        'metadata',
        'webhook_data',
        'created_by_ip',
        'user_agent',
        'audit_trail',
        'compliance_logs',
        'processed_at',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_authorized' => 'decimal:2',
        'amount_captured' => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'amount_fee' => 'decimal:2',
        'amount_net' => 'decimal:2',
        'insurance_copay' => 'decimal:2',
        'insurance_coverage' => 'decimal:2',
        'insurance_processed' => 'boolean',
        'insurance_processed_at' => 'datetime',
        'payment_method_snapshot' => 'array',
        'is_partial' => 'boolean',
        'installment_number' => 'integer',
        'total_installments' => 'integer',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_capture' => 'boolean',
        'capture_scheduled_at' => 'datetime',
        'requires_3ds' => 'boolean',
        '3ds_data' => 'array',
        'passed_3ds' => 'boolean',
        'fraud_check_result' => 'array',
        'fraud_score' => 'decimal:2',
        'manual_review_required' => 'boolean',
        'manual_review_passed' => 'boolean',
        'manual_review_at' => 'datetime',
        'retry_count' => 'integer',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
        'webhook_data' => 'array',
        'audit_trail' => 'array',
        'compliance_logs' => 'array',
        'processed_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    protected $hidden = [
        'gateway_response',
        'webhook_data',
        '3ds_data',
    ];

    /**
     * Payment type constants
     */
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REFUND = 'refund';
    public const TYPE_PARTIAL_REFUND = 'partial_refund';
    public const TYPE_CHARGEBACK = 'chargeback';

    /**
     * Payment status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Flow type constants
     */
    public const FLOW_SINGLE = 'single';
    public const FLOW_SPLIT = 'split';
    public const FLOW_RECURRING = 'recurring';

    /**
     * Get all payment types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_PAYMENT,
            self::TYPE_REFUND,
            self::TYPE_PARTIAL_REFUND,
            self::TYPE_CHARGEBACK,
        ];
    }

    /**
     * Get all payment statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
        ];
    }

    /**
     * Generate a unique payment number
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        // Get the last payment number for this month
        $lastPayment = static::where('payment_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('payment_number', 'desc')
            ->first();
        
        if ($lastPayment) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastPayment->payment_number, -6);
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'parent_payment_id');
    }

    public function childPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_payment_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
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

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRefunded(Builder $query): void
    {
        $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeRequiringReview(Builder $query): void
    {
        $query->where('manual_review_required', true)
              ->whereNull('manual_review_passed');
    }

    public function scopeByGateway(Builder $query, string $gateway): void
    {
        $query->where('gateway', $gateway);
    }

    public function scopeByPaymentMethod(Builder $query, int $paymentMethodId): void
    {
        $query->where('payment_method_id', $paymentMethodId);
    }

    public function scopeInDateRange(Builder $query, $startDate, $endDate): void
    {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByAmount(Builder $query, $minAmount, $maxAmount = null): void
    {
        $query->where('amount', '>=', $minAmount);
        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);
        }
    }

    /**
     * Computed attributes
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

    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_COMPLETED
        );
    }

    protected function isFailed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_FAILED
        );
    }

    protected function isRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_REFUNDED
        );
    }

    protected function isPartiallyRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount_refunded > 0 && $this->amount_refunded < $this->amount
        );
    }

    protected function canBeRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_completed && $this->amount_refunded < $this->amount
        );
    }

    protected function canBeCaptured(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_PENDING 
                && $this->amount_authorized > 0 
                && (!$this->expires_at || $this->expires_at->isFuture())
        );
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->amount, 2)
        );
    }

    protected function formattedAmountRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->amount_refunded, 2)
        );
    }

    protected function remainingRefundableAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount - $this->amount_refunded
        );
    }

    protected function formattedRemainingRefundableAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->remaining_refundable_amount, 2)
        );
    }

    protected function statusDisplayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->status) {
                    self::STATUS_PENDING => 'Pending',
                    self::STATUS_PROCESSING => 'Processing',
                    self::STATUS_COMPLETED => 'Completed',
                    self::STATUS_FAILED => 'Failed',
                    self::STATUS_CANCELLED => 'Cancelled',
                    self::STATUS_REFUNDED => 'Refunded',
                    default => 'Unknown',
                };
            }
        );
    }

    protected function paymentMethodDisplayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->card_last_four && $this->card_brand) {
                    return ucfirst($this->card_brand) . " ending in {$this->card_last_four}";
                }
                
                if ($this->paymentMethod) {
                    return $this->paymentMethod->display_name;
                }
                
                return ucfirst($this->payment_method_type ?? 'Unknown');
            }
        );
    }

    /**
     * Payment processing methods
     */
    public function authorize(array $authData = []): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'amount_authorized' => $this->amount,
            'authorized_at' => now(),
            'expires_at' => now()->addDays(7), // Standard auth expiration
        ]);

        $this->addToAuditTrail('payment_authorized', $authData);
    }

    public function capture(?float $amount = null): void
    {
        $captureAmount = $amount ?? $this->amount_authorized ?? $this->amount;
        
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'amount_captured' => $captureAmount,
            'captured_at' => now(),
            'processed_at' => now(),
        ]);

        $this->addToAuditTrail('payment_captured', [
            'amount_captured' => $captureAmount,
            'captured_at' => now()->toISOString(),
        ]);

        // Mark payment method as used
        if ($this->paymentMethod) {
            $this->paymentMethod->markAsUsed($this->created_by_ip);
        }

        // Update order payment status
        if ($this->order) {
            $this->order->markAsPaid([
                'payment_id' => $this->id,
                'payment_number' => $this->payment_number,
                'amount_paid' => $captureAmount,
            ]);
        }
    }

    public function fail(string $failureCode, string $failureMessage, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);

        $this->addToAuditTrail('payment_failed', [
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'failed_at' => now()->toISOString(),
        ]);

        // Schedule retry if applicable
        $this->scheduleRetry();
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'internal_notes' => $reason,
        ]);

        $this->addToAuditTrail('payment_cancelled', [
            'reason' => $reason,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now()->toISOString(),
        ]);
    }

    public function refund(float $amount, string $reason, ?int $refundedBy = null): Payment
    {
        if ($amount > $this->remaining_refundable_amount) {
            throw new \InvalidArgumentException('Refund amount exceeds remaining refundable amount');
        }

        // Create refund payment record
        $refund = static::create([
            'payment_number' => static::generatePaymentNumber(),
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'payment_method_id' => $this->payment_method_id,
            'type' => $amount >= $this->remaining_refundable_amount ? self::TYPE_REFUND : self::TYPE_PARTIAL_REFUND,
            'status' => self::STATUS_COMPLETED,
            'gateway' => $this->gateway,
            'amount' => -$amount, // Negative amount for refunds
            'amount_captured' => -$amount,
            'currency' => $this->currency,
            'parent_payment_id' => $this->id,
            'payment_method_type' => $this->payment_method_type,
            'card_last_four' => $this->card_last_four,
            'card_brand' => $this->card_brand,
            'refund_reason' => $reason,
            'refunded_by' => $refundedBy ?? auth()->id(),
            'refunded_at' => now(),
            'processed_at' => now(),
            'description' => "Refund for payment {$this->payment_number}",
        ]);

        // Update original payment
        $this->increment('amount_refunded', $amount);
        
        if ($this->amount_refunded >= $this->amount) {
            $this->update(['status' => self::STATUS_REFUNDED]);
        }

        $this->addToAuditTrail('payment_refunded', [
            'refund_amount' => $amount,
            'refund_reason' => $reason,
            'refunded_by' => $refundedBy ?? auth()->id(),
            'refund_payment_id' => $refund->id,
            'refunded_at' => now()->toISOString(),
        ]);

        return $refund;
    }

    /**
     * Fraud and compliance methods
     */
    public function requireManualReview(array $reason = []): void
    {
        $this->update([
            'manual_review_required' => true,
            'fraud_check_result' => array_merge($this->fraud_check_result ?? [], $reason),
        ]);

        $this->addToAuditTrail('manual_review_required', $reason);
    }

    public function passManualReview(?int $reviewedBy = null): void
    {
        $this->update([
            'manual_review_passed' => true,
            'manual_review_at' => now(),
            'reviewed_by' => $reviewedBy ?? auth()->id(),
        ]);

        $this->addToAuditTrail('manual_review_passed', [
            'reviewed_by' => $reviewedBy ?? auth()->id(),
            'reviewed_at' => now()->toISOString(),
        ]);
    }

    public function failManualReview(?int $reviewedBy = null, string $reason = null): void
    {
        $this->update([
            'manual_review_passed' => false,
            'manual_review_at' => now(),
            'reviewed_by' => $reviewedBy ?? auth()->id(),
            'status' => self::STATUS_FAILED,
            'failure_message' => $reason ?? 'Failed manual review',
        ]);

        $this->addToAuditTrail('manual_review_failed', [
            'reviewed_by' => $reviewedBy ?? auth()->id(),
            'reviewed_at' => now()->toISOString(),
            'reason' => $reason,
        ]);
    }

    public function performComplianceChecks(): array
    {
        $checks = [];
        
        // PCI compliance
        $checks['pci_compliant'] = $this->validatePciCompliance();
        
        // AML checks
        $checks['aml_clear'] = $this->performAmlChecks();
        
        // Insurance compliance
        if ($this->insurance_copay || $this->insurance_coverage) {
            $checks['insurance_compliant'] = $this->validateInsuranceCompliance();
        }
        
        // Fraud checks
        $checks['fraud_score'] = $this->fraud_score ?? 0;
        $checks['fraud_clear'] = ($this->fraud_score ?? 0) < 0.3;
        
        $this->compliance_logs = array_merge($this->compliance_logs ?? [], [
            'compliance_check' => [
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
                'overall_compliant' => !in_array(false, array_filter($checks, 'is_bool')),
            ]
        ]);
        
        $this->save();
        
        return $checks;
    }

    /**
     * Retry logic
     */
    public function scheduleRetry(): void
    {
        if ($this->retry_count >= 3) {
            return; // Max retries reached
        }

        $retryDelay = match($this->retry_count) {
            0 => 15, // 15 minutes
            1 => 60, // 1 hour
            2 => 240, // 4 hours
            default => 1440, // 24 hours
        };

        $this->update([
            'next_retry_at' => now()->addMinutes($retryDelay),
        ]);
    }

    public function attemptRetry(): bool
    {
        if (!$this->next_retry_at || $this->next_retry_at->isFuture()) {
            return false;
        }

        $this->increment('retry_count');
        $this->update([
            'last_retry_at' => now(),
            'next_retry_at' => null,
            'status' => self::STATUS_PROCESSING,
        ]);

        $this->addToAuditTrail('payment_retry_attempted', [
            'retry_count' => $this->retry_count,
            'attempted_at' => now()->toISOString(),
        ]);

        return true;
    }

    /**
     * Audit trail methods
     */
    public function addToAuditTrail(string $action, array $data = []): void
    {
        $auditTrail = $this->audit_trail ?? [];
        
        $auditTrail[] = [
            'action' => $action,
            'data' => $data,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['audit_trail' => $auditTrail]);
    }

    /**
     * Helper methods for compliance checks
     */
    protected function validatePciCompliance(): bool
    {
        // Check if payment method is PCI compliant
        if ($this->paymentMethod) {
            $pciChecks = $this->paymentMethod->validatePciCompliance();
            return $pciChecks['overall_compliant'] ?? false;
        }
        
        return true; // If no payment method, assume compliant
    }

    protected function performAmlChecks(): bool
    {
        // Anti-Money Laundering checks
        $flags = [];
        
        // Check transaction amount thresholds
        if ($this->amount > 10000) {
            $flags[] = 'high_amount';
        }
        
        // Check customer patterns
        $recentPayments = static::where('user_id', $this->user_id)
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('amount');
        
        if ($recentPayments > 50000) {
            $flags[] = 'high_volume_customer';
        }
        
        return empty($flags);
    }

    protected function validateInsuranceCompliance(): bool
    {
        // Insurance compliance checks
        if (!$this->insurance_processed && ($this->insurance_copay || $this->insurance_coverage)) {
            return false;
        }
        
        return true;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }
            
            if (!$model->payment_number) {
                $model->payment_number = static::generatePaymentNumber();
            }
            
            $model->created_by_ip = request()->ip();
            $model->user_agent = request()->userAgent();
            
            // Calculate net amount
            if ($model->amount && $model->amount_fee) {
                $model->amount_net = $model->amount - $model->amount_fee;
            }
        });
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'payment_number',
                'type',
                'status',
                'amount',
                'amount_refunded',
                'gateway',
                'payment_method_type',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment {$eventName}: {$this->payment_number}");
    }
}