@extends('layouts.admin')

@section('title', 'Products Management')

@section('content')
<!-- Page Header with Actions -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Products Management</h1>
        <p class="text-gray-500 mt-2">Manage your pharmacy inventory and medications</p>
    </div>
    <div class="flex space-x-3">
        <button class="px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Import CSV
        </button>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Product
        </button>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass rounded-2xl p-4 mb-6 border border-white/20">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="relative">
            <input type="text" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:outline-none">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <select class="px-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Categories</option>
            <option>Antibiotics</option>
            <option>Pain Relief</option>
            <option>Cardiovascular</option>
            <option>Diabetes</option>
            <option>Mental Health</option>
        </select>
        <select class="px-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Schedules</option>
            <option>Non-controlled</option>
            <option>Schedule II</option>
            <option>Schedule III</option>
            <option>Schedule IV</option>
            <option>Schedule V</option>
        </select>
        <select class="px-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>Stock Status</option>
            <option>In Stock</option>
            <option>Low Stock</option>
            <option>Out of Stock</option>
        </select>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Products</p>
                <p class="text-2xl font-bold text-gray-900">1,248</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Low Stock Alert</p>
                <p class="text-2xl font-bold text-orange-600">23</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Out of Stock</p>
                <p class="text-2xl font-bold text-red-600">5</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Value</p>
                <p class="text-2xl font-bold text-gray-900">$245.8K</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="glass rounded-2xl border border-white/20 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NDC</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Product Row 1 -->
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">L</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Lipitor 20mg</p>
                                <p class="text-xs text-gray-500">Atorvastatin Calcium</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-900 font-mono">00071-0155-23</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">Cardiovascular</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Non-controlled</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900">450</span>
                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full w-3/4 bg-green-500 rounded-full"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900">$156.99</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="p-1 hover:bg-red-50 rounded">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Product Row 2 - Controlled Substance -->
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">A</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Adderall XR 20mg</p>
                                <p class="text-xs text-gray-500">Mixed Amphetamine Salts</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-900 font-mono">54092-0389-01</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-full">Mental Health</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Schedule II</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-orange-600">15</span>
                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full w-1/4 bg-orange-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900">$299.99</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="p-1 hover:bg-red-50 rounded">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Product Row 3 - Out of Stock -->
                <tr class="hover:bg-gray-50 transition-colors bg-red-50">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">M</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Metformin 500mg</p>
                                <p class="text-xs text-gray-500">Metformin HCl</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-900 font-mono">00378-2074-01</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Diabetes</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Non-controlled</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-red-600">0</span>
                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full w-0 bg-red-500 rounded-full"></div>
                            </div>
                            <span class="text-xs text-red-600 font-medium">OUT OF STOCK</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900">$4.99</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded hover:bg-orange-200">
                                Reorder
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Table Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Showing <span class="font-medium">1-10</span> of <span class="font-medium">1,248</span> products
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" disabled>
                Previous
            </button>
            <button class="px-3 py-1 bg-purple-600 text-white rounded-lg">1</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">2</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">3</button>
            <span class="px-2">...</span>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">125</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">
                Next
            </button>
        </div>
    </div>
</div>
@endsection