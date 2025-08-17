<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Prescription extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prescription_number',
        'rx_number',
        'original_prescription_id',
        'patient_id',
        'patient_name',
        'patient_dob',
        'patient_address',
        'prescriber_id',
        'prescriber_name',
        'prescriber_npi',
        'prescriber_dea',
        'product_id',
        'medication_name',
        'generic_name',
        'ndc_number',
        'strength',
        'dosage_form',
        'route_of_administration',
        'quantity_prescribed',
        'quantity_unit',
        'days_supply',
        'directions_for_use',
        'indication',
        'refills_authorized',
        'refills_remaining',
        'refills_used',
        'is_refill',
        'controlled_substance_schedule',
        'is_controlled_substance',
        'dea_form_number',
        'date_written',
        'date_received',
        'date_filled',
        'date_dispensed',
        'expiration_date',
        'discard_after_date',
        'uploaded_files',
        'upload_method',
        'upload_notes',
        'verification_status',
        'processing_status',
        'reviewing_pharmacist_id',
        'review_started_at',
        'review_completed_at',
        'pharmacist_notes',
        'drug_interaction_checks',
        'allergy_checks',
        'clinical_reviews',
        'dispensing_pharmacist_id',
        'quantity_dispensed',
        'lot_number',
        'expiration_date_dispensed',
        'manufacturer_dispensed',
        'ndc_dispensed',
        'insurance_information',
        'copay_amount',
        'total_cost',
        'insurance_paid',
        'patient_paid',
        'insurance_claim_number',
        'transferred_from_pharmacy',
        'transferred_to_pharmacy',
        'transfer_date',
        'transfer_reason',
        'requires_consultation',
        'consultation_completed',
        'consultation_completed_at',
        'consultation_pharmacist_id',
        'compliance_checks',
        'legal_notes',
        'is_active',
        'priority_level',
        'flags',
        'alerts',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'patient_dob' => 'date',
        'quantity_prescribed' => 'decimal:3',
        'days_supply' => 'decimal:1',
        'refills_authorized' => 'integer',
        'refills_remaining' => 'integer',
        'refills_used' => 'integer',
        'is_refill' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'date_written' => 'date',
        'date_received' => 'date',
        'date_filled' => 'date',
        'date_dispensed' => 'date',
        'expiration_date' => 'date',
        'discard_after_date' => 'date',
        'uploaded_files' => 'array',
        'review_started_at' => 'datetime',
        'review_completed_at' => 'datetime',
        'drug_interaction_checks' => 'array',
        'allergy_checks' => 'array',
        'clinical_reviews' => 'array',
        'quantity_dispensed' => 'decimal:3',
        'expiration_date_dispensed' => 'date',
        'insurance_information' => 'array',
        'copay_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'insurance_paid' => 'decimal:2',
        'patient_paid' => 'decimal:2',
        'transfer_date' => 'date',
        'requires_consultation' => 'boolean',
        'consultation_completed' => 'boolean',
        'consultation_completed_at' => 'datetime',
        'compliance_checks' => 'array',
        'is_active' => 'boolean',
        'priority_level' => 'integer',
        'flags' => 'array',
        'alerts' => 'array',
    ];

    /**
     * Verification status constants
     */
    public const VERIFICATION_PENDING = 'pending';
    public const VERIFICATION_IN_REVIEW = 'in_review';
    public const VERIFICATION_VERIFIED = 'verified';
    public const VERIFICATION_REJECTED = 'rejected';
    public const VERIFICATION_ON_HOLD = 'on_hold';
    public const VERIFICATION_EXPIRED = 'expired';
    public const VERIFICATION_CANCELLED = 'cancelled';

    /**
     * Processing status constants
     */
    public const PROCESSING_RECEIVED = 'received';
    public const PROCESSING_IN_QUEUE = 'in_queue';
    public const PROCESSING_FILLING = 'filling';
    public const PROCESSING_READY = 'ready';
    public const PROCESSING_DISPENSED = 'dispensed';
    public const PROCESSING_RETURNED = 'returned';
    public const PROCESSING_TRANSFERRED = 'transferred';

    /**
     * Controlled substance schedule constants
     */
    public const SCHEDULE_I = 'I';
    public const SCHEDULE_II = 'II';
    public const SCHEDULE_III = 'III';
    public const SCHEDULE_IV = 'IV';
    public const SCHEDULE_V = 'V';
    public const SCHEDULE_N = 'N'; // Not controlled

    /**
     * Priority level constants
     */
    public const PRIORITY_URGENT = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_NORMAL = 3;
    public const PRIORITY_LOW = 4;
    public const PRIORITY_ROUTINE = 5;

    /**
     * Get all verification statuses
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::VERIFICATION_PENDING,
            self::VERIFICATION_IN_REVIEW,
            self::VERIFICATION_VERIFIED,
            self::VERIFICATION_REJECTED,
            self::VERIFICATION_ON_HOLD,
            self::VERIFICATION_EXPIRED,
            self::VERIFICATION_CANCELLED,
        ];
    }

    /**
     * Get all processing statuses
     */
    public static function getProcessingStatuses(): array
    {
        return [
            self::PROCESSING_RECEIVED,
            self::PROCESSING_IN_QUEUE,
            self::PROCESSING_FILLING,
            self::PROCESSING_READY,
            self::PROCESSING_DISPENSED,
            self::PROCESSING_RETURNED,
            self::PROCESSING_TRANSFERRED,
        ];
    }

    /**
     * Get all controlled substance schedules
     */
    public static function getControlledSubstanceSchedules(): array
    {
        return [
            self::SCHEDULE_I,
            self::SCHEDULE_II,
            self::SCHEDULE_III,
            self::SCHEDULE_IV,
            self::SCHEDULE_V,
            self::SCHEDULE_N,
        ];
    }

    /**
     * Get all priority levels
     */
    public static function getPriorityLevels(): array
    {
        return [
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_ROUTINE => 'Routine',
        ];
    }

    /**
     * Generate a unique prescription number.
     */
    public static function generatePrescriptionNumber(): string
    {
        $prefix = 'RX';
        $year = date('Y');
        $month = date('m');
        
        // Get the last prescription number for this month
        $lastPrescription = static::where('prescription_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('prescription_number', 'desc')
            ->first();
        
        if ($lastPrescription) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastPrescription->prescription_number, -6);
            $nextNumber = str_pad(intval($lastNumber) + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '000001';
        }
        
        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }

    /**
     * Check if prescription is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }

    /**
     * Check if prescription is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === self::VERIFICATION_PENDING;
    }

    /**
     * Check if prescription is in review.
     */
    public function isInReview(): bool
    {
        return $this->verification_status === self::VERIFICATION_IN_REVIEW;
    }

    /**
     * Check if prescription is rejected.
     */
    public function isRejected(): bool
    {
        return $this->verification_status === self::VERIFICATION_REJECTED;
    }

    /**
     * Check if prescription is on hold.
     */
    public function isOnHold(): bool
    {
        return $this->verification_status === self::VERIFICATION_ON_HOLD;
    }

    /**
     * Check if prescription has expired.
     */
    public function isExpired(): bool
    {
        return $this->verification_status === self::VERIFICATION_EXPIRED ||
               ($this->expiration_date && $this->expiration_date->isPast());
    }

    /**
     * Check if prescription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->verification_status === self::VERIFICATION_CANCELLED;
    }

    /**
     * Check if prescription is a controlled substance.
     */
    public function isControlledSubstance(): bool
    {
        return $this->is_controlled_substance || 
               $this->controlled_substance_schedule !== self::SCHEDULE_N;
    }

    /**
     * Check if prescription requires DEA authorization.
     */
    public function requiresDeaAuthorization(): bool
    {
        return $this->isControlledSubstance() && 
               in_array($this->controlled_substance_schedule, [
                   self::SCHEDULE_I, 
                   self::SCHEDULE_II, 
                   self::SCHEDULE_III, 
                   self::SCHEDULE_IV, 
                   self::SCHEDULE_V
               ]);
    }

    /**
     * Check if prescription is a refill.
     */
    public function isRefill(): bool
    {
        return $this->is_refill || $this->original_prescription_id !== null;
    }

    /**
     * Check if prescription has refills remaining.
     */
    public function hasRefillsRemaining(): bool
    {
        return $this->refills_remaining > 0;
    }

    /**
     * Check if prescription can be refilled.
     */
    public function canBeRefilled(): bool
    {
        return $this->hasRefillsRemaining() && 
               !$this->isExpired() && 
               $this->isVerified() &&
               $this->processing_status === self::PROCESSING_DISPENSED;
    }

    /**
     * Check if prescription is ready for dispensing.
     */
    public function isReadyForDispensing(): bool
    {
        return $this->isVerified() && 
               $this->processing_status === self::PROCESSING_READY;
    }

    /**
     * Check if prescription has been dispensed.
     */
    public function isDispensed(): bool
    {
        return $this->processing_status === self::PROCESSING_DISPENSED;
    }

    /**
     * Check if prescription requires consultation.
     */
    public function requiresConsultation(): bool
    {
        return $this->requires_consultation && !$this->consultation_completed;
    }

    /**
     * Get days until expiration.
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Get priority level name.
     */
    public function getPriorityLevelNameAttribute(): string
    {
        $priorities = self::getPriorityLevels();
        return $priorities[$this->priority_level] ?? 'Unknown';
    }

    /**
     * Calculate prescription expiration date based on date written.
     */
    public function calculateExpirationDate(): Carbon
    {
        $dateWritten = $this->date_written;
        
        if ($this->isControlledSubstance()) {
            // Controlled substances expire in 6 months
            return $dateWritten->copy()->addMonths(6);
        }
        
        // Non-controlled substances expire in 1 year
        return $dateWritten->copy()->addYear();
    }

    /**
     * Start pharmacist review.
     */
    public function startReview(?int $pharmacistId = null): void
    {
        $this->update([
            'verification_status' => self::VERIFICATION_IN_REVIEW,
            'reviewing_pharmacist_id' => $pharmacistId ?? auth()->id(),
            'review_started_at' => now(),
        ]);
    }

    /**
     * Complete pharmacist review with verification.
     */
    public function completeReviewAsVerified(?string $notes = null): void
    {
        $this->update([
            'verification_status' => self::VERIFICATION_VERIFIED,
            'processing_status' => self::PROCESSING_IN_QUEUE,
            'review_completed_at' => now(),
            'pharmacist_notes' => $notes,
        ]);
    }

    /**
     * Complete pharmacist review with rejection.
     */
    public function completeReviewAsRejected(string $reason): void
    {
        $this->update([
            'verification_status' => self::VERIFICATION_REJECTED,
            'review_completed_at' => now(),
            'pharmacist_notes' => $reason,
        ]);
    }

    /**
     * Put prescription on hold.
     */
    public function putOnHold(string $reason): void
    {
        $this->update([
            'verification_status' => self::VERIFICATION_ON_HOLD,
            'pharmacist_notes' => $reason,
        ]);
    }

    /**
     * Use a refill.
     */
    public function useRefill(): void
    {
        if (!$this->hasRefillsRemaining()) {
            throw new \Exception('No refills remaining');
        }
        
        $this->increment('refills_used');
        $this->decrement('refills_remaining');
    }

    /**
     * Mark as dispensed.
     */
    public function markAsDispensed(array $dispensingData = []): void
    {
        $data = array_merge([
            'processing_status' => self::PROCESSING_DISPENSED,
            'date_dispensed' => now(),
            'dispensing_pharmacist_id' => auth()->id(),
        ], $dispensingData);
        
        $this->update($data);
        
        // If this is a refill, use up a refill
        if ($this->isRefill()) {
            $this->useRefill();
        }
    }

    /**
     * Add flag to prescription.
     */
    public function addFlag(string $flag, ?string $description = null): void
    {
        $flags = $this->flags ?? [];
        $flags[] = [
            'flag' => $flag,
            'description' => $description,
            'added_at' => now()->toISOString(),
            'added_by' => auth()->id(),
        ];
        
        $this->update(['flags' => $flags]);
    }

    /**
     * Remove flag from prescription.
     */
    public function removeFlag(string $flag): void
    {
        $flags = $this->flags ?? [];
        $flags = array_filter($flags, function ($item) use ($flag) {
            return $item['flag'] !== $flag;
        });
        
        $this->update(['flags' => array_values($flags)]);
    }

    /**
     * Check if prescription has flag.
     */
    public function hasFlag(string $flag): bool
    {
        $flags = $this->flags ?? [];
        
        foreach ($flags as $item) {
            if ($item['flag'] === $flag) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add alert to prescription.
     */
    public function addAlert(string $alert, string $level = 'info'): void
    {
        $alerts = $this->alerts ?? [];
        $alerts[] = [
            'alert' => $alert,
            'level' => $level,
            'added_at' => now()->toISOString(),
            'added_by' => auth()->id(),
        ];
        
        $this->update(['alerts' => $alerts]);
    }

    /**
     * Clear all alerts.
     */
    public function clearAlerts(): void
    {
        $this->update(['alerts' => []]);
    }

    /**
     * Get the patient for this prescription.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the prescriber for this prescription.
     */
    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(Prescriber::class);
    }

    /**
     * Get the product for this prescription.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the reviewing pharmacist.
     */
    public function reviewingPharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewing_pharmacist_id');
    }

    /**
     * Get the dispensing pharmacist.
     */
    public function dispensingPharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensing_pharmacist_id');
    }

    /**
     * Get the consultation pharmacist.
     */
    public function consultationPharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultation_pharmacist_id');
    }

    /**
     * Get the original prescription (for refills).
     */
    public function originalPrescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'original_prescription_id');
    }

    /**
     * Get refills of this prescription.
     */
    public function refills(): HasMany
    {
        return $this->hasMany(Prescription::class, 'original_prescription_id');
    }

    /**
     * Get cart items associated with this prescription.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get order items associated with this prescription.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get audit logs for this prescription.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(PrescriptionAuditLog::class);
    }

    /**
     * Get the user who created this prescription.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this prescription.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this prescription.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope to get only verified prescriptions.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::VERIFICATION_VERIFIED);
    }

    /**
     * Scope to get only active prescriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only controlled substances.
     */
    public function scopeControlledSubstances($query)
    {
        return $query->where('is_controlled_substance', true);
    }

    /**
     * Scope to get prescriptions by priority.
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority_level', $priority);
    }

    /**
     * Scope to get prescriptions requiring review.
     */
    public function scopeRequiringReview($query)
    {
        return $query->whereIn('verification_status', [
            self::VERIFICATION_PENDING,
            self::VERIFICATION_IN_REVIEW,
            self::VERIFICATION_ON_HOLD,
        ]);
    }

    /**
     * Scope to get prescriptions ready for dispensing.
     */
    public function scopeReadyForDispensing($query)
    {
        return $query->verified()
                    ->where('processing_status', self::PROCESSING_READY);
    }

    /**
     * Scope to get prescriptions requiring consultation.
     */
    public function scopeRequiringConsultation($query)
    {
        return $query->where('requires_consultation', true)
                    ->where('consultation_completed', false);
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'prescription_number',
                'patient_id',
                'prescriber_id',
                'medication_name',
                'verification_status',
                'processing_status',
                'is_controlled_substance',
                'quantity_prescribed',
                'refills_remaining',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Prescription {$eventName}: {$this->prescription_number}");
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->prescription_number) {
                $model->prescription_number = static::generatePrescriptionNumber();
            }
            
            if (!$model->expiration_date) {
                $model->expiration_date = $model->calculateExpirationDate();
            }
            
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}