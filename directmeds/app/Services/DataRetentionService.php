<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ComplianceReport;
use App\Models\Prescription;
use App\Models\PrescriptionAuditLog;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    /**
     * Data retention policies by data type and regulatory requirement.
     */
    private const RETENTION_POLICIES = [
        'hipaa_audit_logs' => ['years' => 6, 'regulation' => 'HIPAA'],
        'dea_audit_logs' => ['years' => 2, 'regulation' => 'DEA'],
        'prescription_records' => ['years' => 7, 'regulation' => 'State/Federal'],
        'patient_records' => ['years' => 10, 'regulation' => 'HIPAA'],
        'financial_records' => ['years' => 7, 'regulation' => 'IRS'],
        'compliance_reports' => ['years' => 7, 'regulation' => 'Multiple'],
        'system_audit_logs' => ['years' => 3, 'regulation' => 'Internal'],
        'user_access_logs' => ['years' => 1, 'regulation' => 'Internal'],
    ];

    /**
     * Get comprehensive retention summary.
     */
    public function getRetentionSummary(): array
    {
        return [
            'overview' => $this->getRetentionOverview(),
            'by_data_type' => $this->getRetentionByDataType(),
            'upcoming_expirations' => $this->getUpcomingExpirations(),
            'expired_data' => $this->getExpiredData(),
            'storage_metrics' => $this->getStorageMetrics(),
            'compliance_status' => $this->getComplianceStatus(),
        ];
    }

    /**
     * Get retention overview metrics.
     */
    private function getRetentionOverview(): array
    {
        return [
            'total_audit_logs' => AuditLog::count(),
            'audit_logs_requiring_retention' => AuditLog::requireingRetention()->count(),
            'expired_audit_logs' => AuditLog::expiredRetention()->count(),
            'total_compliance_reports' => ComplianceReport::count(),
            'expired_compliance_reports' => ComplianceReport::expiredRetention()->count(),
            'total_prescription_audit_logs' => PrescriptionAuditLog::count(),
            'expired_prescription_logs' => PrescriptionAuditLog::expiredRetention()->count(),
            'total_storage_gb' => $this->calculateTotalStorage(),
            'estimated_cleanup_gb' => $this->estimateCleanupStorage(),
        ];
    }

    /**
     * Get retention summary by data type.
     */
    private function getRetentionByDataType(): array
    {
        $dataTypes = [];

        // Audit Logs
        $dataTypes['audit_logs'] = [
            'total_records' => AuditLog::count(),
            'phi_records' => AuditLog::phiAccess()->count(),
            'controlled_substance_records' => AuditLog::controlledSubstance()->count(),
            'expired_records' => AuditLog::expiredRetention()->count(),
            'retention_periods' => [
                'hipaa' => self::RETENTION_POLICIES['hipaa_audit_logs']['years'],
                'dea' => self::RETENTION_POLICIES['dea_audit_logs']['years'],
                'system' => self::RETENTION_POLICIES['system_audit_logs']['years'],
            ],
            'next_expiration_date' => $this->getNextExpirationDate('audit_logs'),
            'storage_size_mb' => $this->calculateAuditLogStorage(),
        ];

        // Compliance Reports
        $dataTypes['compliance_reports'] = [
            'total_records' => ComplianceReport::count(),
            'hipaa_reports' => ComplianceReport::byFramework('HIPAA')->count(),
            'dea_reports' => ComplianceReport::byFramework('DEA')->count(),
            'expired_records' => ComplianceReport::expiredRetention()->count(),
            'retention_period_years' => self::RETENTION_POLICIES['compliance_reports']['years'],
            'next_expiration_date' => $this->getNextExpirationDate('compliance_reports'),
            'storage_size_mb' => $this->calculateComplianceReportStorage(),
        ];

        // Prescription Records
        $dataTypes['prescriptions'] = [
            'total_records' => Prescription::count(),
            'controlled_substance_records' => Prescription::where('is_controlled_substance', true)->count(),
            'expired_records' => $this->getExpiredPrescriptions()->count(),
            'retention_period_years' => self::RETENTION_POLICIES['prescription_records']['years'],
            'next_expiration_date' => $this->getNextExpirationDate('prescriptions'),
            'storage_size_mb' => $this->calculatePrescriptionStorage(),
        ];

        // Prescription Audit Logs
        $dataTypes['prescription_audit_logs'] = [
            'total_records' => PrescriptionAuditLog::count(),
            'hipaa_records' => PrescriptionAuditLog::hipaaActions()->count(),
            'dea_records' => PrescriptionAuditLog::deaActions()->count(),
            'expired_records' => PrescriptionAuditLog::expiredRetention()->count(),
            'retention_period_years' => max(
                self::RETENTION_POLICIES['hipaa_audit_logs']['years'],
                self::RETENTION_POLICIES['dea_audit_logs']['years']
            ),
            'next_expiration_date' => $this->getNextExpirationDate('prescription_audit_logs'),
            'storage_size_mb' => $this->calculatePrescriptionAuditLogStorage(),
        ];

        // User Records
        $dataTypes['user_records'] = [
            'total_records' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'deleted_users' => User::onlyTrashed()->count(),
            'retention_period_years' => self::RETENTION_POLICIES['patient_records']['years'],
            'next_expiration_date' => $this->getNextExpirationDate('user_records'),
            'storage_size_mb' => $this->calculateUserRecordStorage(),
        ];

        return $dataTypes;
    }

    /**
     * Get upcoming expirations (next 90 days).
     */
    private function getUpcomingExpirations(): array
    {
        $cutoffDate = now()->addDays(90);
        
        return [
            'audit_logs' => AuditLog::requireingRetention()
                ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) <= ?', [$cutoffDate])
                ->count(),
            'compliance_reports' => ComplianceReport::where('requires_retention', true)
                ->where('retention_expiry_date', '<=', $cutoffDate)
                ->count(),
            'prescription_audit_logs' => PrescriptionAuditLog::requireingRetention()
                ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) <= ?', [$cutoffDate])
                ->count(),
            'by_month' => $this->getExpirationsByMonth(),
        ];
    }

    /**
     * Get expired data that can be archived/deleted.
     */
    private function getExpiredData(): array
    {
        return [
            'audit_logs' => [
                'total_expired' => AuditLog::expiredRetention()->count(),
                'phi_expired' => AuditLog::phiAccess()->expiredRetention()->count(),
                'controlled_substance_expired' => AuditLog::controlledSubstance()->expiredRetention()->count(),
                'system_expired' => AuditLog::where('is_phi_access', false)
                    ->where('is_controlled_substance', false)
                    ->expiredRetention()->count(),
            ],
            'compliance_reports' => [
                'total_expired' => ComplianceReport::expiredRetention()->count(),
                'by_framework' => $this->getExpiredReportsByFramework(),
            ],
            'prescription_audit_logs' => [
                'total_expired' => PrescriptionAuditLog::expiredRetention()->count(),
                'hipaa_expired' => PrescriptionAuditLog::hipaaActions()->expiredRetention()->count(),
                'dea_expired' => PrescriptionAuditLog::deaActions()->expiredRetention()->count(),
            ],
            'estimated_storage_recovery_gb' => $this->estimateCleanupStorage(),
        ];
    }

    /**
     * Get storage metrics.
     */
    private function getStorageMetrics(): array
    {
        return [
            'total_database_size_gb' => $this->calculateDatabaseSize(),
            'audit_logs_size_gb' => $this->calculateAuditLogStorage() / 1024,
            'compliance_reports_size_gb' => $this->calculateComplianceReportStorage() / 1024,
            'file_storage_size_gb' => $this->calculateFileStorage(),
            'archive_storage_size_gb' => $this->calculateArchiveStorage(),
            'growth_rate_per_month_gb' => $this->calculateGrowthRate(),
            'projected_storage_12_months_gb' => $this->projectStorageGrowth(12),
        ];
    }

    /**
     * Get compliance status.
     */
    private function getComplianceStatus(): array
    {
        $expiredRecords = [
            'audit_logs' => AuditLog::expiredRetention()->count(),
            'compliance_reports' => ComplianceReport::expiredRetention()->count(),
            'prescription_audit_logs' => PrescriptionAuditLog::expiredRetention()->count(),
        ];

        $totalExpired = array_sum($expiredRecords);

        return [
            'overall_compliance' => $totalExpired === 0 ? 'compliant' : 'non_compliant',
            'expired_record_count' => $totalExpired,
            'compliance_issues' => $this->identifyComplianceIssues(),
            'last_cleanup_date' => $this->getLastCleanupDate(),
            'next_recommended_cleanup' => $this->getNextRecommendedCleanup(),
            'retention_policy_violations' => $this->identifyRetentionViolations(),
        ];
    }

    /**
     * Execute retention policy cleanup.
     */
    public function executeRetentionPolicy(bool $dryRun = false): array
    {
        $results = [
            'dry_run' => $dryRun,
            'started_at' => now(),
            'processed' => 0,
            'archived' => 0,
            'deleted' => 0,
            'errors' => [],
            'storage_recovered_mb' => 0,
        ];

        DB::beginTransaction();

        try {
            // Process expired audit logs
            $auditLogResults = $this->processExpiredAuditLogs($dryRun);
            $results['audit_logs'] = $auditLogResults;
            $results['processed'] += $auditLogResults['processed'];
            $results['archived'] += $auditLogResults['archived'];
            $results['deleted'] += $auditLogResults['deleted'];

            // Process expired compliance reports
            $reportResults = $this->processExpiredComplianceReports($dryRun);
            $results['compliance_reports'] = $reportResults;
            $results['processed'] += $reportResults['processed'];
            $results['archived'] += $reportResults['archived'];
            $results['deleted'] += $reportResults['deleted'];

            // Process expired prescription audit logs
            $prescriptionLogResults = $this->processExpiredPrescriptionAuditLogs($dryRun);
            $results['prescription_audit_logs'] = $prescriptionLogResults;
            $results['processed'] += $prescriptionLogResults['processed'];
            $results['archived'] += $prescriptionLogResults['archived'];

            // Calculate storage recovered
            $results['storage_recovered_mb'] = $this->calculateStorageRecovered($results);

            if (!$dryRun) {
                DB::commit();
                $this->logRetentionCleanup($results);
            } else {
                DB::rollBack();
            }

            $results['completed_at'] = now();
            $results['duration_seconds'] = $results['completed_at']->diffInSeconds($results['started_at']);

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
            Log::error('Data retention cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * Process expired audit logs.
     */
    private function processExpiredAuditLogs(bool $dryRun): array
    {
        $results = ['processed' => 0, 'archived' => 0, 'deleted' => 0];

        // Get expired audit logs
        $expiredLogs = AuditLog::expiredRetention()->get();
        $results['processed'] = $expiredLogs->count();

        if (!$dryRun && $results['processed'] > 0) {
            // Archive high-value logs (PHI, controlled substances)
            $highValueLogs = $expiredLogs->filter(function ($log) {
                return $log->is_phi_access || $log->is_controlled_substance || 
                       $log->risk_level === AuditLog::RISK_HIGH;
            });

            foreach ($highValueLogs as $log) {
                $this->archiveAuditLog($log);
                $results['archived']++;
            }

            // Delete low-value logs
            $lowValueLogs = $expiredLogs->filter(function ($log) {
                return !$log->is_phi_access && !$log->is_controlled_substance && 
                       $log->risk_level !== AuditLog::RISK_HIGH;
            });

            // Note: We can't actually delete audit logs due to model protection
            // Instead, we mark them for deletion in a separate archive table
            foreach ($lowValueLogs as $log) {
                $this->markAuditLogForDeletion($log);
                $results['deleted']++;
            }
        }

        return $results;
    }

    /**
     * Process expired compliance reports.
     */
    private function processExpiredComplianceReports(bool $dryRun): array
    {
        $results = ['processed' => 0, 'archived' => 0, 'deleted' => 0];

        $expiredReports = ComplianceReport::expiredRetention()->get();
        $results['processed'] = $expiredReports->count();

        if (!$dryRun && $results['processed'] > 0) {
            foreach ($expiredReports as $report) {
                if ($report->criticality === ComplianceReport::CRITICALITY_CRITICAL ||
                    in_array($report->regulatory_framework, ['HIPAA', 'DEA'])) {
                    $report->archive();
                    $results['archived']++;
                } else {
                    // Archive low-priority reports and mark for potential deletion
                    $report->archive();
                    $results['archived']++;
                }
            }
        }

        return $results;
    }

    /**
     * Process expired prescription audit logs.
     */
    private function processExpiredPrescriptionAuditLogs(bool $dryRun): array
    {
        $results = ['processed' => 0, 'archived' => 0, 'deleted' => 0];

        $expiredLogs = PrescriptionAuditLog::expiredRetention()->get();
        $results['processed'] = $expiredLogs->count();

        if (!$dryRun && $results['processed'] > 0) {
            // All prescription audit logs are archived rather than deleted
            foreach ($expiredLogs as $log) {
                $this->archivePrescriptionAuditLog($log);
                $results['archived']++;
            }
        }

        return $results;
    }

    /**
     * Archive audit log to long-term storage.
     */
    private function archiveAuditLog(AuditLog $log): void
    {
        $archiveData = $log->toArray();
        $archiveData['archived_at'] = now();
        $archiveData['original_id'] = $log->id;
        
        // Store in archive storage (could be separate database, file system, etc.)
        $archivePath = "archives/audit-logs/" . now()->format('Y/m') . "/audit-log-{$log->id}.json";
        Storage::disk('local')->put($archivePath, json_encode($archiveData, JSON_PRETTY_PRINT));
        
        // Log the archival
        Log::info('Audit log archived', [
            'original_id' => $log->id,
            'archive_path' => $archivePath,
            'user_id' => $log->user_id,
            'event_type' => $log->event_type,
        ]);
    }

    /**
     * Mark audit log for deletion (since we can't actually delete due to model protection).
     */
    private function markAuditLogForDeletion(AuditLog $log): void
    {
        // Create a record in a deletion queue table or add metadata
        $deletionRecord = [
            'table_name' => 'audit_logs',
            'record_id' => $log->id,
            'marked_for_deletion_at' => now(),
            'retention_expired_at' => $log->retention_expiry_date,
            'reason' => 'retention_policy_expired',
        ];
        
        // Store deletion record (would typically be in a separate table)
        $deletionPath = "deletion-queue/audit-logs/" . now()->format('Y/m') . "/deletion-{$log->id}.json";
        Storage::disk('local')->put($deletionPath, json_encode($deletionRecord, JSON_PRETTY_PRINT));
        
        Log::info('Audit log marked for deletion', [
            'id' => $log->id,
            'event_type' => $log->event_type,
            'retention_expired' => $log->retention_expiry_date,
        ]);
    }

    /**
     * Archive prescription audit log.
     */
    private function archivePrescriptionAuditLog(PrescriptionAuditLog $log): void
    {
        $archiveData = $log->toArray();
        $archiveData['archived_at'] = now();
        $archiveData['original_id'] = $log->id;
        
        $archivePath = "archives/prescription-audit-logs/" . now()->format('Y/m') . "/prescription-audit-{$log->id}.json";
        Storage::disk('local')->put($archivePath, json_encode($archiveData, JSON_PRETTY_PRINT));
        
        Log::info('Prescription audit log archived', [
            'original_id' => $log->id,
            'archive_path' => $archivePath,
            'prescription_id' => $log->prescription_id,
            'action' => $log->action,
        ]);
    }

    /**
     * Helper methods for calculations and analysis
     */

    private function getNextExpirationDate(string $dataType): ?Carbon
    {
        return match ($dataType) {
            'audit_logs' => AuditLog::requireingRetention()
                ->selectRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) as expiry_date')
                ->orderBy('expiry_date')
                ->first()?->expiry_date,
            'compliance_reports' => ComplianceReport::where('requires_retention', true)
                ->orderBy('retention_expiry_date')
                ->first()?->retention_expiry_date,
            'prescription_audit_logs' => PrescriptionAuditLog::requireingRetention()
                ->selectRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) as expiry_date')
                ->orderBy('expiry_date')
                ->first()?->expiry_date,
            default => null,
        };
    }

    private function getExpiredPrescriptions(): \Illuminate\Database\Eloquent\Collection
    {
        $retentionYears = self::RETENTION_POLICIES['prescription_records']['years'];
        return Prescription::whereRaw('DATE_ADD(created_at, INTERVAL ? YEAR) < ?', [$retentionYears, now()])->get();
    }

    private function getExpiredReportsByFramework(): array
    {
        return ComplianceReport::expiredRetention()
            ->groupBy('regulatory_framework')
            ->selectRaw('regulatory_framework, COUNT(*) as count')
            ->pluck('count', 'regulatory_framework')
            ->toArray();
    }

    private function getExpirationsByMonth(): array
    {
        $expirations = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = now()->addMonths($i);
            $expirations[$month->format('Y-m')] = [
                'audit_logs' => AuditLog::requireingRetention()
                    ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) BETWEEN ? AND ?', [
                        $month->startOfMonth(),
                        $month->endOfMonth()
                    ])->count(),
                'compliance_reports' => ComplianceReport::where('requires_retention', true)
                    ->whereBetween('retention_expiry_date', [
                        $month->startOfMonth(),
                        $month->endOfMonth()
                    ])->count(),
            ];
        }
        return $expirations;
    }

    private function calculateTotalStorage(): float
    {
        return round(
            ($this->calculateAuditLogStorage() + 
             $this->calculateComplianceReportStorage() + 
             $this->calculatePrescriptionStorage() + 
             $this->calculatePrescriptionAuditLogStorage()) / 1024, 
            2
        );
    }

    private function estimateCleanupStorage(): float
    {
        // Estimate storage that could be recovered from cleanup
        $expiredAuditLogs = AuditLog::expiredRetention()->count();
        $expiredReports = ComplianceReport::expiredRetention()->count();
        $avgAuditLogSize = 2; // KB
        $avgReportSize = 100; // KB
        
        return round((($expiredAuditLogs * $avgAuditLogSize) + ($expiredReports * $avgReportSize)) / 1024, 2);
    }

    private function calculateAuditLogStorage(): float
    {
        // Estimate based on average record size
        $count = AuditLog::count();
        $avgSizeKB = 2; // Estimated average size per audit log in KB
        return $count * $avgSizeKB;
    }

    private function calculateComplianceReportStorage(): float
    {
        $totalSize = ComplianceReport::whereNotNull('file_size')->sum('file_size');
        return round($totalSize / 1024, 2); // Convert to MB
    }

    private function calculatePrescriptionStorage(): float
    {
        $count = Prescription::count();
        $avgSizeKB = 5; // Estimated average size per prescription in KB
        return $count * $avgSizeKB;
    }

    private function calculatePrescriptionAuditLogStorage(): float
    {
        $count = PrescriptionAuditLog::count();
        $avgSizeKB = 3; // Estimated average size per prescription audit log in KB
        return $count * $avgSizeKB;
    }

    private function calculateUserRecordStorage(): float
    {
        $count = User::withTrashed()->count();
        $avgSizeKB = 1; // Estimated average size per user record in KB
        return $count * $avgSizeKB;
    }

    private function calculateDatabaseSize(): float
    {
        // This would require database-specific queries to get actual size
        // For now, return an estimate
        return round($this->calculateTotalStorage() * 1.5, 2); // Include indexes and overhead
    }

    private function calculateFileStorage(): float
    {
        // Calculate total file storage used
        $totalBytes = 0;
        $directories = ['compliance-reports', 'archives', 'logs'];
        
        foreach ($directories as $directory) {
            if (Storage::disk('local')->exists($directory)) {
                $files = Storage::disk('local')->allFiles($directory);
                foreach ($files as $file) {
                    $totalBytes += Storage::disk('local')->size($file);
                }
            }
        }
        
        return round($totalBytes / (1024 * 1024 * 1024), 2); // Convert to GB
    }

    private function calculateArchiveStorage(): float
    {
        $totalBytes = 0;
        if (Storage::disk('local')->exists('archives')) {
            $files = Storage::disk('local')->allFiles('archives');
            foreach ($files as $file) {
                $totalBytes += Storage::disk('local')->size($file);
            }
        }
        return round($totalBytes / (1024 * 1024 * 1024), 2); // Convert to GB
    }

    private function calculateGrowthRate(): float
    {
        // Calculate average monthly growth based on recent data
        $currentMonth = AuditLog::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $lastMonth = AuditLog::whereBetween('created_at', [
            now()->subMonth()->startOfMonth(), 
            now()->subMonth()->endOfMonth()
        ])->count();
        
        $avgRecordsPerMonth = ($currentMonth + $lastMonth) / 2;
        $avgSizeKB = 2; // Average audit log size
        
        return round(($avgRecordsPerMonth * $avgSizeKB) / 1024, 2); // MB per month
    }

    private function projectStorageGrowth(int $months): float
    {
        $monthlyGrowth = $this->calculateGrowthRate();
        $currentStorage = $this->calculateTotalStorage();
        return round($currentStorage + ($monthlyGrowth * $months), 2);
    }

    private function identifyComplianceIssues(): array
    {
        $issues = [];

        $expiredAuditLogs = AuditLog::expiredRetention()->count();
        if ($expiredAuditLogs > 0) {
            $issues[] = [
                'type' => 'expired_audit_logs',
                'severity' => 'medium',
                'description' => "{$expiredAuditLogs} audit logs have exceeded retention period",
                'action_required' => 'Archive or delete expired audit logs',
            ];
        }

        $expiredReports = ComplianceReport::expiredRetention()->count();
        if ($expiredReports > 0) {
            $issues[] = [
                'type' => 'expired_compliance_reports',
                'severity' => 'low',
                'description' => "{$expiredReports} compliance reports have exceeded retention period",
                'action_required' => 'Archive expired compliance reports',
            ];
        }

        return $issues;
    }

    private function getLastCleanupDate(): ?Carbon
    {
        // This would typically be stored in a system settings table
        // For now, check for recent archive files
        $archiveFiles = Storage::disk('local')->files('archives');
        if (empty($archiveFiles)) {
            return null;
        }
        
        $latestFile = collect($archiveFiles)->map(function ($file) {
            return Storage::disk('local')->lastModified($file);
        })->max();
        
        return $latestFile ? Carbon::createFromTimestamp($latestFile) : null;
    }

    private function getNextRecommendedCleanup(): Carbon
    {
        $lastCleanup = $this->getLastCleanupDate();
        $baseDate = $lastCleanup ?: now()->subMonths(3);
        return $baseDate->addMonths(3); // Recommend quarterly cleanup
    }

    private function identifyRetentionViolations(): array
    {
        $violations = [];

        // Check for records that should have been deleted but weren't
        $criticallyExpired = AuditLog::expiredRetention()
            ->whereRaw('DATE_ADD(created_at, INTERVAL retention_years YEAR) < ?', [now()->subYear()])
            ->count();

        if ($criticallyExpired > 0) {
            $violations[] = [
                'type' => 'critically_expired_data',
                'severity' => 'high',
                'description' => "{$criticallyExpired} records are more than 1 year past retention expiry",
                'regulation_risk' => 'High - potential regulatory compliance violation',
            ];
        }

        return $violations;
    }

    private function calculateStorageRecovered(array $results): float
    {
        $avgAuditLogSize = 2; // KB
        $avgReportSize = 100; // KB
        
        $recoveredKB = ($results['audit_logs']['deleted'] * $avgAuditLogSize) +
                      ($results['compliance_reports']['archived'] * $avgReportSize);
        
        return round($recoveredKB / 1024, 2); // Convert to MB
    }

    private function logRetentionCleanup(array $results): void
    {
        Log::info('Data retention cleanup completed', $results);
        
        // Also create an audit log entry
        AuditLog::logEvent(
            AuditLog::EVENT_SYSTEM_UPDATE,
            'DataRetentionService',
            null,
            [
                'description' => 'Data retention cleanup executed',
                'metadata' => $results,
                'risk_level' => AuditLog::RISK_MEDIUM,
            ]
        );
    }
}