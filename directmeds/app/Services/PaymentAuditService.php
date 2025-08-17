<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentAuditService
{
    protected string $auditChannel = 'payment_audit';
    protected string $complianceChannel = 'compliance';

    /**
     * Log payment transaction for audit trail
     */
    public function logTransaction(Payment $payment, string $action, array $details = []): void
    {
        $auditData = [
            'transaction_id' => $payment->uuid,
            'payment_number' => $payment->payment_number,
            'user_id' => $payment->user_id,
            'order_id' => $payment->order_id,
            'action' => $action,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'gateway' => $payment->gateway,
            'status' => $payment->status,
            'payment_method_type' => $payment->payment_method_type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'details' => $details,
        ];

        Log::channel($this->auditChannel)->info("Payment {$action}", $auditData);

        // Store audit record in file system for compliance
        $this->storeAuditRecord($auditData);
    }

    /**
     * Log payment method operations
     */
    public function logPaymentMethodOperation(PaymentMethod $paymentMethod, string $action, array $details = []): void
    {
        $auditData = [
            'payment_method_id' => $paymentMethod->uuid,
            'user_id' => $paymentMethod->user_id,
            'action' => $action,
            'type' => $paymentMethod->type,
            'gateway' => $paymentMethod->gateway,
            'card_brand' => $paymentMethod->card_brand,
            'card_last_four' => $paymentMethod->card_last_four,
            'is_default' => $paymentMethod->is_default,
            'is_active' => $paymentMethod->is_active,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'details' => $details,
        ];

        Log::channel($this->auditChannel)->info("Payment method {$action}", $auditData);
        
        // Store audit record
        $this->storeAuditRecord($auditData);
    }

    /**
     * Log compliance events
     */
    public function logComplianceEvent(string $event, array $data): void
    {
        $complianceData = [
            'event' => $event,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        Log::channel($this->complianceChannel)->warning("Compliance event: {$event}", $complianceData);
        
        // Store compliance record with higher security
        $this->storeComplianceRecord($complianceData);
    }

    /**
     * Log PCI compliance checks
     */
    public function logPciComplianceCheck(PaymentMethod $paymentMethod, array $checks): void
    {
        $this->logComplianceEvent('pci_compliance_check', [
            'payment_method_id' => $paymentMethod->uuid,
            'user_id' => $paymentMethod->user_id,
            'checks' => $checks,
            'overall_compliant' => !in_array(false, $checks),
        ]);
    }

    /**
     * Log fraud detection events
     */
    public function logFraudEvent(Payment $payment, string $fraudType, array $details): void
    {
        $fraudData = [
            'payment_id' => $payment->uuid,
            'user_id' => $payment->user_id,
            'fraud_type' => $fraudType,
            'fraud_score' => $payment->fraud_score,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'details' => $details,
        ];

        $this->logComplianceEvent('fraud_detection', $fraudData);

        // Alert on high-risk fraud
        if (($payment->fraud_score ?? 0) > 0.7) {
            $this->alertHighRiskTransaction($payment, $fraudData);
        }
    }

    /**
     * Log security violations
     */
    public function logSecurityViolation(string $violation, array $details): void
    {
        $securityData = [
            'violation' => $violation,
            'severity' => $this->determineSecuritySeverity($violation),
            'details' => $details,
        ];

        $this->logComplianceEvent('security_violation', $securityData);

        // Send immediate alert for critical violations
        if ($securityData['severity'] === 'critical') {
            $this->alertCriticalSecurity($securityData);
        }
    }

    /**
     * Generate audit report for date range
     */
    public function generateAuditReport(\DateTime $startDate, \DateTime $endDate, array $filters = []): array
    {
        $auditRecords = $this->retrieveAuditRecords($startDate, $endDate, $filters);
        
        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
            ],
            'summary' => $this->generateAuditSummary($auditRecords),
            'transactions' => $this->summarizeTransactions($auditRecords),
            'compliance_events' => $this->summarizeComplianceEvents($auditRecords),
            'security_events' => $this->summarizeSecurityEvents($auditRecords),
            'recommendations' => $this->generateRecommendations($auditRecords),
        ];

        // Store report for compliance purposes
        $this->storeAuditReport($report);

        return $report;
    }

    /**
     * Check for suspicious patterns in payment activity
     */
    public function detectSuspiciousPatterns(User $user, int $days = 30): array
    {
        $payments = Payment::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $patterns = [];

        // Check for rapid payment attempts
        $rapidAttempts = $payments->filter(function ($payment) {
            return $payment->created_at->diffInMinutes(now()) < 5;
        });

        if ($rapidAttempts->count() > 5) {
            $patterns[] = [
                'type' => 'rapid_attempts',
                'severity' => 'medium',
                'count' => $rapidAttempts->count(),
                'description' => 'Multiple payment attempts in short time frame',
            ];
        }

        // Check for high failure rate
        $failedPayments = $payments->where('status', Payment::STATUS_FAILED);
        $failureRate = $payments->count() > 0 ? $failedPayments->count() / $payments->count() : 0;

        if ($failureRate > 0.5) {
            $patterns[] = [
                'type' => 'high_failure_rate',
                'severity' => 'high',
                'rate' => $failureRate,
                'description' => 'Unusually high payment failure rate',
            ];
        }

        // Check for unusual amounts
        $avgAmount = $payments->avg('amount');
        $unusualAmounts = $payments->filter(function ($payment) use ($avgAmount) {
            return $payment->amount > ($avgAmount * 10) || $payment->amount < ($avgAmount * 0.1);
        });

        if ($unusualAmounts->count() > 0) {
            $patterns[] = [
                'type' => 'unusual_amounts',
                'severity' => 'low',
                'count' => $unusualAmounts->count(),
                'description' => 'Payments with unusual amounts detected',
            ];
        }

        // Log detected patterns
        if (!empty($patterns)) {
            $this->logComplianceEvent('suspicious_patterns_detected', [
                'user_id' => $user->id,
                'patterns' => $patterns,
                'analysis_period_days' => $days,
            ]);
        }

        return $patterns;
    }

    /**
     * Protected helper methods
     */

    protected function storeAuditRecord(array $auditData): void
    {
        $filename = 'audit/payments/' . now()->format('Y/m/d') . '/audit-' . now()->format('H') . '.json';
        
        $existingData = [];
        if (Storage::disk('local')->exists($filename)) {
            $existingData = json_decode(Storage::disk('local')->get($filename), true) ?? [];
        }

        $existingData[] = $auditData;
        
        Storage::disk('local')->put($filename, json_encode($existingData, JSON_PRETTY_PRINT));
    }

    protected function storeComplianceRecord(array $complianceData): void
    {
        $filename = 'compliance/payments/' . now()->format('Y/m/d') . '/compliance-' . now()->format('H') . '.json';
        
        $existingData = [];
        if (Storage::disk('local')->exists($filename)) {
            $existingData = json_decode(Storage::disk('local')->get($filename), true) ?? [];
        }

        $existingData[] = $complianceData;
        
        Storage::disk('local')->put($filename, json_encode($existingData, JSON_PRETTY_PRINT));
    }

    protected function storeAuditReport(array $report): void
    {
        $filename = 'reports/audit/' . now()->format('Y-m-d_H-i-s') . '_audit_report.json';
        Storage::disk('local')->put($filename, json_encode($report, JSON_PRETTY_PRINT));
    }

    protected function retrieveAuditRecords(\DateTime $startDate, \DateTime $endDate, array $filters = []): array
    {
        // This would implement retrieval from stored audit files
        // For now, return empty array as placeholder
        return [];
    }

    protected function generateAuditSummary(array $auditRecords): array
    {
        return [
            'total_transactions' => count($auditRecords),
            'unique_users' => count(array_unique(array_column($auditRecords, 'user_id'))),
            'total_amount' => array_sum(array_column($auditRecords, 'amount')),
            'status_breakdown' => array_count_values(array_column($auditRecords, 'status')),
            'gateway_breakdown' => array_count_values(array_column($auditRecords, 'gateway')),
        ];
    }

    protected function summarizeTransactions(array $auditRecords): array
    {
        $transactions = array_filter($auditRecords, function ($record) {
            return strpos($record['action'], 'payment_') === 0;
        });

        return [
            'count' => count($transactions),
            'actions' => array_count_values(array_column($transactions, 'action')),
        ];
    }

    protected function summarizeComplianceEvents(array $auditRecords): array
    {
        $complianceEvents = array_filter($auditRecords, function ($record) {
            return isset($record['event']) && strpos($record['event'], 'compliance') !== false;
        });

        return [
            'count' => count($complianceEvents),
            'events' => array_count_values(array_column($complianceEvents, 'event')),
        ];
    }

    protected function summarizeSecurityEvents(array $auditRecords): array
    {
        $securityEvents = array_filter($auditRecords, function ($record) {
            return isset($record['event']) && strpos($record['event'], 'security') !== false;
        });

        return [
            'count' => count($securityEvents),
            'violations' => array_count_values(array_column($securityEvents, 'violation')),
        ];
    }

    protected function generateRecommendations(array $auditRecords): array
    {
        $recommendations = [];

        // Analyze patterns and generate recommendations
        $failedPayments = array_filter($auditRecords, function ($record) {
            return isset($record['status']) && $record['status'] === Payment::STATUS_FAILED;
        });

        if (count($failedPayments) > (count($auditRecords) * 0.1)) {
            $recommendations[] = [
                'type' => 'high_failure_rate',
                'priority' => 'high',
                'description' => 'High payment failure rate detected. Review payment gateway configuration and fraud settings.',
            ];
        }

        return $recommendations;
    }

    protected function determineSecuritySeverity(string $violation): string
    {
        $criticalViolations = [
            'sql_injection_attempt',
            'xss_attempt',
            'brute_force_attack',
            'unauthorized_access',
        ];

        $highViolations = [
            'suspicious_ip',
            'bot_activity',
            'rate_limit_exceeded',
        ];

        if (in_array($violation, $criticalViolations)) {
            return 'critical';
        } elseif (in_array($violation, $highViolations)) {
            return 'high';
        } else {
            return 'medium';
        }
    }

    protected function alertHighRiskTransaction(Payment $payment, array $fraudData): void
    {
        Log::channel('alerts')->critical('High risk payment transaction detected', [
            'payment_id' => $payment->uuid,
            'fraud_score' => $payment->fraud_score,
            'fraud_data' => $fraudData,
        ]);

        // In production, you would send alerts via email, Slack, etc.
    }

    protected function alertCriticalSecurity(array $securityData): void
    {
        Log::channel('alerts')->emergency('Critical security violation detected', $securityData);

        // In production, you would send immediate alerts to security team
    }
}