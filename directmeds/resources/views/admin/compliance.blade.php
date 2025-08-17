@extends('layouts.admin')

@section('title', 'Compliance & Audit')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Compliance & Audit Dashboard</h1>
        <p class="text-gray-500 mt-2">Monitor regulatory compliance and audit trails</p>
    </div>
    <div class="flex space-x-3">
        <button class="px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m0 4h12a2 2 0 002-2v-3a1 1 0 00-1-1H5a1 1 0 00-1 1v3a2 2 0 002 2z"></path>
            </svg>
            Generate Report
        </button>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Audit Log
        </button>
    </div>
</div>

<!-- Compliance Status Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- HIPAA Compliance -->
    <div class="glass rounded-2xl p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <span class="text-2xl font-bold text-green-600">98%</span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2">HIPAA Compliance</h3>
        <p class="text-xs text-gray-500">Last audit: 2 days ago</p>
        <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Data Encryption</span>
                <span class="text-green-600">✓ Compliant</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Access Controls</span>
                <span class="text-green-600">✓ Compliant</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Audit Logging</span>
                <span class="text-green-600">✓ Active</span>
            </div>
        </div>
    </div>
    
    <!-- DEA Compliance -->
    <div class="glass rounded-2xl p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <span class="text-2xl font-bold text-blue-600">100%</span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2">DEA Compliance</h3>
        <p class="text-xs text-gray-500">All licenses verified</p>
        <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Schedule II-V Tracking</span>
                <span class="text-green-600">✓ Active</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Prescriber Verification</span>
                <span class="text-green-600">✓ Updated</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">ARCOS Reporting</span>
                <span class="text-green-600">✓ Current</span>
            </div>
        </div>
    </div>
    
    <!-- FDA Compliance -->
    <div class="glass rounded-2xl p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <span class="text-2xl font-bold text-purple-600">95%</span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2">FDA Compliance</h3>
        <p class="text-xs text-gray-500">3 items need attention</p>
        <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Drug Tracking</span>
                <span class="text-green-600">✓ Compliant</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Adverse Events</span>
                <span class="text-yellow-600">⚠ Review</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Recall Procedures</span>
                <span class="text-green-600">✓ Updated</span>
            </div>
        </div>
    </div>
    
    <!-- State Board -->
    <div class="glass rounded-2xl p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                </svg>
            </div>
            <span class="text-2xl font-bold text-orange-600">92%</span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2">State Board</h3>
        <p class="text-xs text-gray-500">License renewal due</p>
        <div class="mt-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Pharmacy License</span>
                <span class="text-yellow-600">⚠ Expires 30d</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Staff Licenses</span>
                <span class="text-green-600">✓ Current</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Inspections</span>
                <span class="text-green-600">✓ Passed</span>
            </div>
        </div>
    </div>
</div>

<!-- Audit Activity Timeline -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Recent Audit Events -->
    <div class="lg:col-span-2 glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Audit Events</h3>
        <div class="space-y-4 max-h-96 overflow-y-auto">
            <!-- Critical Event -->
            <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-lg border-l-4 border-red-500">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Unauthorized Access Attempt</p>
                    <p class="text-xs text-gray-600">User ID: USR-4821 attempted to access restricted DEA records</p>
                    <p class="text-xs text-gray-500 mt-1">5 minutes ago • IP: 192.168.1.45</p>
                </div>
            </div>
            
            <!-- Warning Event -->
            <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Bulk Data Export</p>
                    <p class="text-xs text-gray-600">Admin exported 500+ patient records to CSV</p>
                    <p class="text-xs text-gray-500 mt-1">1 hour ago • Reviewed by: John Smith</p>
                </div>
            </div>
            
            <!-- Info Event -->
            <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Prescription Verification</p>
                    <p class="text-xs text-gray-600">Schedule II prescription verified for patient P-45821</p>
                    <p class="text-xs text-gray-500 mt-1">2 hours ago • Pharmacist: Jane Doe, RPh</p>
                </div>
            </div>
            
            <!-- Success Event -->
            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Compliance Check Passed</p>
                    <p class="text-xs text-gray-600">Monthly HIPAA compliance audit completed successfully</p>
                    <p class="text-xs text-gray-500 mt-1">3 hours ago • Automated System</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compliance Metrics -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Compliance Metrics</h3>
        
        <div class="space-y-4">
            <!-- Data Retention -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Data Retention</span>
                    <span class="text-sm font-medium text-gray-900">7 years</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full w-full bg-gradient-to-r from-green-400 to-green-600 rounded-full"></div>
                </div>
            </div>
            
            <!-- Audit Coverage -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Audit Coverage</span>
                    <span class="text-sm font-medium text-gray-900">96%</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-400 to-blue-600 rounded-full" style="width: 96%"></div>
                </div>
            </div>
            
            <!-- Security Score -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Security Score</span>
                    <span class="text-sm font-medium text-gray-900">A+</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-400 to-purple-600 rounded-full" style="width: 98%"></div>
                </div>
            </div>
            
            <!-- Response Time -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Avg Response Time</span>
                    <span class="text-sm font-medium text-gray-900">4.2 min</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-orange-400 to-orange-600 rounded-full" style="width: 85%"></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h4>
            <div class="space-y-2">
                <button class="w-full text-left px-3 py-2 text-sm bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100">
                    Run HIPAA Audit
                </button>
                <button class="w-full text-left px-3 py-2 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                    Generate DEA Report
                </button>
                <button class="w-full text-left px-3 py-2 text-sm bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                    Review Access Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Compliance Reports -->
<div class="glass rounded-2xl p-6 border border-white/20">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Compliance Reports</h3>
        <select class="px-3 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-200">
            <option>Last 30 days</option>
            <option>Last 90 days</option>
            <option>Last year</option>
        </select>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs text-green-600 font-medium">Available</span>
            </div>
            <h4 class="font-medium text-gray-900">HIPAA Compliance Report</h4>
            <p class="text-xs text-gray-500 mt-1">Q4 2024 • Generated Dec 1</p>
        </div>
        
        <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs text-blue-600 font-medium">Processing</span>
            </div>
            <h4 class="font-medium text-gray-900">DEA ARCOS Report</h4>
            <p class="text-xs text-gray-500 mt-1">November 2024 • In progress</p>
        </div>
        
        <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs text-purple-600 font-medium">Scheduled</span>
            </div>
            <h4 class="font-medium text-gray-900">State Board Audit</h4>
            <p class="text-xs text-gray-500 mt-1">Due Dec 15, 2024</p>
        </div>
    </div>
</div>
@endsection