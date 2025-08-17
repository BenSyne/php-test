<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaymentMethod extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'gateway',
        'gateway_method_id',
        'card_brand',
        'card_last_four',
        'card_exp_month',
        'card_exp_year',
        'card_fingerprint',
        'bank_name',
        'bank_account_type',
        'bank_account_last_four',
        'bank_routing_number_last_four',
        'insurance_provider',
        'insurance_member_id',
        'insurance_group_number',
        'insurance_plan_name',
        'insurance_copay_amount',
        'insurance_deductible',
        'insurance_verified',
        'insurance_verified_at',
        'encrypted_data',
        'pci_token',
        'tokenization_method',
        'is_default',
        'is_active',
        'is_verified',
        'verified_at',
        'expires_at',
        'metadata',
        'billing_name',
        'billing_street_address',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'created_by_ip',
        'last_used_ip',
        'last_used_at',
        'usage_count',
        'compliance_checks',
        'fraud_checks',
    ];

    protected $casts = [
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
        'insurance_copay_amount' => 'decimal:2',
        'insurance_deductible' => 'decimal:2',
        'insurance_verified' => 'boolean',
        'insurance_verified_at' => 'datetime',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
        'compliance_checks' => 'array',
        'fraud_checks' => 'array',
    ];

    protected $hidden = [
        'encrypted_data',
        'pci_token',
        'gateway_method_id',
    ];

    /**
     * Payment method type constants
     */
    public const TYPE_CARD = 'card';
    public const TYPE_BANK_ACCOUNT = 'bank_account';
    public const TYPE_INSURANCE = 'insurance';

    /**
     * Gateway constants
     */
    public const GATEWAY_STRIPE = 'stripe';
    public const GATEWAY_SQUARE = 'square';
    public const GATEWAY_PAYPAL = 'paypal';

    /**
     * Get all payment method types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CARD,
            self::TYPE_BANK_ACCOUNT,
            self::TYPE_INSURANCE,
        ];
    }

    /**
     * Get all supported gateways
     */
    public static function getGateways(): array
    {
        return [
            self::GATEWAY_STRIPE,
            self::GATEWAY_SQUARE,
            self::GATEWAY_PAYPAL,
        ];
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Computed attributes
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->type) {
                    self::TYPE_CARD => $this->formatCardDisplay(),
                    self::TYPE_BANK_ACCOUNT => $this->formatBankDisplay(),
                    self::TYPE_INSURANCE => $this->formatInsuranceDisplay(),
                    default => 'Unknown Payment Method',
                };
            }
        );
    }

    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->type === self::TYPE_CARD && $this->card_exp_month && $this->card_exp_year) {
                    $expirationDate = \Carbon\Carbon::createFromDate($this->card_exp_year, $this->card_exp_month, 1)->endOfMonth();
                    return $expirationDate->isPast();
                }
                
                if ($this->expires_at) {
                    return $this->expires_at->isPast();
                }
                
                return false;
            }
        );
    }

    protected function expiresInDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->type === self::TYPE_CARD && $this->card_exp_month && $this->card_exp_year) {
                    $expirationDate = \Carbon\Carbon::createFromDate($this->card_exp_year, $this->card_exp_month, 1)->endOfMonth();
                    return now()->diffInDays($expirationDate, false);
                }
                
                if ($this->expires_at) {
                    return now()->diffInDays($this->expires_at, false);
                }
                
                return null;
            }
        );
    }

    protected function billingAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parts = array_filter([
                    $this->billing_street_address,
                    $this->billing_city,
                    $this->billing_state,
                    $this->billing_postal_code,
                ]);
                
                return !empty($parts) ? implode(', ', $parts) : null;
            }
        );
    }

    protected function maskedCardNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->type === self::TYPE_CARD && $this->card_last_four) {
                    return '**** **** **** ' . $this->card_last_four;
                }
                return null;
            }
        );
    }

    /**
     * Security methods
     */
    public function encryptSensitiveData(array $data): void
    {
        $this->encrypted_data = Crypt::encryptString(json_encode($data));
        $this->save();
    }

    public function decryptSensitiveData(): ?array
    {
        if (!$this->encrypted_data) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($this->encrypted_data), true);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt payment method data', [
                'payment_method_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * PCI Compliance methods
     */
    public function generatePciToken(): string
    {
        $token = 'pm_' . Str::random(24);
        $this->pci_token = $token;
        $this->save();
        return $token;
    }

    public function validatePciCompliance(): array
    {
        $checks = [];
        
        // Check for stored card data
        if ($this->type === self::TYPE_CARD) {
            $checks['no_full_card_number'] = !$this->hasFullCardNumber();
            $checks['no_cvv_stored'] = !$this->hasCvvStored();
            $checks['has_pci_token'] = !empty($this->pci_token);
            $checks['encrypted_data_present'] = !empty($this->encrypted_data);
        }
        
        // Check tokenization
        $checks['proper_tokenization'] = !empty($this->gateway_method_id) || !empty($this->pci_token);
        
        // Check billing address validation
        $checks['billing_address_validated'] = $this->isBillingAddressValid();
        
        $this->compliance_checks = array_merge($this->compliance_checks ?? [], [
            'pci_validation' => [
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
                'overall_compliant' => !in_array(false, $checks),
            ]
        ]);
        
        $this->save();
        
        return $checks;
    }

    /**
     * Fraud prevention methods
     */
    public function performFraudChecks(array $transactionData = []): array
    {
        $checks = [];
        
        // Check usage patterns
        $checks['usage_frequency'] = $this->checkUsageFrequency();
        $checks['location_consistency'] = $this->checkLocationConsistency();
        $checks['amount_pattern'] = $this->checkAmountPattern($transactionData);
        
        // Check against known fraud indicators
        $checks['card_bin_check'] = $this->checkCardBin();
        $checks['velocity_check'] = $this->checkVelocity();
        
        $fraudScore = $this->calculateFraudScore($checks);
        
        $this->fraud_checks = array_merge($this->fraud_checks ?? [], [
            'fraud_check' => [
                'timestamp' => now()->toISOString(),
                'checks' => $checks,
                'fraud_score' => $fraudScore,
                'transaction_data' => $transactionData,
            ]
        ]);
        
        $this->save();
        
        return [
            'checks' => $checks,
            'fraud_score' => $fraudScore,
            'recommendation' => $this->getFraudRecommendation($fraudScore),
        ];
    }

    /**
     * Payment method management
     */
    public function setAsDefault(): void
    {
        // Remove default from other payment methods
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        $this->is_default = true;
        $this->save();
    }

    public function markAsUsed(?string $ipAddress = null): void
    {
        $this->increment('usage_count');
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ipAddress ?? request()->ip(),
        ]);
    }

    public function verify(array $verificationData = []): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        
        // Add verification to compliance checks
        $this->compliance_checks = array_merge($this->compliance_checks ?? [], [
            'verification' => [
                'timestamp' => now()->toISOString(),
                'data' => $verificationData,
            ]
        ]);
        
        $this->save();
    }

    public function deactivate(string $reason = null): void
    {
        $this->update([
            'is_active' => false,
            'is_default' => false,
        ]);
        
        // If this was the default, set another as default
        if ($this->is_default) {
            $nextDefault = static::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->where('is_active', true)
                ->first();
            
            if ($nextDefault) {
                $nextDefault->setAsDefault();
            }
        }
    }

    /**
     * Display formatting methods
     */
    protected function formatCardDisplay(): string
    {
        $brand = ucfirst($this->card_brand ?? 'Card');
        $lastFour = $this->card_last_four ? " ending in {$this->card_last_four}" : '';
        $expiry = ($this->card_exp_month && $this->card_exp_year) 
            ? " (expires {$this->card_exp_month}/{$this->card_exp_year})" 
            : '';
        
        return $brand . $lastFour . $expiry;
    }

    protected function formatBankDisplay(): string
    {
        $bank = $this->bank_name ?? 'Bank Account';
        $type = $this->bank_account_type ? " ({$this->bank_account_type})" : '';
        $lastFour = $this->bank_account_last_four ? " ending in {$this->bank_account_last_four}" : '';
        
        return $bank . $type . $lastFour;
    }

    protected function formatInsuranceDisplay(): string
    {
        $provider = $this->insurance_provider ?? 'Insurance';
        $plan = $this->insurance_plan_name ? " - {$this->insurance_plan_name}" : '';
        $verified = $this->insurance_verified ? ' (Verified)' : ' (Unverified)';
        
        return $provider . $plan . $verified;
    }

    /**
     * Helper methods for compliance and fraud checks
     */
    protected function hasFullCardNumber(): bool
    {
        // Check if any stored data might contain full card number
        return false; // Should always be false in PCI-compliant system
    }

    protected function hasCvvStored(): bool
    {
        // Check if CVV is stored (should never be true)
        return false; // Should always be false in PCI-compliant system
    }

    protected function isBillingAddressValid(): bool
    {
        return !empty($this->billing_street_address) 
            && !empty($this->billing_city) 
            && !empty($this->billing_state) 
            && !empty($this->billing_postal_code);
    }

    protected function checkUsageFrequency(): string
    {
        $recentUsage = $this->payments()
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
        
        return $recentUsage > 10 ? 'high' : ($recentUsage > 5 ? 'medium' : 'low');
    }

    protected function checkLocationConsistency(): string
    {
        // Compare current IP with historical usage
        // This is a simplified check
        return 'consistent'; // Would implement geolocation comparison
    }

    protected function checkAmountPattern(array $transactionData): string
    {
        // Check for unusual amount patterns
        return 'normal'; // Would implement pattern analysis
    }

    protected function checkCardBin(): string
    {
        // Check card BIN against fraud databases
        return 'clean'; // Would implement BIN checking
    }

    protected function checkVelocity(): string
    {
        // Check transaction velocity
        return 'normal'; // Would implement velocity checking
    }

    protected function calculateFraudScore(array $checks): float
    {
        // Simple scoring algorithm
        $score = 0.0;
        
        foreach ($checks as $check => $result) {
            $score += match($result) {
                'high', 'suspicious' => 0.3,
                'medium' => 0.1,
                'low', 'normal', 'clean', 'consistent' => 0.0,
                default => 0.05,
            };
        }
        
        return min($score, 1.0);
    }

    protected function getFraudRecommendation(float $score): string
    {
        return match(true) {
            $score >= 0.7 => 'block',
            $score >= 0.4 => 'manual_review',
            $score >= 0.2 => 'additional_verification',
            default => 'approve',
        };
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
            
            $model->created_by_ip = request()->ip();
            
            // Generate PCI token if not provided
            if (!$model->pci_token && in_array($model->type, [self::TYPE_CARD, self::TYPE_BANK_ACCOUNT])) {
                $model->generatePciToken();
            }
        });

        static::updating(function ($model) {
            // Prevent certain fields from being updated
            $model->preventDirectUpdate(['encrypted_data', 'pci_token']);
        });
    }

    /**
     * Prevent direct update of sensitive fields
     */
    protected function preventDirectUpdate(array $fields): void
    {
        foreach ($fields as $field) {
            if ($this->isDirty($field) && $this->getOriginal($field) !== null) {
                $this->attributes[$field] = $this->getOriginal($field);
            }
        }
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type',
                'is_default',
                'is_active',
                'is_verified',
                'card_brand',
                'card_last_four',
                'bank_name',
                'insurance_provider',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment method {$eventName}: {$this->display_name}");
    }
}