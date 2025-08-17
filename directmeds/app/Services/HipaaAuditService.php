<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Prescription;
use App\Models\ComplianceReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class HipaaAuditService
{
    /**
     * Generate HIPAA audit summary for a given period.
     */
    public function generateAuditSummary(Carbon $startDate, Carbon $endDate): array
    {
        $auditLogs = AuditLog::phiAccess()
            ->withinDateRange($startDate, $endDate)
            ->with('user')
            ->get();

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'overview' => $this->generateOverviewMetrics($auditLogs),
            'access_patterns' => $this->analyzeAccessPatterns($auditLogs),
            'user_activity' => $this->analyzeUserActivity($auditLogs),
            'risk_analysis' => $this->analyzeRiskMetrics($auditLogs),
            'compliance_violations' => $this->identifyComplianceViolations($auditLogs),
            'recommendations' => $this->generateRecommendations($auditLogs),
        ];
    }

    /**
     * Generate overview metrics.
     */
    private function generateOverviewMetrics(Collection $auditLogs): array
    {
        return [
            'total_phi_access_events' => $auditLogs->count(),
            'unique_users_accessing_phi' => $auditLogs->pluck('user_id')->unique()->count(),
            'unique_patients_accessed' => $auditLogs->where('entity_type', 'User')
                ->whereIn('event_type', [AuditLog::EVENT_PATIENT_PROFILE_ACCESSED])
                ->pluck('entity_id')->unique()->count(),
            'prescription_access_events' => $auditLogs->where('entity_type', 'Prescription')->count(),
            'failed_access_attempts' => $auditLogs->where('access_granted', false)->count(),
            'high_risk_events' => $auditLogs->where('risk_level', AuditLog::RISK_HIGH)->count(),
            'after_hours_access' => $auditLogs->filter(function ($log) {
                $hour = $log->created_at->hour;
                return $hour < 7 || $hour > 19; // Before 7 AM or after 7 PM
            })->count(),
            'weekend_access' => $auditLogs->filter(function ($log) {
                return $log->created_at->isWeekend();
            })->count(),
        ];
    }

    /**
     * Analyze access patterns.
     */
    private function analyzeAccessPatterns(Collection $auditLogs): array
    {
        $byHour = $auditLogs->groupBy(function ($log) {
            return $log->created_at->hour;
        })->map->count()->sortKeys();

        $byDayOfWeek = $auditLogs->groupBy(function ($log) {
            return $log->created_at->dayOfWeek;
        })->map->count()->sortKeys();

        $byEventType = $auditLogs->groupBy('event_type')->map->count()->sortByDesc();

        $byEntityType = $auditLogs->groupBy('entity_type')->map->count()->sortByDesc();

        return [
            'hourly_distribution' => $byHour->toArray(),
            'daily_distribution' => $byDayOfWeek->map(function ($count, $day) {
                return [
                    'day' => Carbon::create()->dayOfWeek($day)->format('l'),
                    'count' => $count
                ];
            })->values()->toArray(),
            'event_type_distribution' => $byEventType->toArray(),
            'entity_type_distribution' => $byEntityType->toArray(),
            'peak_access_hours' => $byHour->sortByDesc()->take(3)->keys()->toArray(),
            'unusual_access_patterns' => $this->identifyUnusualPatterns($auditLogs),
        ];
    }

    /**
     * Analyze user activity.
     */
    private function analyzeUserActivity(Collection $auditLogs): array
    {
        $userActivity = $auditLogs->groupBy('user_id')->map(function ($logs, $userId) {
            $user = $logs->first()->user;
            return [
                'user_id' => $userId,
                'user_name' => $user?->name ?? 'Unknown',
                'user_type' => $user?->user_type ?? 'Unknown',
                'total_access_events' => $logs->count(),
                'unique_patients_accessed' => $logs->where('entity_type', 'User')
                    ->pluck('entity_id')->unique()->count(),
                'prescription_accesses' => $logs->where('entity_type', 'Prescription')->count(),
                'failed_attempts' => $logs->where('access_granted', false)->count(),
                'after_hours_access' => $logs->filter(function ($log) {
                    $hour = $log->created_at->hour;
                    return $hour < 7 || $hour > 19;
                })->count(),
                'weekend_access' => $logs->filter(function ($log) {
                    return $log->created_at->isWeekend();
                })->count(),
                'risk_score' => $this->calculateUserRiskScore($logs),
            ];
        })->sortByDesc('total_access_events');

        return [
            'top_users_by_access' => $userActivity->take(10)->values()->toArray(),
            'users_with_failed_attempts' => $userActivity->where('failed_attempts', '>', 0)
                ->sortByDesc('failed_attempts')->take(5)->values()->toArray(),
            'high_risk_users' => $userActivity->where('risk_score', '>', 7)
                ->sortByDesc('risk_score')->values()->toArray(),
            'unusual_user_activity' => $this->identifyUnusualUserActivity($userActivity),
        ];
    }

    /**
     * Analyze risk metrics.
     */
    private function analyzeRiskMetrics(Collection $auditLogs): array
    {
        $riskDistribution = $auditLogs->groupBy('risk_level')->map->count();
        
        return [
            'risk_level_distribution' => $riskDistribution->toArray(),
            'high_risk_percentage' => round(
                ($riskDistribution->get(AuditLog::RISK_HIGH, 0) / max($auditLogs->count(), 1)) * 100, 
                2
            ),
            'critical_risk_events' => $auditLogs->where('risk_level', AuditLog::RISK_CRITICAL)->count(),
            'failed_access_rate' => round(
                ($auditLogs->where('access_granted', false)->count() / max($auditLogs->count(), 1)) * 100,
                2
            ),
            'bulk_access_events' => $this->identifyBulkAccessEvents($auditLogs),
            'suspicious_ip_addresses' => $this->identifySuspiciousIPs($auditLogs),
        ];
    }

    /**
     * Identify compliance violations.
     */
    private function identifyComplianceViolations(Collection $auditLogs): array
    {
        $violations = [];

        // Check for excessive failed login attempts
        $failedLogins = $auditLogs->where('event_type', AuditLog::EVENT_FAILED_LOGIN)
            ->groupBy('user_id')
            ->filter(function ($logs) {
                return $logs->count() > 5; // More than 5 failed attempts
            });

        if ($failedLogins->count() > 0) {
            $violations[] = [
                'type' => 'excessive_failed_logins',
                'severity' => 'high',
                'description' => 'Users with excessive failed login attempts detected',
                'count' => $failedLogins->count(),
                'details' => $failedLogins->map(function ($logs, $userId) {
                    return [
                        'user_id' => $userId,
                        'failed_attempts' => $logs->count(),
                        'user_name' => $logs->first()->user?->name,
                    ];
                })->values()->toArray(),
            ];
        }

        // Check for after-hours access without justification
        $afterHoursAccess = $auditLogs->filter(function ($log) {
            $hour = $log->created_at->hour;
            return ($hour < 6 || $hour > 22) && !isset($log->metadata['emergency_access']);
        });

        if ($afterHoursAccess->count() > 10) {
            $violations[] = [
                'type' => 'excessive_after_hours_access',
                'severity' => 'medium',
                'description' => 'High volume of after-hours PHI access without emergency justification',
                'count' => $afterHoursAccess->count(),
                'users_involved' => $afterHoursAccess->pluck('user_id')->unique()->count(),
            ];
        }

        // Check for users accessing unusually high number of patient records
        $bulkAccess = $auditLogs->where('entity_type', 'User')
            ->groupBy('user_id')
            ->filter(function ($logs) {
                $uniquePatients = $logs->pluck('entity_id')->unique()->count();
                return $uniquePatients > 50; // More than 50 unique patients per user
            });

        if ($bulkAccess->count() > 0) {
            $violations[] = [
                'type' => 'potential_bulk_phi_access',
                'severity' => 'high',
                'description' => 'Users accessing unusually high number of patient records',
                'count' => $bulkAccess->count(),
                'details' => $bulkAccess->map(function ($logs, $userId) {
                    return [
                        'user_id' => $userId,
                        'unique_patients_accessed' => $logs->pluck('entity_id')->unique()->count(),
                        'user_name' => $logs->first()->user?->name,
                    ];
                })->values()->toArray(),
            ];
        }

        // Check for access from suspicious IP addresses
        $suspiciousIPs = $this->identifySuspiciousIPs($auditLogs);
        if (count($suspiciousIPs) > 0) {
            $violations[] = [
                'type' => 'suspicious_ip_access',
                'severity' => 'critical',
                'description' => 'PHI access from potentially suspicious IP addresses',
                'count' => count($suspiciousIPs),
                'ip_addresses' => $suspiciousIPs,
            ];
        }

        return $violations;
    }

    /**
     * Generate recommendations based on audit analysis.
     */
    private function generateRecommendations(Collection $auditLogs): array
    {
        $recommendations = [];

        // High failed access rate
        $failedRate = ($auditLogs->where('access_granted', false)->count() / max($auditLogs->count(), 1)) * 100;
        if ($failedRate > 5) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'security',
                'title' => 'High Failed Access Rate',
                'description' => "Failed access rate is {$failedRate}%. Consider reviewing user permissions and implementing additional authentication measures.",
                'action_items' => [
                    'Review user role assignments',
                    'Implement stronger password policies',
                    'Consider multi-factor authentication for all users',
                ],
            ];
        }

        // Excessive after-hours access
        $afterHoursCount = $auditLogs->filter(function ($log) {
            $hour = $log->created_at->hour;
            return $hour < 7 || $hour > 19;
        })->count();

        if ($afterHoursCount > ($auditLogs->count() * 0.1)) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'policy',
                'title' => 'High After-Hours Access',
                'description' => 'Significant amount of PHI access occurring outside business hours.',
                'action_items' => [
                    'Review after-hours access policies',
                    'Implement emergency access logging requirements',
                    'Consider additional approval workflows for after-hours access',
                ],
            ];
        }

        // No audit log integrity checks
        $integrityIssues = $auditLogs->filter(function ($log) {
            return !$log->verifyIntegrity();
        })->count();

        if ($integrityIssues > 0) {
            $recommendations[] = [
                'priority' => 'critical',
                'category' => 'integrity',
                'title' => 'Audit Log Integrity Issues',
                'description' => "{$integrityIssues} audit logs failed integrity verification.",
                'action_items' => [
                    'Investigate potential data tampering',
                    'Review audit log storage security',
                    'Implement additional integrity monitoring',
                ],
            ];
        }

        // Users with excessive access
        $excessiveUsers = $auditLogs->groupBy('user_id')
            ->filter(function ($logs) {
                return $logs->count() > 1000; // More than 1000 access events
            })->count();

        if ($excessiveUsers > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'monitoring',
                'title' => 'Users with Excessive PHI Access',
                'description' => "{$excessiveUsers} users have excessive PHI access patterns.",
                'action_items' => [
                    'Review user access patterns for legitimate business need',
                    'Implement access rate limiting',
                    'Conduct user access reviews',
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate risk score for a user based on their access patterns.
     */
    private function calculateUserRiskScore(Collection $userLogs): float
    {
        $score = 0;

        // Base score from access count
        $accessCount = $userLogs->count();
        if ($accessCount > 1000) $score += 3;
        elseif ($accessCount > 500) $score += 2;
        elseif ($accessCount > 100) $score += 1;

        // Failed access attempts
        $failedAttempts = $userLogs->where('access_granted', false)->count();
        $score += min($failedAttempts * 0.5, 3); // Cap at 3 points

        // After-hours access
        $afterHoursAccess = $userLogs->filter(function ($log) {
            $hour = $log->created_at->hour;
            return $hour < 7 || $hour > 19;
        })->count();
        $score += min(($afterHoursAccess / max($accessCount, 1)) * 5, 2); // Cap at 2 points

        // Weekend access
        $weekendAccess = $userLogs->filter(function ($log) {
            return $log->created_at->isWeekend();
        })->count();
        $score += min(($weekendAccess / max($accessCount, 1)) * 3, 1.5); // Cap at 1.5 points

        // Bulk patient access
        $uniquePatients = $userLogs->where('entity_type', 'User')->pluck('entity_id')->unique()->count();
        if ($uniquePatients > 100) $score += 2;
        elseif ($uniquePatients > 50) $score += 1;

        return round($score, 1);
    }

    /**
     * Identify unusual access patterns.
     */
    private function identifyUnusualPatterns(Collection $auditLogs): array
    {
        $patterns = [];

        // Burst access (many accesses in short time)
        $burstEvents = $auditLogs->groupBy(function ($log) {
            return $log->user_id . '_' . $log->created_at->format('Y-m-d H:i');
        })->filter(function ($logs) {
            return $logs->count() > 10; // More than 10 accesses per minute
        });

        if ($burstEvents->count() > 0) {
            $patterns[] = [
                'type' => 'burst_access',
                'description' => 'High-frequency access patterns detected',
                'count' => $burstEvents->count(),
            ];
        }

        // Sequential patient access (accessing patients in order)
        $sequentialAccess = $this->detectSequentialAccess($auditLogs);
        if ($sequentialAccess > 0) {
            $patterns[] = [
                'type' => 'sequential_access',
                'description' => 'Sequential patient record access detected',
                'count' => $sequentialAccess,
            ];
        }

        return $patterns;
    }

    /**
     * Identify unusual user activity.
     */
    private function identifyUnusualUserActivity(Collection $userActivity): array
    {
        $unusual = [];

        foreach ($userActivity as $activity) {
            $flags = [];

            if ($activity['failed_attempts'] > 10) {
                $flags[] = 'excessive_failed_attempts';
            }

            if ($activity['after_hours_access'] > ($activity['total_access_events'] * 0.3)) {
                $flags[] = 'high_after_hours_ratio';
            }

            if ($activity['unique_patients_accessed'] > 100) {
                $flags[] = 'excessive_patient_access';
            }

            if ($activity['risk_score'] > 8) {
                $flags[] = 'high_risk_score';
            }

            if (count($flags) > 0) {
                $unusual[] = array_merge($activity, ['flags' => $flags]);
            }
        }

        return $unusual;
    }

    /**
     * Identify bulk access events.
     */
    private function identifyBulkAccessEvents(Collection $auditLogs): array
    {
        return $auditLogs->groupBy(function ($log) {
            return $log->user_id . '_' . $log->created_at->format('Y-m-d H');
        })->filter(function ($logs) {
            return $logs->count() > 50; // More than 50 accesses per hour
        })->map(function ($logs, $key) {
            [$userId, $hour] = explode('_', $key);
            return [
                'user_id' => $userId,
                'user_name' => $logs->first()->user?->name,
                'hour' => $hour,
                'access_count' => $logs->count(),
                'unique_entities' => $logs->pluck('entity_id')->unique()->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Identify suspicious IP addresses.
     */
    private function identifySuspiciousIPs(Collection $auditLogs): array
    {
        $ipAnalysis = $auditLogs->groupBy('ip_address')->map(function ($logs, $ip) {
            $userCount = $logs->pluck('user_id')->unique()->count();
            $failedAttempts = $logs->where('access_granted', false)->count();
            
            return [
                'ip' => $ip,
                'total_attempts' => $logs->count(),
                'unique_users' => $userCount,
                'failed_attempts' => $failedAttempts,
                'success_rate' => round((($logs->count() - $failedAttempts) / $logs->count()) * 100, 2),
            ];
        });

        // Flag IPs with suspicious patterns
        return $ipAnalysis->filter(function ($data) {
            return $data['failed_attempts'] > 20 || // High failed attempts
                   $data['unique_users'] > 10 || // Too many different users
                   $data['success_rate'] < 50; // Low success rate
        })->values()->toArray();
    }

    /**
     * Detect sequential access patterns.
     */
    private function detectSequentialAccess(Collection $auditLogs): int
    {
        $sequentialCount = 0;
        
        $userAccess = $auditLogs->where('entity_type', 'User')
            ->groupBy('user_id')
            ->map(function ($logs) {
                return $logs->sortBy('created_at')->pluck('entity_id')->toArray();
            });

        foreach ($userAccess as $entityIds) {
            if (count($entityIds) < 10) continue; // Need at least 10 accesses to detect pattern
            
            $sequential = 0;
            for ($i = 1; $i < count($entityIds); $i++) {
                if (is_numeric($entityIds[$i]) && is_numeric($entityIds[$i-1])) {
                    if ($entityIds[$i] == $entityIds[$i-1] + 1) {
                        $sequential++;
                    }
                }
            }
            
            // If more than 70% of accesses are sequential, flag it
            if ($sequential > (count($entityIds) * 0.7)) {
                $sequentialCount++;
            }
        }

        return $sequentialCount;
    }

    /**
     * Generate HIPAA compliance report.
     */
    public function generateComplianceReport(Carbon $startDate, Carbon $endDate): ComplianceReport
    {
        $report = ComplianceReport::createReport(
            ComplianceReport::TYPE_HIPAA_ACCESS,
            "HIPAA PHI Access Report - {$startDate->format('M Y')}",
            $startDate,
            $endDate,
            [
                'regulatory_framework' => ComplianceReport::FRAMEWORK_HIPAA,
                'criticality' => ComplianceReport::CRITICALITY_HIGH,
                'description' => 'Comprehensive HIPAA PHI access audit report',
            ]
        );

        $report->startGeneration();

        try {
            $auditSummary = $this->generateAuditSummary($startDate, $endDate);
            
            $complianceScore = $this->calculateComplianceScore($auditSummary);
            
            $report->completeGeneration([
                'summary_data' => $auditSummary['overview'],
                'detailed_findings' => [
                    'access_patterns' => $auditSummary['access_patterns'],
                    'user_activity' => $auditSummary['user_activity'],
                    'risk_analysis' => $auditSummary['risk_analysis'],
                ],
                'violations' => $auditSummary['compliance_violations'],
                'recommendations' => $auditSummary['recommendations'],
                'compliance_score' => $complianceScore,
                'total_records_analyzed' => AuditLog::phiAccess()
                    ->withinDateRange($startDate, $endDate)->count(),
                'violations_found' => count($auditSummary['compliance_violations']),
            ]);

        } catch (\Exception $e) {
            $report->markAsFailed($e->getMessage());
            throw $e;
        }

        return $report;
    }

    /**
     * Calculate compliance score based on audit findings.
     */
    private function calculateComplianceScore(array $auditSummary): float
    {
        $score = 100;

        // Deduct points for violations
        foreach ($auditSummary['compliance_violations'] as $violation) {
            $deduction = match ($violation['severity']) {
                'critical' => 20,
                'high' => 10,
                'medium' => 5,
                'low' => 2,
                default => 1,
            };
            $score -= $deduction;
        }

        // Deduct points for high failed access rate
        $failedRate = $auditSummary['risk_analysis']['failed_access_rate'] ?? 0;
        if ($failedRate > 10) $score -= 15;
        elseif ($failedRate > 5) $score -= 10;

        // Deduct points for excessive after-hours access
        $afterHours = $auditSummary['overview']['after_hours_access'] ?? 0;
        $total = $auditSummary['overview']['total_phi_access_events'] ?? 1;
        $afterHoursRate = ($afterHours / $total) * 100;
        if ($afterHoursRate > 20) $score -= 10;
        elseif ($afterHoursRate > 10) $score -= 5;

        return max(0, round($score, 2));
    }
}