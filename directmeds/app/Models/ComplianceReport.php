<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ComplianceReport extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_type',
        'report_name',
        'report_period',
        'period_start',
        'period_end',
        'report_identifier',
        'status',
        'parameters',
        'filters',
        'description',
        'regulatory_framework',
        'compliance_standard',
        'criticality',
        'summary_data',
        'detailed_findings',
        'violations',
        'recommendations',
        'raw_data',
        'file_path',
        'file_format',
        'file_size',
        'file_hash',
        'generated_by',
        'generation_started_at',
        'generation_completed_at',
        'generation_time_seconds',
        'generation_errors',
        'compliance_score',
        'total_records_analyzed',
        'violations_found',
        'warnings_found',
        'exceptions_found',
        'requires_retention',
        'retention_years',
        'retention_expiry_date',
        'is_archived',
        'archived_at',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'distribution_list',
        'distributed_at',
        'distribution_log',
        'system_version',
        'report_template_version',
        'system_state',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'parameters' => 'array',
        'filters' => 'array',
        'summary_data' => 'array',
        'detailed_findings' => 'array',
        'violations' => 'array',
        'recommendations' => 'array',
        'generation_started_at' => 'datetime',
        'generation_completed_at' => 'datetime',
        'generation_time_seconds' => 'integer',
        'compliance_score' => 'decimal:2',
        'total_records_analyzed' => 'integer',
        'violations_found' => 'integer',
        'warnings_found' => 'integer',
        'exceptions_found' => 'integer',
        'requires_retention' => 'boolean',
        'retention_years' => 'integer',
        'retention_expiry_date' => 'date',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'distribution_list' => 'array',
        'distributed_at' => 'datetime',
        'distribution_log' => 'array',
        'system_state' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Report type constants
     */
    public const TYPE_HIPAA_ACCESS = 'hipaa_access';
    public const TYPE_HIPAA_SECURITY = 'hipaa_security';
    public const TYPE_DEA_CONTROLLED_SUBSTANCES = 'dea_controlled_substances';
    public const TYPE_DEA_INVENTORY = 'dea_inventory';
    public const TYPE_PCI_COMPLIANCE = 'pci_compliance';
    public const TYPE_AUDIT_TRAIL = 'audit_trail';
    public const TYPE_DATA_RETENTION = 'data_retention';
    public const TYPE_USER_ACCESS = 'user_access';
    public const TYPE_SECURITY_INCIDENTS = 'security_incidents';
    public const TYPE_PRESCRIPTION_MONITORING = 'prescription_monitoring';
    public const TYPE_FAILED_LOGINS = 'failed_logins';
    public const TYPE_DATA_EXPORTS = 'data_exports';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Review status constants
     */
    public const REVIEW_PENDING = 'pending';
    public const REVIEW_UNDER_REVIEW = 'under_review';
    public const REVIEW_APPROVED = 'approved';
    public const REVIEW_REJECTED = 'rejected';

    /**
     * Regulatory framework constants
     */
    public const FRAMEWORK_HIPAA = 'HIPAA';
    public const FRAMEWORK_DEA = 'DEA';
    public const FRAMEWORK_PCI_DSS = 'PCI-DSS';
    public const FRAMEWORK_SOX = 'SOX';
    public const FRAMEWORK_INTERNAL = 'Internal';

    /**
     * Criticality constants
     */
    public const CRITICALITY_LOW = 'low';
    public const CRITICALITY_MEDIUM = 'medium';
    public const CRITICALITY_HIGH = 'high';
    public const CRITICALITY_CRITICAL = 'critical';

    /**
     * Report period constants
     */
    public const PERIOD_DAILY = 'daily';
    public const PERIOD_WEEKLY = 'weekly';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_QUARTERLY = 'quarterly';
    public const PERIOD_YEARLY = 'yearly';
    public const PERIOD_CUSTOM = 'custom';

    /**
     * Create a new compliance report.
     */
    public static function createReport(
        string $reportType,
        string $reportName,
        Carbon $periodStart,
        Carbon $periodEnd,
        array $options = []
    ): self {
        $reportIdentifier = self::generateReportIdentifier($reportType, $periodStart, $periodEnd);

        $data = array_merge([
            'report_type' => $reportType,
            'report_name' => $reportName,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'report_identifier' => $reportIdentifier,
            'status' => self::STATUS_PENDING,
            'generated_by' => auth()->id(),
            'regulatory_framework' => self::getDefaultFramework($reportType),
            'criticality' => self::CRITICALITY_MEDIUM,
            'requires_retention' => true,
            'retention_years' => self::getDefaultRetentionYears($reportType),
            'system_version' => config('app.version'),
            'report_template_version' => '1.0',
        ], $options);

        return static::create($data);
    }

    /**
     * Generate a unique report identifier.
     */
    public static function generateReportIdentifier(string $reportType, Carbon $periodStart, Carbon $periodEnd): string
    {
        $prefix = strtoupper(substr($reportType, 0, 3));
        $dateRange = $periodStart->format('Ymd') . '_' . $periodEnd->format('Ymd');
        $suffix = strtoupper(substr(uniqid(), -6));
        
        return "{$prefix}_{$dateRange}_{$suffix}";
    }

    /**
     * Get default regulatory framework for report type.
     */
    public static function getDefaultFramework(string $reportType): string
    {
        return match ($reportType) {
            self::TYPE_HIPAA_ACCESS, self::TYPE_HIPAA_SECURITY => self::FRAMEWORK_HIPAA,
            self::TYPE_DEA_CONTROLLED_SUBSTANCES, self::TYPE_DEA_INVENTORY => self::FRAMEWORK_DEA,
            self::TYPE_PCI_COMPLIANCE => self::FRAMEWORK_PCI_DSS,
            default => self::FRAMEWORK_INTERNAL,
        };
    }

    /**
     * Get default retention years for report type.
     */
    public static function getDefaultRetentionYears(string $reportType): int
    {
        return match ($reportType) {
            self::TYPE_HIPAA_ACCESS, self::TYPE_HIPAA_SECURITY => 6,
            self::TYPE_DEA_CONTROLLED_SUBSTANCES, self::TYPE_DEA_INVENTORY => 2,
            self::TYPE_PCI_COMPLIANCE => 3,
            default => 7,
        };
    }

    /**
     * Start report generation.
     */
    public function startGeneration(): void
    {
        $this->update([
            'status' => self::STATUS_GENERATING,
            'generation_started_at' => now(),
        ]);
    }

    /**
     * Complete report generation successfully.
     */
    public function completeGeneration(array $reportData = []): void
    {
        $endTime = now();
        $generationTime = $this->generation_started_at 
            ? $endTime->diffInSeconds($this->generation_started_at)
            : null;

        $this->update(array_merge([
            'status' => self::STATUS_COMPLETED,
            'generation_completed_at' => $endTime,
            'generation_time_seconds' => $generationTime,
        ], $reportData));
    }

    /**
     * Mark report generation as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'generation_completed_at' => now(),
            'generation_errors' => $error,
        ]);
    }

    /**
     * Save report file and update metadata.
     */
    public function saveReportFile(string $content, string $format = 'pdf'): void
    {
        $filename = $this->report_identifier . '.' . $format;
        $path = "compliance-reports/{$this->report_type}/" . now()->format('Y/m') . "/{$filename}";
        
        Storage::disk('local')->put($path, $content);
        
        $this->update([
            'file_path' => $path,
            'file_format' => $format,
            'file_size' => Storage::disk('local')->size($path),
            'file_hash' => hash('sha256', $content),
        ]);
    }

    /**
     * Get report file content.
     */
    public function getReportFileContent(): ?string
    {
        if (!$this->file_path || !Storage::disk('local')->exists($this->file_path)) {
            return null;
        }

        return Storage::disk('local')->get($this->file_path);
    }

    /**
     * Verify file integrity.
     */
    public function verifyFileIntegrity(): bool
    {
        $content = $this->getReportFileContent();
        if (!$content) {
            return false;
        }

        return hash('sha256', $content) === $this->file_hash;
    }

    /**
     * Archive the report.
     */
    public function archive(): void
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }

    /**
     * Submit for review.
     */
    public function submitForReview(): void
    {
        $this->update([
            'review_status' => self::REVIEW_UNDER_REVIEW,
        ]);
    }

    /**
     * Approve the report.
     */
    public function approve(?string $notes = null): void
    {
        $this->update([
            'review_status' => self::REVIEW_APPROVED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Reject the report.
     */
    public function reject(string $reason): void
    {
        $this->update([
            'review_status' => self::REVIEW_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);
    }

    /**
     * Distribute the report.
     */
    public function distribute(array $recipients): void
    {
        $distributionLog = $this->distribution_log ?? [];
        $distributionLog[] = [
            'timestamp' => now(),
            'recipients' => $recipients,
            'distributed_by' => auth()->id(),
        ];

        $this->update([
            'distributed_at' => now(),
            'distribution_log' => $distributionLog,
        ]);
    }

    /**
     * Check if report is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return false;
        }

        // Define expected completion times based on report type
        $expectedHours = match ($this->report_type) {
            self::TYPE_DAILY => 4,
            self::TYPE_WEEKLY => 24,
            self::TYPE_MONTHLY => 72,
            default => 168, // 1 week
        };

        return $this->created_at->addHours($expectedHours)->isPast();
    }

    /**
     * Get compliance score color.
     */
    public function getComplianceScoreColorAttribute(): string
    {
        if (!$this->compliance_score) {
            return 'gray';
        }

        return match (true) {
            $this->compliance_score >= 95 => 'green',
            $this->compliance_score >= 80 => 'yellow',
            $this->compliance_score >= 60 => 'orange',
            default => 'red',
        };
    }

    /**
     * Get compliance status.
     */
    public function getComplianceStatusAttribute(): string
    {
        if (!$this->compliance_score) {
            return 'Unknown';
        }

        return match (true) {
            $this->compliance_score >= 95 => 'Excellent',
            $this->compliance_score >= 80 => 'Good',
            $this->compliance_score >= 60 => 'Needs Improvement',
            default => 'Critical',
        };
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
     * Get the user who generated this report.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the user who reviewed this report.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to get reports by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to get reports by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get reports by regulatory framework.
     */
    public function scopeByFramework($query, string $framework)
    {
        return $query->where('regulatory_framework', $framework);
    }

    /**
     * Scope to get reports within date range.
     */
    public function scopeWithinPeriod($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('period_start', [$startDate, $endDate])
              ->orWhereBetween('period_end', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('period_start', '<=', $startDate)
                     ->where('period_end', '>=', $endDate);
              });
        });
    }

    /**
     * Scope to get overdue reports.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_ARCHIVED])
                     ->where('created_at', '<', now()->subHours(24));
    }

    /**
     * Scope to get reports requiring review.
     */
    public function scopeRequiringReview($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
                     ->where('review_status', self::REVIEW_PENDING);
    }

    /**
     * Scope to get expired retention reports.
     */
    public function scopeExpiredRetention($query)
    {
        return $query->where('requires_retention', true)
                     ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) < ?', [now()]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set retention expiry date when creating
        static::creating(function ($model) {
            if ($model->requires_retention && !$model->retention_expiry_date) {
                $model->retention_expiry_date = $model->created_at->addYears($model->retention_years);
            }
        });
    }
}