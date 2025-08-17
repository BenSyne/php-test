<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ComplianceReport;
use App\Services\HipaaAuditService;
use App\Services\DeaReportingService;
use App\Services\DataRetentionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ComplianceController extends Controller
{
    protected HipaaAuditService $hipaaService;
    protected DeaReportingService $deaService;
    protected DataRetentionService $retentionService;

    public function __construct(
        HipaaAuditService $hipaaService,
        DeaReportingService $deaService,
        DataRetentionService $retentionService
    ) {
        $this->hipaaService = $hipaaService;
        $this->deaService = $deaService;
        $this->retentionService = $retentionService;
        
        // Require admin or compliance officer role
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['admin', 'compliance_officer', 'pharmacist_manager'])) {
                abort(403, 'Insufficient permissions for compliance access');
            }
            return $next($request);
        });
    }

    /**
     * Display the compliance dashboard.
     */
    public function dashboard(): View
    {
        // Log dashboard access
        AuditLog::logEvent(
            AuditLog::EVENT_ACCESSED,
            'ComplianceDashboard',
            null,
            [
                'description' => 'Compliance dashboard accessed',
                'is_phi_access' => true,
                'risk_level' => AuditLog::RISK_HIGH,
            ]
        );

        $metrics = [
            'phi_access_count' => AuditLog::phiAccess()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count(),
            'controlled_substance_count' => AuditLog::controlledSubstance()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count(),
            'failed_access_count' => AuditLog::failedAccess()
                ->whereBetween('created_at', [now()->startOfDay(), now()])
                ->count(),
            'pending_reports' => ComplianceReport::byStatus(ComplianceReport::STATUS_PENDING)->count(),
            'overdue_reports' => ComplianceReport::overdue()->count(),
            'reports_requiring_review' => ComplianceReport::requiringReview()->count(),
        ];

        $recentReports = ComplianceReport::with(['generator', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentAuditLogs = AuditLog::with('user')
            ->where(function ($query) {
                $query->where('is_phi_access', true)
                      ->orWhere('is_controlled_substance', true)
                      ->orWhere('risk_level', AuditLog::RISK_HIGH);
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('compliance.dashboard', compact('metrics', 'recentReports', 'recentAuditLogs'));
    }

    /**
     * Display audit logs with filtering.
     */
    public function auditLogs(Request $request): View
    {
        $query = AuditLog::with('user');

        // Apply filters
        if ($request->filled('event_type')) {
            $query->eventType($request->event_type);
        }

        if ($request->filled('entity_type')) {
            $query->entityType($request->entity_type);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('risk_level')) {
            $query->riskLevel($request->risk_level);
        }

        if ($request->filled('data_classification')) {
            $query->dataClassification($request->data_classification);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->boolean('phi_only')) {
            $query->phiAccess();
        }

        if ($request->boolean('controlled_substances_only')) {
            $query->controlledSubstance();
        }

        if ($request->boolean('failed_access_only')) {
            $query->failedAccess();
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(50);

        // Log audit log access
        AuditLog::logEvent(
            AuditLog::EVENT_ACCESSED,
            'AuditLogs',
            null,
            [
                'description' => 'Audit logs accessed with filters',
                'metadata' => $request->only([
                    'event_type', 'entity_type', 'user_id', 'risk_level', 
                    'data_classification', 'date_from', 'date_to',
                    'phi_only', 'controlled_substances_only', 'failed_access_only'
                ]),
                'is_phi_access' => true,
                'risk_level' => AuditLog::RISK_HIGH,
            ]
        );

        return view('compliance.audit-logs', compact('auditLogs'));
    }

    /**
     * Display compliance reports.
     */
    public function reports(Request $request): View
    {
        $query = ComplianceReport::with(['generator', 'reviewer']);

        // Apply filters
        if ($request->filled('report_type')) {
            $query->byType($request->report_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('regulatory_framework')) {
            $query->byFramework($request->regulatory_framework);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('compliance.reports', compact('reports'));
    }

    /**
     * Generate a new compliance report.
     */
    public function generateReport(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => [
                'required',
                Rule::in([
                    ComplianceReport::TYPE_HIPAA_ACCESS,
                    ComplianceReport::TYPE_HIPAA_SECURITY,
                    ComplianceReport::TYPE_DEA_CONTROLLED_SUBSTANCES,
                    ComplianceReport::TYPE_DEA_INVENTORY,
                    ComplianceReport::TYPE_PCI_COMPLIANCE,
                    ComplianceReport::TYPE_AUDIT_TRAIL,
                    ComplianceReport::TYPE_DATA_RETENTION,
                    ComplianceReport::TYPE_USER_ACCESS,
                    ComplianceReport::TYPE_SECURITY_INCIDENTS,
                    ComplianceReport::TYPE_PRESCRIPTION_MONITORING,
                    ComplianceReport::TYPE_FAILED_LOGINS,
                    ComplianceReport::TYPE_DATA_EXPORTS,
                ])
            ],
            'report_name' => 'required|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'parameters' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        try {
            $report = ComplianceReport::createReport(
                $request->report_type,
                $request->report_name,
                Carbon::parse($request->period_start),
                Carbon::parse($request->period_end),
                [
                    'parameters' => $request->parameters ?? [],
                    'filters' => $request->filters ?? [],
                    'description' => $request->description,
                ]
            );

            // Queue report generation job
            \Illuminate\Support\Facades\Queue::push(new \App\Jobs\GenerateComplianceReport($report));

            // Log report generation request
            AuditLog::logEvent(
                AuditLog::EVENT_CREATED,
                'ComplianceReport',
                $report->id,
                [
                    'description' => "Compliance report generation requested: {$report->report_name}",
                    'metadata' => [
                        'report_type' => $report->report_type,
                        'period_start' => $report->period_start->toDateString(),
                        'period_end' => $report->period_end->toDateString(),
                    ],
                    'is_phi_access' => true,
                    'risk_level' => AuditLog::RISK_HIGH,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Report generation started successfully',
                'report' => $report,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a compliance report.
     */
    public function downloadReport(ComplianceReport $report): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        if ($report->status !== ComplianceReport::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Report is not ready for download',
            ], 400);
        }

        if (!$report->file_path || !\Illuminate\Support\Facades\Storage::exists($report->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Report file not found',
            ], 404);
        }

        // Verify file integrity
        if (!$report->verifyFileIntegrity()) {
            return response()->json([
                'success' => false,
                'message' => 'Report file integrity check failed',
            ], 500);
        }

        // Log report download
        AuditLog::logEvent(
            AuditLog::EVENT_ACCESSED,
            'ComplianceReport',
            $report->id,
            [
                'description' => "Compliance report downloaded: {$report->report_name}",
                'metadata' => [
                    'report_identifier' => $report->report_identifier,
                    'file_format' => $report->file_format,
                    'file_size' => $report->file_size,
                ],
                'is_phi_access' => true,
                'risk_level' => AuditLog::RISK_HIGH,
            ]
        );

        $filename = "{$report->report_identifier}.{$report->file_format}";
        
        return \Illuminate\Support\Facades\Storage::download($report->file_path, $filename);
    }

    /**
     * Get HIPAA audit summary.
     */
    public function hipaaAuditSummary(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $startDate = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $endDate = $request->date_to ? Carbon::parse($request->date_to) : now();

        $summary = $this->hipaaService->generateAuditSummary($startDate, $endDate);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Get DEA reporting summary.
     */
    public function deaReportingSummary(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $startDate = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $endDate = $request->date_to ? Carbon::parse($request->date_to) : now();

        $summary = $this->deaService->generateControlledSubstanceReport($startDate, $endDate);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Get data retention summary.
     */
    public function dataRetentionSummary(): JsonResponse
    {
        $summary = $this->retentionService->getRetentionSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Execute data retention cleanup.
     */
    public function executeRetentionCleanup(Request $request): JsonResponse
    {
        $request->validate([
            'confirm' => 'required|boolean|accepted',
            'dry_run' => 'nullable|boolean',
        ]);

        try {
            $result = $this->retentionService->executeRetentionPolicy($request->boolean('dry_run', false));

            // Log retention cleanup execution
            AuditLog::logEvent(
                AuditLog::EVENT_SYSTEM_UPDATE,
                'DataRetention',
                null,
                [
                    'description' => 'Data retention cleanup executed',
                    'metadata' => [
                        'dry_run' => $request->boolean('dry_run', false),
                        'records_processed' => $result['processed'],
                        'records_archived' => $result['archived'],
                        'records_deleted' => $result['deleted'],
                    ],
                    'risk_level' => AuditLog::RISK_HIGH,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Retention cleanup executed successfully',
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Retention cleanup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Review and approve a compliance report.
     */
    public function reviewReport(Request $request, ComplianceReport $report): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            if ($request->action === 'approve') {
                $report->approve($request->notes);
                $message = 'Report approved successfully';
            } else {
                $report->reject($request->notes ?: 'No reason provided');
                $message = 'Report rejected successfully';
            }

            // Log report review
            AuditLog::logEvent(
                AuditLog::EVENT_UPDATED,
                'ComplianceReport',
                $report->id,
                [
                    'description' => "Compliance report {$request->action}d: {$report->report_name}",
                    'metadata' => [
                        'action' => $request->action,
                        'notes' => $request->notes,
                        'reviewer_id' => auth()->id(),
                    ],
                    'is_phi_access' => true,
                    'risk_level' => AuditLog::RISK_MEDIUM,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'report' => $report->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review action failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get compliance metrics for dashboard widgets.
     */
    public function metrics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // day, week, month, quarter, year

        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $metrics = [
            'audit_metrics' => [
                'total_events' => AuditLog::whereBetween('created_at', [$startDate, now()])->count(),
                'phi_access_events' => AuditLog::phiAccess()->whereBetween('created_at', [$startDate, now()])->count(),
                'controlled_substance_events' => AuditLog::controlledSubstance()->whereBetween('created_at', [$startDate, now()])->count(),
                'failed_access_events' => AuditLog::failedAccess()->whereBetween('created_at', [$startDate, now()])->count(),
                'high_risk_events' => AuditLog::riskLevel(AuditLog::RISK_HIGH)->whereBetween('created_at', [$startDate, now()])->count(),
            ],
            'compliance_reports' => [
                'total_reports' => ComplianceReport::whereBetween('created_at', [$startDate, now()])->count(),
                'completed_reports' => ComplianceReport::byStatus(ComplianceReport::STATUS_COMPLETED)->whereBetween('created_at', [$startDate, now()])->count(),
                'pending_reports' => ComplianceReport::byStatus(ComplianceReport::STATUS_PENDING)->count(),
                'overdue_reports' => ComplianceReport::overdue()->count(),
                'reports_requiring_review' => ComplianceReport::requiringReview()->count(),
            ],
            'retention_metrics' => $this->retentionService->getRetentionSummary(),
        ];

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
        ]);
    }
}