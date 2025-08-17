@extends('layouts.admin')

@section('title', 'Prescriptions Management')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Prescriptions Management</h1>
        <p class="text-gray-500 mt-2">Review and verify patient prescriptions</p>
    </div>
    <div class="flex space-x-3">
        <button class="px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Export Report
        </button>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            New Prescription
        </button>
    </div>
</div>

<!-- Alert Bar for Urgent Prescriptions -->
<div class="glass rounded-2xl p-4 mb-6 border-l-4 border-orange-500 bg-gradient-to-r from-orange-50 to-transparent">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-orange-900">5 prescriptions require urgent verification</p>
                <p class="text-xs text-orange-700">3 are for controlled substances • 2 have expired prescriber licenses</p>
            </div>
        </div>
        <button class="text-orange-600 hover:text-orange-700 font-medium text-sm">View Urgent</button>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Pending Review</p>
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">23</p>
        <p class="text-xs text-gray-500 mt-1">↑ 12% from yesterday</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Verified Today</p>
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">87</p>
        <p class="text-xs text-green-600 mt-1">On track</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Rejected</p>
            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">4</p>
        <p class="text-xs text-gray-500 mt-1">Invalid DEA/NPI</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Controlled</p>
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">15</p>
        <p class="text-xs text-purple-600 mt-1">Schedule II-V</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase font-medium">Avg. Process Time</p>
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">4.2m</p>
        <p class="text-xs text-blue-600 mt-1">↓ 15% faster</p>
    </div>
</div>

<!-- Filters and Actions -->
<div class="glass rounded-2xl p-4 mb-6 border border-white/20">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex flex-wrap gap-2">
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium">All (129)</button>
            <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50">Pending (23)</button>
            <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50">Verified (87)</button>
            <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50">Rejected (4)</button>
            <button class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50">Controlled (15)</button>
        </div>
        <div class="flex items-center space-x-3">
            <div class="relative">
                <input type="text" placeholder="Search by patient, prescriber, drug..." class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
                <option>Sort by: Newest First</option>
                <option>Sort by: Oldest First</option>
                <option>Sort by: Priority</option>
                <option>Sort by: Patient Name</option>
            </select>
        </div>
    </div>
</div>

<!-- Prescriptions List -->
<div class="space-y-4">
    <!-- Urgent Prescription Card -->
    <div class="glass rounded-2xl border-l-4 border-red-500 overflow-hidden hover:shadow-xl transition-shadow">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">URGENT</span>
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full">Schedule II</span>
                        <span class="text-sm text-gray-500">RX-2024-0892</span>
                        <span class="text-sm text-gray-400">• 5 minutes ago</span>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Patient</p>
                            <p class="font-semibold text-gray-900">Sarah Johnson</p>
                            <p class="text-sm text-gray-600">DOB: 03/15/1985 • ID: P-45821</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Medication</p>
                            <p class="font-semibold text-gray-900">Oxycodone HCl 10mg</p>
                            <p class="text-sm text-gray-600">Qty: 30 • 10 days supply</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Prescriber</p>
                            <p class="font-semibold text-gray-900">Dr. Michael Chen, MD</p>
                            <p class="text-sm text-gray-600">DEA: BC1234567 • NPI: 1234567890</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Patient has active opioid prescription from another provider (filled 5 days ago)
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-2 ml-6">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Verify & Approve
                    </button>
                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Reject
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Request Info
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Regular Prescription Card -->
    <div class="glass rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending Review</span>
                        <span class="text-sm text-gray-500">RX-2024-0891</span>
                        <span class="text-sm text-gray-400">• 12 minutes ago</span>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Patient</p>
                            <p class="font-semibold text-gray-900">Robert Martinez</p>
                            <p class="text-sm text-gray-600">DOB: 08/22/1967 • ID: P-45820</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Medication</p>
                            <p class="font-semibold text-gray-900">Lisinopril 10mg</p>
                            <p class="text-sm text-gray-600">Qty: 90 • 90 days supply</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Prescriber</p>
                            <p class="font-semibold text-gray-900">Dr. Emily Watson, MD</p>
                            <p class="text-sm text-gray-600">DEA: BW7654321 • NPI: 0987654321</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-2 ml-6">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Verify & Approve
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Verified Prescription Card -->
    <div class="glass rounded-2xl border-l-4 border-green-500 overflow-hidden hover:shadow-lg transition-shadow opacity-75">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Verified</span>
                        <span class="text-sm text-gray-500">RX-2024-0890</span>
                        <span class="text-sm text-gray-400">• 1 hour ago</span>
                        <span class="text-sm text-green-600">✓ Verified by John Smith, RPh</span>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Patient</p>
                            <p class="font-semibold text-gray-900">Jennifer Lee</p>
                            <p class="text-sm text-gray-600">DOB: 11/30/1992 • ID: P-45819</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Medication</p>
                            <p class="font-semibold text-gray-900">Metformin 500mg</p>
                            <p class="text-sm text-gray-600">Qty: 60 • 30 days supply</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase mb-1">Prescriber</p>
                            <p class="font-semibold text-gray-900">Dr. James Park, MD</p>
                            <p class="text-sm text-gray-600">DEA: BP9876543 • NPI: 1122334455</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-2 ml-6">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Process Order
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6 flex items-center justify-between">
    <p class="text-sm text-gray-600">
        Showing <span class="font-medium">1-10</span> of <span class="font-medium">129</span> prescriptions
    </p>
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" disabled>
            Previous
        </button>
        <button class="px-3 py-1 bg-purple-600 text-white rounded-lg">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">2</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">3</button>
        <span class="px-2">...</span>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">13</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">
            Next
        </button>
    </div>
</div>
@endsection