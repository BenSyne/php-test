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

class Prescriber extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'title',
        'npi_number',
        'dea_number',
        'state_license_number',
        'state_license_state',
        'state_license_expiry',
        'additional_licenses',
        'specialty',
        'subspecialty',
        'email',
        'phone',
        'fax',
        'practice_name',
        'practice_address',
        'practice_city',
        'practice_state',
        'practice_zip',
        'practice_phone',
        'practice_fax',
        'dea_schedule',
        'dea_expiry',
        'dea_activity_code',
        'dea_business_activity',
        'verification_status',
        'verified_at',
        'verified_by',
        'verification_notes',
        'is_active',
        'last_prescription_date',
        'total_prescriptions',
        'compliance_flags',
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
        'state_license_expiry' => 'date',
        'dea_expiry' => 'date',
        'verified_at' => 'datetime',
        'last_prescription_date' => 'datetime',
        'is_active' => 'boolean',
        'total_prescriptions' => 'integer',
        'additional_licenses' => 'array',
        'compliance_flags' => 'array',
    ];

    /**
     * Verification status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_EXPIRED = 'expired';

    /**
     * DEA schedule constants
     */
    public const DEA_SCHEDULE_I = 'I';
    public const DEA_SCHEDULE_II = 'II';
    public const DEA_SCHEDULE_III = 'III';
    public const DEA_SCHEDULE_IV = 'IV';
    public const DEA_SCHEDULE_V = 'V';

    /**
     * Get all verification statuses
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_SUSPENDED,
            self::STATUS_REVOKED,
            self::STATUS_EXPIRED,
        ];
    }

    /**
     * Get all DEA schedules
     */
    public static function getDeaSchedules(): array
    {
        return [
            self::DEA_SCHEDULE_I,
            self::DEA_SCHEDULE_II,
            self::DEA_SCHEDULE_III,
            self::DEA_SCHEDULE_IV,
            self::DEA_SCHEDULE_V,
        ];
    }

    /**
     * Get the prescriber's full name.
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        
        if ($this->middle_name) {
            $name = $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
        }
        
        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }
        
        return $name;
    }

    /**
     * Get the prescriber's display name with title.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        
        if ($this->title) {
            $name = $this->title . ' ' . $name;
        }
        
        return $name;
    }

    /**
     * Get the prescriber's practice address formatted.
     */
    public function getFormattedPracticeAddressAttribute(): string
    {
        return sprintf(
            '%s, %s, %s %s',
            $this->practice_address,
            $this->practice_city,
            $this->practice_state,
            $this->practice_zip
        );
    }

    /**
     * Check if prescriber is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === self::STATUS_VERIFIED;
    }

    /**
     * Check if prescriber is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    /**
     * Check if prescriber is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->verification_status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if prescriber is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->verification_status === self::STATUS_REVOKED;
    }

    /**
     * Check if prescriber verification has expired.
     */
    public function isExpired(): bool
    {
        return $this->verification_status === self::STATUS_EXPIRED;
    }

    /**
     * Check if prescriber can prescribe (is verified and active).
     */
    public function canPrescribe(): bool
    {
        return $this->is_active && $this->isVerified() && $this->hasValidLicense();
    }

    /**
     * Check if prescriber has a valid state license.
     */
    public function hasValidLicense(): bool
    {
        return $this->state_license_number && 
               $this->state_license_expiry && 
               $this->state_license_expiry->isFuture();
    }

    /**
     * Check if prescriber has a valid DEA registration.
     */
    public function hasValidDea(): bool
    {
        if (!$this->dea_number) {
            return false;
        }

        return !$this->dea_expiry || $this->dea_expiry->isFuture();
    }

    /**
     * Check if prescriber can prescribe controlled substances.
     */
    public function canPrescribeControlledSubstances(): bool
    {
        return $this->canPrescribe() && $this->hasValidDea();
    }

    /**
     * Check if prescriber can prescribe a specific controlled substance schedule.
     */
    public function canPrescribeSchedule(string $schedule): bool
    {
        if (!$this->canPrescribeControlledSubstances()) {
            return false;
        }

        if (!$this->dea_schedule) {
            return false;
        }

        // Convert Roman numerals to numbers for comparison
        $allowedSchedules = $this->parseDeaSchedule($this->dea_schedule);
        $requestedSchedule = $this->parseDeaSchedule($schedule);

        return in_array($requestedSchedule, $allowedSchedules);
    }

    /**
     * Parse DEA schedule to get all allowed schedules.
     */
    private function parseDeaSchedule(string $schedule): array
    {
        $scheduleMap = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
        ];

        if (!isset($scheduleMap[$schedule])) {
            return [];
        }

        $maxSchedule = $scheduleMap[$schedule];
        $allowedSchedules = [];

        // A prescriber with Schedule II authorization can prescribe II-V
        // A prescriber with Schedule III authorization can prescribe III-V, etc.
        for ($i = $maxSchedule; $i <= 5; $i++) {
            $allowedSchedules[] = $i;
        }

        return $allowedSchedules;
    }

    /**
     * Validate NPI number format.
     */
    public function validateNpiNumber(string $npi): bool
    {
        // NPI must be 10 digits
        if (!preg_match('/^\d{10}$/', $npi)) {
            return false;
        }

        // Implement Luhn algorithm for NPI validation
        return $this->luhnCheck($npi);
    }

    /**
     * Validate DEA number format.
     */
    public function validateDeaNumber(string $dea): bool
    {
        // DEA format: 2 letters + 7 digits
        if (!preg_match('/^[A-Z]{2}\d{7}$/', $dea)) {
            return false;
        }

        // First letter must be A, B, F, or M
        $firstLetter = substr($dea, 0, 1);
        if (!in_array($firstLetter, ['A', 'B', 'F', 'M'])) {
            return false;
        }

        // Calculate checksum
        $numbers = substr($dea, 2, 7);
        $sum1 = intval($numbers[0]) + intval($numbers[2]) + intval($numbers[4]);
        $sum2 = intval($numbers[1]) + intval($numbers[3]) + intval($numbers[5]);
        $checksum = ($sum1 + 2 * $sum2) % 10;

        return $checksum === intval($numbers[6]);
    }

    /**
     * Luhn algorithm implementation for NPI validation.
     */
    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $alternate = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = intval($number[$i]);

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }

    /**
     * Mark prescriber as verified.
     */
    public function markAsVerified(?int $verifiedBy = null, ?string $notes = null): void
    {
        $this->update([
            'verification_status' => self::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => $verifiedBy ?? auth()->id(),
            'verification_notes' => $notes,
        ]);
    }

    /**
     * Mark prescriber as suspended.
     */
    public function markAsSuspended(?string $reason = null): void
    {
        $this->update([
            'verification_status' => self::STATUS_SUSPENDED,
            'verification_notes' => $reason,
        ]);
    }

    /**
     * Mark prescriber as revoked.
     */
    public function markAsRevoked(?string $reason = null): void
    {
        $this->update([
            'verification_status' => self::STATUS_REVOKED,
            'is_active' => false,
            'verification_notes' => $reason,
        ]);
    }

    /**
     * Add compliance flag.
     */
    public function addComplianceFlag(string $flag, ?string $description = null): void
    {
        $flags = $this->compliance_flags ?? [];
        $flags[] = [
            'flag' => $flag,
            'description' => $description,
            'added_at' => now()->toISOString(),
            'added_by' => auth()->id(),
        ];

        $this->update(['compliance_flags' => $flags]);
    }

    /**
     * Remove compliance flag.
     */
    public function removeComplianceFlag(string $flag): void
    {
        $flags = $this->compliance_flags ?? [];
        $flags = array_filter($flags, function ($item) use ($flag) {
            return $item['flag'] !== $flag;
        });

        $this->update(['compliance_flags' => array_values($flags)]);
    }

    /**
     * Check if prescriber has compliance flag.
     */
    public function hasComplianceFlag(string $flag): bool
    {
        $flags = $this->compliance_flags ?? [];
        
        foreach ($flags as $item) {
            if ($item['flag'] === $flag) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the prescriptions for this prescriber.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the user who verified this prescriber.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who created this prescriber.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this prescriber.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this prescriber.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope to get only verified prescribers.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::STATUS_VERIFIED);
    }

    /**
     * Scope to get only active prescribers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get prescribers who can prescribe.
     */
    public function scopeCanPrescribe($query)
    {
        return $query->active()
                    ->verified()
                    ->where('state_license_expiry', '>', now());
    }

    /**
     * Scope to get prescribers who can prescribe controlled substances.
     */
    public function scopeCanPrescribeControlled($query)
    {
        return $query->canPrescribe()
                    ->whereNotNull('dea_number')
                    ->where(function ($q) {
                        $q->whereNull('dea_expiry')
                          ->orWhere('dea_expiry', '>', now());
                    });
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'npi_number',
                'dea_number',
                'state_license_number',
                'verification_status',
                'is_active',
                'compliance_flags',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Prescriber {$eventName}: {$this->full_name}");
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
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