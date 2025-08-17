<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class AuditLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_type',
        'entity_type',
        'entity_id',
        'entity_identifier',
        'user_id',
        'user_name',
        'user_type',
        'user_role',
        'ip_address',
        'user_agent',
        'session_id',
        'request_id',
        'route_name',
        'http_method',
        'url',
        'old_values',
        'new_values',
        'metadata',
        'is_phi_access',
        'is_controlled_substance',
        'is_financial_data',
        'requires_retention',
        'retention_years',
        'checksum',
        'is_verified',
        'verified_at',
        'source_system',
        'environment',
        'application_version',
        'description',
        'response_time_ms',
        'response_status',
        'access_granted',
        'risk_level',
        'data_classification',
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
        'is_phi_access' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'is_financial_data' => 'boolean',
        'requires_retention' => 'boolean',
        'retention_years' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'response_time_ms' => 'decimal:2',
        'response_status' => 'integer',
        'access_granted' => 'boolean',
    ];

    /**
     * Event type constants
     */
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_VIEWED = 'viewed';
    public const EVENT_ACCESSED = 'accessed';
    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_FAILED_LOGIN = 'failed_login';
    public const EVENT_PRESCRIPTION_CREATED = 'prescription_created';
    public const EVENT_PRESCRIPTION_DISPENSED = 'prescription_dispensed';
    public const EVENT_PRESCRIPTION_VERIFIED = 'prescription_verified';
    public const EVENT_PRESCRIPTION_REJECTED = 'prescription_rejected';
    public const EVENT_PATIENT_PROFILE_ACCESSED = 'patient_profile_accessed';
    public const EVENT_MEDICAL_RECORD_ACCESSED = 'medical_record_accessed';
    public const EVENT_PAYMENT_PROCESSED = 'payment_processed';
    public const EVENT_DATA_EXPORT = 'data_export';
    public const EVENT_SYSTEM_CONFIG_CHANGED = 'system_config_changed';
    public const EVENT_BACKUP_CREATED = 'backup_created';
    public const EVENT_BACKUP_RESTORED = 'backup_restored';

    /**
     * Risk level constants
     */
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';
    public const RISK_CRITICAL = 'critical';

    /**
     * Data classification constants
     */
    public const DATA_PUBLIC = 'public';
    public const DATA_INTERNAL = 'internal';
    public const DATA_CONFIDENTIAL = 'confidential';
    public const DATA_PHI = 'phi';
    public const DATA_PCI = 'pci';

    /**
     * Create an audit log entry.
     */
    public static function logEvent(
        string $eventType,
        ?string $entityType = null,
        ?int $entityId = null,
        array $options = []
    ): self {
        $user = auth()->user();
        $request = request();

        $data = array_merge([
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_type' => $user?->user_type,
            'user_role' => $user?->getRoleNames()->first(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'request_id' => $request->header('X-Request-ID') ?: \Illuminate\Support\Str::uuid(),
            'route_name' => $request->route()?->getName(),
            'http_method' => $request->method(),
            'url' => $request->fullUrl(),
            'source_system' => config('app.name'),
            'environment' => config('app.env'),
            'application_version' => config('app.version'),
            'risk_level' => self::RISK_LOW,
            'data_classification' => self::DATA_INTERNAL,
        ], $options);

        // Auto-detect PHI access
        if (self::isPHIAccess($eventType, $entityType, $options)) {
            $data['is_phi_access'] = true;
            $data['data_classification'] = self::DATA_PHI;
            $data['risk_level'] = self::RISK_HIGH;
        }

        // Auto-detect controlled substance access
        if (self::isControlledSubstanceAccess($eventType, $entityType, $options)) {
            $data['is_controlled_substance'] = true;
            $data['risk_level'] = self::RISK_HIGH;
        }

        // Auto-detect financial data access
        if (self::isFinancialDataAccess($eventType, $entityType, $options)) {
            $data['is_financial_data'] = true;
            $data['data_classification'] = self::DATA_PCI;
            $data['risk_level'] = self::RISK_MEDIUM;
        }

        // Generate checksum for data integrity
        $data['checksum'] = self::generateChecksum($data);

        return static::create($data);
    }

    /**
     * Log PHI access event.
     */
    public static function logPHIAccess(
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        array $options = []
    ): self {
        return self::logEvent($eventType, $entityType, $entityId, array_merge($options, [
            'is_phi_access' => true,
            'data_classification' => self::DATA_PHI,
            'risk_level' => self::RISK_HIGH,
            'requires_retention' => true,
            'retention_years' => 6, // HIPAA minimum
        ]));
    }

    /**
     * Log controlled substance access event.
     */
    public static function logControlledSubstanceAccess(
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        array $options = []
    ): self {
        return self::logEvent($eventType, $entityType, $entityId, array_merge($options, [
            'is_controlled_substance' => true,
            'risk_level' => self::RISK_HIGH,
            'requires_retention' => true,
            'retention_years' => 2, // DEA requirement
        ]));
    }

    /**
     * Log financial data access event.
     */
    public static function logFinancialDataAccess(
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        array $options = []
    ): self {
        return self::logEvent($eventType, $entityType, $entityId, array_merge($options, [
            'is_financial_data' => true,
            'data_classification' => self::DATA_PCI,
            'risk_level' => self::RISK_MEDIUM,
            'requires_retention' => true,
            'retention_years' => 3, // PCI DSS requirement
        ]));
    }

    /**
     * Detect if this is PHI access.
     */
    private static function isPHIAccess(string $eventType, ?string $entityType, array $options): bool
    {
        // Explicit PHI flag
        if ($options['is_phi_access'] ?? false) {
            return true;
        }

        // PHI-related entities
        $phiEntities = [
            'User', 'UserProfile', 'Patient', 'Prescription', 'MedicalRecord',
            'InsuranceCard', 'EmergencyContact', 'MedicalHistory'
        ];

        if (in_array($entityType, $phiEntities)) {
            return true;
        }

        // PHI-related events
        $phiEvents = [
            self::EVENT_PATIENT_PROFILE_ACCESSED,
            self::EVENT_MEDICAL_RECORD_ACCESSED,
            self::EVENT_PRESCRIPTION_CREATED,
            self::EVENT_PRESCRIPTION_DISPENSED,
            self::EVENT_PRESCRIPTION_VERIFIED,
        ];

        return in_array($eventType, $phiEvents);
    }

    /**
     * Detect if this is controlled substance access.
     */
    private static function isControlledSubstanceAccess(string $eventType, ?string $entityType, array $options): bool
    {
        // Explicit controlled substance flag
        if ($options['is_controlled_substance'] ?? false) {
            return true;
        }

        // Check if the entity is a controlled substance prescription
        if ($entityType === 'Prescription' && isset($options['metadata']['is_controlled_substance'])) {
            return $options['metadata']['is_controlled_substance'];
        }

        return false;
    }

    /**
     * Detect if this is financial data access.
     */
    private static function isFinancialDataAccess(string $eventType, ?string $entityType, array $options): bool
    {
        // Explicit financial data flag
        if ($options['is_financial_data'] ?? false) {
            return true;
        }

        // Financial-related entities
        $financialEntities = [
            'Payment', 'PaymentMethod', 'CreditCard', 'BankAccount', 'Invoice', 'Transaction'
        ];

        if (in_array($entityType, $financialEntities)) {
            return true;
        }

        // Financial-related events
        $financialEvents = [
            self::EVENT_PAYMENT_PROCESSED,
        ];

        return in_array($eventType, $financialEvents);
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
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related entity (polymorphic relationship).
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Scope to get PHI access logs.
     */
    public function scopePhiAccess($query)
    {
        return $query->where('is_phi_access', true);
    }

    /**
     * Scope to get controlled substance logs.
     */
    public function scopeControlledSubstance($query)
    {
        return $query->where('is_controlled_substance', true);
    }

    /**
     * Scope to get financial data logs.
     */
    public function scopeFinancialData($query)
    {
        return $query->where('is_financial_data', true);
    }

    /**
     * Scope to get logs by event type.
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get logs by entity type.
     */
    public function scopeEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope to get logs by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs by risk level.
     */
    public function scopeRiskLevel($query, string $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    /**
     * Scope to get logs by data classification.
     */
    public function scopeDataClassification($query, string $classification)
    {
        return $query->where('data_classification', $classification);
    }

    /**
     * Scope to get logs within date range.
     */
    public function scopeWithinDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get logs requiring retention.
     */
    public function scopeRequiringRetention($query)
    {
        return $query->where('requires_retention', true);
    }

    /**
     * Scope to get expired retention logs.
     */
    public function scopeExpiredRetention($query)
    {
        return $query->where('requires_retention', true)
                     ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) < ?', [now()]);
    }

    /**
     * Scope to get failed access attempts.
     */
    public function scopeFailedAccess($query)
    {
        return $query->where('access_granted', false);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion of audit logs for compliance
        static::deleting(function ($model) {
            throw new \Exception('Audit logs cannot be deleted for compliance reasons.');
        });

        // Prevent updates to audit logs for integrity
        static::updating(function ($model) {
            throw new \Exception('Audit logs cannot be modified for integrity reasons.');
        });
    }
}