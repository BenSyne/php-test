<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compliance Reports - DirectMeds</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <a href="{{ route('compliance.dashboard') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Compliance Reports</h1>
                        <span class="ml-2 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                            Regulatory Reporting
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="showGenerateReportModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Generate New Report
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Reports</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $reports->where('status', 'completed')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $reports->whereIn('status', ['pending', 'generating'])->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Review</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $reports->where('review_status', 'pending')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Failed</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $reports->where('status', 'failed')->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Filter Reports</h3>
                </div>
                <form method="GET" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Report Type</label>
                            <select name="report_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                <option value="hipaa_access" {{ request('report_type') === 'hipaa_access' ? 'selected' : '' }}>HIPAA Access</option>
                                <option value="hipaa_security" {{ request('report_type') === 'hipaa_security' ? 'selected' : '' }}>HIPAA Security</option>
                                <option value="dea_controlled_substances" {{ request('report_type') === 'dea_controlled_substances' ? 'selected' : '' }}>DEA Controlled Substances</option>
                                <option value="dea_inventory" {{ request('report_type') === 'dea_inventory' ? 'selected' : '' }}>DEA Inventory</option>
                                <option value="pci_compliance" {{ request('report_type') === 'pci_compliance' ? 'selected' : '' }}>PCI Compliance</option>
                                <option value="audit_trail" {{ request('report_type') === 'audit_trail' ? 'selected' : '' }}>Audit Trail</option>
                                <option value="data_retention" {{ request('report_type') === 'data_retention' ? 'selected' : '' }}>Data Retention</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="generating" {{ request('status') === 'generating' ? 'selected' : '' }}>Generating</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Regulatory Framework</label>
                            <select name="regulatory_framework" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Frameworks</option>
                                <option value="HIPAA" {{ request('regulatory_framework') === 'HIPAA' ? 'selected' : '' }}>HIPAA</option>
                                <option value="DEA" {{ request('regulatory_framework') === 'DEA' ? 'selected' : '' }}>DEA</option>
                                <option value="PCI-DSS" {{ request('regulatory_framework') === 'PCI-DSS' ? 'selected' : '' }}>PCI-DSS</option>
                                <option value="Internal" {{ request('regulatory_framework') === 'Internal' ? 'selected' : '' }}>Internal</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <a href="{{ route('compliance.reports') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Clear Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($reports as $report)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-green-100 text-green-800',
                                        'generating' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'archived' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusColor = $statusColors[$report->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                                @if($report->criticality === 'critical')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Critical
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-1">
                                @if($report->status === 'completed')
                                    <button onclick="downloadReport('{{ $report->id }}')" class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                @endif
                                <button onclick="showReportDetails('{{ $report->id }}')" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $report->report_name }}</h3>
                        
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Type:</span>
                                <span class="font-medium">{{ ucwords(str_replace('_', ' ', $report->report_type)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Framework:</span>
                                <span class="font-medium">{{ $report->regulatory_framework }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Period:</span>
                                <span class="font-medium">
                                    {{ $report->period_start->format('M d') }} - {{ $report->period_end->format('M d, Y') }}
                                </span>
                            </div>
                            @if($report->compliance_score)
                                <div class="flex justify-between">
                                    <span>Compliance Score:</span>
                                    <span class="font-medium {{ $report->compliance_score >= 95 ? 'text-green-600' : ($report->compliance_score >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($report->compliance_score, 1) }}%
                                    </span>
                                </div>
                            @endif
                            @if($report->violations_found !== null)
                                <div class="flex justify-between">
                                    <span>Violations:</span>
                                    <span class="font-medium {{ $report->violations_found > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $report->violations_found }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center text-xs text-gray-500">
                                <span>Generated by {{ $report->generator?->name ?: 'System' }}</span>
                                <span>{{ $report->created_at->diffForHumans() }}</span>
                            </div>
                            @if($report->generation_time_seconds)
                                <div class="text-xs text-gray-500 mt-1">
                                    Generated in {{ $report->generation_time_seconds }}s
                                </div>
                            @endif
                        </div>

                        @if($report->review_status === 'pending' && $report->status === 'completed')
                            <div class="mt-4 flex space-x-2">
                                <button onclick="reviewReport('{{ $report->id }}', 'approve')" 
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium">
                                    Approve
                                </button>
                                <button onclick="reviewReport('{{ $report->id }}', 'reject')" 
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm font-medium">
                                    Reject
                                </button>
                            </div>
                        @endif

                        @if($report->review_status === 'approved')
                            <div class="mt-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Approved by {{ $report->reviewer?->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No reports found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by generating your first compliance report.</p>
                        <div class="mt-6">
                            <button onclick="showGenerateReportModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reports->hasPages())
            <div class="mt-8">
                {{ $reports->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center">Generate Compliance Report</h3>
                <form id="generateReportForm" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Type</label>
                        <select name="report_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Select Report Type</option>
                            <option value="hipaa_access">HIPAA Access Report</option>
                            <option value="hipaa_security">HIPAA Security Report</option>
                            <option value="dea_controlled_substances">DEA Controlled Substances</option>
                            <option value="dea_inventory">DEA Inventory Report</option>
                            <option value="pci_compliance">PCI Compliance Report</option>
                            <option value="audit_trail">Audit Trail Report</option>
                            <option value="data_retention">Data Retention Report</option>
                            <option value="user_access">User Access Report</option>
                            <option value="security_incidents">Security Incidents Report</option>
                            <option value="prescription_monitoring">Prescription Monitoring</option>
                            <option value="failed_logins">Failed Logins Report</option>
                            <option value="data_exports">Data Exports Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Name</label>
                        <input type="text" name="report_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                               placeholder="Monthly HIPAA Audit" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="period_start" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="period_end" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                                  placeholder="Additional context or notes for this report"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="hideGenerateReportModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showGenerateReportModal() {
            document.getElementById('generateReportModal').classList.remove('hidden');
            
            // Set default dates (last 30 days)
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            document.querySelector('[name="period_start"]').value = startDate.toISOString().split('T')[0];
            document.querySelector('[name="period_end"]').value = endDate.toISOString().split('T')[0];
        }

        function hideGenerateReportModal() {
            document.getElementById('generateReportModal').classList.add('hidden');
            document.getElementById('generateReportForm').reset();
        }

        function downloadReport(reportId) {
            window.location.href = `/compliance/reports/${reportId}/download`;
        }

        function showReportDetails(reportId) {
            // This would typically open a detailed view modal
            alert('Report details functionality would be implemented here');
        }

        function reviewReport(reportId, action) {
            const notes = action === 'reject' ? prompt('Please provide a reason for rejection:') : '';
            if (action === 'reject' && !notes) return;

            fetch(`/compliance/reports/${reportId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: action,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Report ${action}d successfully`);
                    location.reload();
                } else {
                    alert(`Failed to ${action} report: ` + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(`An error occurred while ${action}ing the report`);
            });
        }

        // Handle generate report form submission
        document.getElementById('generateReportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate dates
            if (new Date(data.period_start) >= new Date(data.period_end)) {
                alert('End date must be after start date');
                return;
            }
            
            fetch('/compliance/generate-report', {
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
                    alert('Report generation started successfully. You will be notified when it completes.');
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

        // Auto-update report name based on type selection
        document.querySelector('[name="report_type"]').addEventListener('change', function() {
            const reportType = this.value;
            const reportNameInput = document.querySelector('[name="report_name"]');
            
            if (reportType) {
                const now = new Date();
                const monthName = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                const typeName = this.options[this.selectedIndex].text;
                reportNameInput.value = `${monthName} ${typeName}`;
            }
        });
    </script>
</body>
</html>