<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PrescriptionAuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prescription_id',
        'action',
        'action_type',
        'description',
        'user_id',
        'user_name',
        'user_type',
        'user_role',
        'ip_address',
        'user_agent',
        'session_id',
        'old_values',
        'new_values',
        'metadata',
        'is_hipaa_action',
        'is_dea_action',
        'requires_retention',
        'retention_years',
        'checksum',
        'is_verified',
        'verified_at',
        'source_system',
        'environment',
        'application_version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'is_hipaa_action' => 'boolean',
        'is_dea_action' => 'boolean',
        'requires_retention' => 'boolean',
        'retention_years' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Action constants
     */
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_VERIFIED = 'verified';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_DISPENSED = 'dispensed';
    public const ACTION_REVIEWED = 'reviewed';
    public const ACTION_ON_HOLD = 'on_hold';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_REFILLED = 'refilled';
    public const ACTION_TRANSFERRED = 'transferred';
    public const ACTION_CONSULTATION = 'consultation';
    public const ACTION_SYSTEM_UPDATE = 'system_update';

    /**
     * Action type constants
     */
    public const TYPE_USER = 'user';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_AUTOMATED = 'automated';

    /**
     * HIPAA sensitive actions
     */
    public const HIPAA_ACTIONS = [
        self::ACTION_CREATED,
        self::ACTION_UPDATED,
        self::ACTION_VERIFIED,
        self::ACTION_DISPENSED,
        self::ACTION_REVIEWED,
        self::ACTION_CONSULTATION,
    ];

    /**
     * DEA sensitive actions (for controlled substances)
     */
    public const DEA_ACTIONS = [
        self::ACTION_CREATED,
        self::ACTION_VERIFIED,
        self::ACTION_DISPENSED,
        self::ACTION_TRANSFERRED,
        self::ACTION_CANCELLED,
    ];

    /**
     * Create an audit log entry.
     */
    public static function createLog(
        int $prescriptionId,
        string $action,
        string $description,
        array $options = []
    ): self {
        $user = auth()->user();
        
        $data = array_merge([
            'prescription_id' => $prescriptionId,
            'action' => $action,
            'action_type' => self::TYPE_USER,
            'description' => $description,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_type' => $user?->user_type,
            'user_role' => $user?->getRoleNames()->first(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'is_hipaa_action' => in_array($action, self::HIPAA_ACTIONS),
            'is_dea_action' => in_array($action, self::DEA_ACTIONS),
            'source_system' => config('app.name'),
            'environment' => config('app.env'),
            'application_version' => config('app.version'),
        ], $options);

        // Generate checksum for data integrity
        $data['checksum'] = self::generateChecksum($data);

        return static::create($data);
    }

    /**
     * Create an audit log for prescription creation.
     */
    public static function logCreated(Prescription $prescription, array $metadata = []): self
    {
        return self::createLog(
            $prescription->id,
            self::ACTION_CREATED,
            "Prescription {$prescription->prescription_number} created for {$prescription->patient_name}",
            [
                'metadata' => array_merge([
                    'medication' => $prescription->medication_name,
                    'prescriber' => $prescription->prescriber_name,
                    'quantity' => $prescription->quantity_prescribed,
                ], $metadata),
                'new_values' => $prescription->toArray(),
                'is_dea_action' => $prescription->isControlledSubstance(),
            ]
        );
    }

    /**
     * Create an audit log for prescription update.
     */
    public static function logUpdated(Prescription $prescription, array $oldValues, array $metadata = []): self
    {
        $changes = array_diff_assoc($prescription->toArray(), $oldValues);
        
        return self::createLog(
            $prescription->id,
            self::ACTION_UPDATED,
            "Prescription {$prescription->prescription_number} updated",
            [
                'old_values' => $oldValues,
                'new_values' => $changes,
                'metadata' => array_merge([
                    'fields_changed' => array_keys($changes),
                ], $metadata),
                'is_dea_action' => $prescription->isControlledSubstance(),
            ]
        );
    }

    /**
     * Create an audit log for prescription verification.
     */
    public static function logVerified(Prescription $prescription, ?string $notes = null): self
    {
        return self::createLog(
            $prescription->id,
            self::ACTION_VERIFIED,
            "Prescription {$prescription->prescription_number} verified by pharmacist",
            [
                'metadata' => [
                    'verification_notes' => $notes,
                    'pharmacist_id' => $prescription->reviewing_pharmacist_id,
                    'review_duration' => $prescription->review_started_at 
                        ? now()->diffInMinutes($prescription->review_started_at) 
                        : null,
                ],
                'is_dea_action' => $prescription->isControlledSubstance(),
            ]
        );
    }

    /**
     * Create an audit log for prescription rejection.
     */
    public static function logRejected(Prescription $prescription, string $reason): self
    {
        return self::createLog(
            $prescription->id,
            self::ACTION_REJECTED,
            "Prescription {$prescription->prescription_number} rejected by pharmacist",
            [
                'metadata' => [
                    'rejection_reason' => $reason,
                    'pharmacist_id' => $prescription->reviewing_pharmacist_id,
                ],
                'is_dea_action' => $prescription->isControlledSubstance(),
            ]
        );
    }

    /**
     * Create an audit log for prescription dispensing.
     */
    public static function logDispensed(Prescription $prescription, array $dispensingData = []): self
    {
        return self::createLog(
            $prescription->id,
            self::ACTION_DISPENSED,
            "Prescription {$prescription->prescription_number} dispensed to {$prescription->patient_name}",
            [
                'metadata' => array_merge([
                    'quantity_dispensed' => $prescription->quantity_dispensed,
                    'lot_number' => $prescription->lot_number,
                    'pharmacist_id' => $prescription->dispensing_pharmacist_id,
                    'ndc_dispensed' => $prescription->ndc_dispensed,
                ], $dispensingData),
                'is_dea_action' => $prescription->isControlledSubstance(),
            ]
        );
    }

    /**
     * Create an audit log for consultation.
     */
    public static function logConsultation(Prescription $prescription, array $consultationData = []): self
    {
        return self::createLog(
            $prescription->id,
            self::ACTION_CONSULTATION,
            "Patient consultation completed for prescription {$prescription->prescription_number}",
            [
                'metadata' => array_merge([
                    'consultation_pharmacist_id' => $prescription->consultation_pharmacist_id,
                    'consultation_duration' => $consultationData['duration'] ?? null,
                    'consultation_notes' => $consultationData['notes'] ?? null,
                ], $consultationData),
            ]
        );
    }

    /**
     * Create an audit log for system updates.
     */
    public static function logSystemUpdate(
        int $prescriptionId,
        string $description,
        array $metadata = []
    ): self {
        return self::createLog(
            $prescriptionId,
            self::ACTION_SYSTEM_UPDATE,
            $description,
            [
                'action_type' => self::TYPE_SYSTEM,
                'user_id' => null,
                'user_name' => 'System',
                'user_type' => 'system',
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Generate checksum for data integrity.
     */
    public static function generateChecksum(array $data): string
    {
        // Remove checksum field if it exists
        unset($data['checksum']);
        
        // Sort array by keys for consistent checksum
        ksort($data);
        
        // Convert to JSON and generate hash
        return hash('sha256', json_encode($data));
    }

    /**
     * Verify data integrity using checksum.
     */
    public function verifyIntegrity(): bool
    {
        $data = $this->toArray();
        $storedChecksum = $data['checksum'];
        unset($data['checksum'], $data['id'], $data['created_at'], $data['updated_at']);
        
        $calculatedChecksum = self::generateChecksum($data);
        
        return $storedChecksum === $calculatedChecksum;
    }

    /**
     * Check if this is a HIPAA-sensitive action.
     */
    public function isHipaaAction(): bool
    {
        return $this->is_hipaa_action;
    }

    /**
     * Check if this is a DEA-sensitive action.
     */
    public function isDeaAction(): bool
    {
        return $this->is_dea_action;
    }

    /**
     * Check if this record requires retention.
     */
    public function requiresRetention(): bool
    {
        return $this->requires_retention;
    }

    /**
     * Get retention expiry date.
     */
    public function getRetentionExpiryDateAttribute(): Carbon
    {
        return $this->created_at->addYears($this->retention_years);
    }

    /**
     * Check if retention period has expired.
     */
    public function isRetentionExpired(): bool
    {
        return now()->isAfter($this->retention_expiry_date);
    }

    /**
     * Get the prescription for this audit log.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get HIPAA-sensitive logs.
     */
    public function scopeHipaaActions($query)
    {
        return $query->where('is_hipaa_action', true);
    }

    /**
     * Scope to get DEA-sensitive logs.
     */
    public function scopeDeaActions($query)
    {
        return $query->where('is_dea_action', true);
    }

    /**
     * Scope to get logs requiring retention.
     */
    public function scopeRequiringRetention($query)
    {
        return $query->where('requires_retention', true);
    }

    /**
     * Scope to get logs by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs within date range.
     */
    public function scopeWithinDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion of audit logs
        static::deleting(function ($model) {
            throw new \Exception('Audit logs cannot be deleted for compliance reasons.');
        });
    }
}