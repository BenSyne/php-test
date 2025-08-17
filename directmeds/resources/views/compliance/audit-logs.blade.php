<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - DirectMeds Compliance</title>
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
                        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                        <span class="ml-2 px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                            PHI Access Tracking
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="exportAuditLogs()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Export CSV
                        </button>
                        <button onclick="refreshLogs()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                </div>
                <form method="GET" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Type</label>
                            <select name="event_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Events</option>
                                <option value="login" {{ request('event_type') === 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('event_type') === 'logout' ? 'selected' : '' }}>Logout</option>
                                <option value="failed_login" {{ request('event_type') === 'failed_login' ? 'selected' : '' }}>Failed Login</option>
                                <option value="patient_profile_accessed" {{ request('event_type') === 'patient_profile_accessed' ? 'selected' : '' }}>Patient Profile Access</option>
                                <option value="prescription_created" {{ request('event_type') === 'prescription_created' ? 'selected' : '' }}>Prescription Created</option>
                                <option value="prescription_dispensed" {{ request('event_type') === 'prescription_dispensed' ? 'selected' : '' }}>Prescription Dispensed</option>
                                <option value="medical_record_accessed" {{ request('event_type') === 'medical_record_accessed' ? 'selected' : '' }}>Medical Record Access</option>
                                <option value="payment_processed" {{ request('event_type') === 'payment_processed' ? 'selected' : '' }}>Payment Processed</option>
                                <option value="data_export" {{ request('event_type') === 'data_export' ? 'selected' : '' }}>Data Export</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Risk Level</label>
                            <select name="risk_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Risk Levels</option>
                                <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Classification</label>
                            <select name="data_classification" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Classifications</option>
                                <option value="public" {{ request('data_classification') === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="internal" {{ request('data_classification') === 'internal' ? 'selected' : '' }}>Internal</option>
                                <option value="confidential" {{ request('data_classification') === 'confidential' ? 'selected' : '' }}>Confidential</option>
                                <option value="phi" {{ request('data_classification') === 'phi' ? 'selected' : '' }}>PHI</option>
                                <option value="pci" {{ request('data_classification') === 'pci' ? 'selected' : '' }}>PCI</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">User</label>
                            <input type="number" name="user_id" value="{{ request('user_id') }}" 
                                   placeholder="User ID" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="phi_only" value="1" {{ request('phi_only') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">PHI Only</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="controlled_substances_only" value="1" {{ request('controlled_substances_only') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Controlled Substances</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input type="checkbox" name="failed_access_only" value="1" {{ request('failed_access_only') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Failed Access Only</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <a href="{{ route('compliance.audit-logs') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Clear Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Summary -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">
                                Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} 
                                of {{ $auditLogs->total() }} audit log entries
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            @if(request()->hasAny(['event_type', 'risk_level', 'data_classification', 'user_id', 'date_from', 'date_to', 'phi_only', 'controlled_substances_only', 'failed_access_only']))
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Filtered Results
                                </span>
                            @endif
                            @if(request('phi_only'))
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    PHI Data Only
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Timestamp
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Event
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Entity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Level
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Compliance
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP Address
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($auditLogs as $log)
                            <tr class="hover:bg-gray-50 {{ !$log->access_granted ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ ucwords(str_replace('_', ' ', $log->event_type)) }}
                                    </div>
                                    @if($log->description)
                                        <div class="text-xs text-gray-500 truncate max-w-xs">
                                            {{ $log->description }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->user?->name ?: 'System' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->user?->user_type ?: 'system' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($log->entity_type)
                                        <div>{{ $log->entity_type }}</div>
                                        @if($log->entity_identifier)
                                            <div class="text-xs text-gray-500">{{ $log->entity_identifier }}</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $riskColors = [
                                            'low' => 'bg-green-100 text-green-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'high' => 'bg-red-100 text-red-800',
                                            'critical' => 'bg-purple-100 text-purple-800'
                                        ];
                                        $riskColor = $riskColors[$log->risk_level] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $riskColor }}">
                                        {{ ucfirst($log->risk_level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-1">
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
                                        @if($log->is_financial_data)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                PCI
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->access_granted)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Success
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Failed
                                        </span>
                                    @endif
                                    @if($log->response_status)
                                        <div class="text-xs text-gray-500 mt-1">
                                            HTTP {{ $log->response_status }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No audit logs found</h3>
                                    <p class="mt-1 text-sm text-gray-500">No audit logs match your current filters.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($auditLogs->hasPages())
                <div class="bg-white px-6 py-3 border-t border-gray-200">
                    {{ $auditLogs->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function refreshLogs() {
            location.reload();
        }

        function exportAuditLogs() {
            // Build query string from current filters
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Add export format
            params.append('format', 'csv');
            
            // Create download link
            const exportUrl = '{{ route("compliance.audit-logs") }}?' + params.toString();
            window.open(exportUrl, '_blank');
        }

        // Auto-refresh every 2 minutes for active monitoring
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 120000);

        // Highlight failed access attempts
        document.querySelectorAll('tr').forEach(row => {
            if (row.classList.contains('bg-red-50')) {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('bg-red-100');
                });
                row.addEventListener('mouseleave', function() {
                    this.classList.remove('bg-red-100');
                });
            }
        });
    </script>
</body>
</html>