<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compliance Dashboard - DirectMeds</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Compliance Dashboard</h1>
                        <span class="ml-2 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                            HIPAA Compliant
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            Last Updated: {{ now()->format('M d, Y H:i') }}
                        </div>
                        <button onclick="refreshDashboard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">PHI Access Events</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['phi_access_count']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <span class="text-gray-600">This Month</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Controlled Substances</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['controlled_substance_count']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <span class="text-gray-600">This Month</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 {{ $metrics['failed_access_count'] > 10 ? 'bg-red-500' : 'bg-green-500' }} rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Failed Access Attempts</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['failed_access_count']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <span class="text-gray-600">Today</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 {{ $metrics['overdue_reports'] > 0 ? 'bg-red-500' : 'bg-blue-500' }} rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Reports</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['pending_reports']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <span class="{{ $metrics['overdue_reports'] > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                {{ $metrics['overdue_reports'] }} Overdue
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Audit Activity -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Audit Activity</h3>
                            <p class="text-sm text-gray-500">High-priority compliance events</p>
                        </div>
                        <div class="overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                @forelse($recentAuditLogs as $log)
                                <div class="px-6 py-4 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                @if($log->is_phi_access)
                                                    <span class="inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                                @elseif($log->is_controlled_substance)
                                                    <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full"></span>
                                                @else
                                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $log->description ?: $log->event_type }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $log->user?->name ?: 'System' }} • 
                                                    {{ $log->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($log->risk_level === 'high' || $log->risk_level === 'critical')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ ucfirst($log->risk_level) }} Risk
                                                </span>
                                            @endif
                                            @if($log->is_phi_access)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    PHI
                                                </span>
                                            @endif
                                            @if($log->is_controlled_substance)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    DEA
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-6 py-4 text-center text-gray-500">
                                    No recent audit activity
                                </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <a href="{{ route('compliance.audit-logs') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View all audit logs →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Compliance Reports -->
                <div>
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Reports</h3>
                            <p class="text-sm text-gray-500">Compliance reports</p>
                        </div>
                        <div class="overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                @forelse($recentReports as $report)
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $report->report_name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ ucfirst(str_replace('_', ' ', $report->report_type)) }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ $report->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($report->status === 'completed')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Complete
                                                </span>
                                            @elseif($report->status === 'generating')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Generating
                                                </span>
                                            @elseif($report->status === 'failed')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Failed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-6 py-4 text-center text-gray-500">
                                    No recent reports
                                </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex justify-between">
                                <a href="{{ route('compliance.reports') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View all reports →
                                </a>
                                <button onclick="showGenerateReportModal()" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance Status Overview -->
            <div class="mt-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Compliance Status Overview</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- HIPAA Compliance -->
                            <div class="text-center">
                                <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">HIPAA</h4>
                                <p class="text-sm text-gray-500 mb-2">Privacy & Security</p>
                                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Compliant
                                </div>
                            </div>

                            <!-- DEA Compliance -->
                            <div class="text-center">
                                <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">DEA</h4>
                                <p class="text-sm text-gray-500 mb-2">Controlled Substances</p>
                                <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Monitoring
                                </div>
                            </div>

                            <!-- Data Retention -->
                            <div class="text-center">
                                <div class="mx-auto w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">Data Retention</h4>
                                <p class="text-sm text-gray-500 mb-2">Archive & Cleanup</p>
                                <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Active
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <button onclick="generateHipaaReport()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                HIPAA Report
                            </button>
                            <button onclick="generateDeaReport()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                DEA Report
                            </button>
                            <button onclick="viewAuditLogs()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Audit Logs
                            </button>
                            <button onclick="runRetentionCleanup()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Data Cleanup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center">Generate Compliance Report</h3>
                <form id="generateReportForm" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Type</label>
                        <select name="report_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="hipaa_access">HIPAA Access Report</option>
                            <option value="dea_controlled_substances">DEA Controlled Substances</option>
                            <option value="audit_trail">Audit Trail Report</option>
                            <option value="data_retention">Data Retention Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Name</label>
                        <input type="text" name="report_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Monthly HIPAA Audit">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="period_start" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="period_end" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="hideGenerateReportModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dashboard JavaScript functionality
        function refreshDashboard() {
            location.reload();
        }

        function showGenerateReportModal() {
            document.getElementById('generateReportModal').classList.remove('hidden');
        }

        function hideGenerateReportModal() {
            document.getElementById('generateReportModal').classList.add('hidden');
        }

        function generateHipaaReport() {
            // Auto-fill form for HIPAA report
            showGenerateReportModal();
            document.querySelector('[name="report_type"]').value = 'hipaa_access';
            document.querySelector('[name="report_name"]').value = 'Monthly HIPAA Access Report';
            
            const now = new Date();
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            document.querySelector('[name="period_start"]').value = startOfMonth.toISOString().split('T')[0];
            document.querySelector('[name="period_end"]').value = now.toISOString().split('T')[0];
        }

        function generateDeaReport() {
            showGenerateReportModal();
            document.querySelector('[name="report_type"]').value = 'dea_controlled_substances';
            document.querySelector('[name="report_name"]').value = 'Monthly DEA Controlled Substances Report';
            
            const now = new Date();
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            document.querySelector('[name="period_start"]').value = startOfMonth.toISOString().split('T')[0];
            document.querySelector('[name="period_end"]').value = now.toISOString().split('T')[0];
        }

        function viewAuditLogs() {
            window.location.href = '{{ route("compliance.audit-logs") }}';
        }

        function runRetentionCleanup() {
            if (confirm('Are you sure you want to run data retention cleanup? This will archive/delete expired records.')) {
                fetch('{{ route("compliance.retention.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        confirm: true,
                        dry_run: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Data retention cleanup completed successfully');
                        location.reload();
                    } else {
                        alert('Cleanup failed: ' + data.message);
                    }
                });
            }
        }

        // Handle generate report form submission
        document.getElementById('generateReportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('{{ route("compliance.generate-report") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Report generation started successfully');
                    hideGenerateReportModal();
                    location.reload();
                } else {
                    alert('Failed to generate report: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while generating the report');
            });
        });

        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>