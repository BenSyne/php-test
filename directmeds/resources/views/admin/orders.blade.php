@extends('layouts.admin')

@section('title', 'Orders Management')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Orders Management</h1>
        <p class="text-gray-500 mt-2">Track and manage customer orders</p>
    </div>
    <div class="flex space-x-3">
        <button class="px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Orders
        </button>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Order
        </button>
    </div>
</div>

<!-- Order Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">New Orders</p>
        <p class="text-2xl font-bold text-blue-600">12</p>
        <div class="mt-2 flex items-center text-xs">
            <span class="text-green-600">↑ 8%</span>
            <span class="text-gray-500 ml-1">vs yesterday</span>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">Processing</p>
        <p class="text-2xl font-bold text-yellow-600">34</p>
        <div class="mt-2 h-1 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full w-3/4 bg-yellow-500 rounded-full"></div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">Ready to Ship</p>
        <p class="text-2xl font-bold text-purple-600">18</p>
        <span class="text-xs text-purple-600 bg-purple-100 px-2 py-1 rounded-full">Action needed</span>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">Shipped</p>
        <p class="text-2xl font-bold text-indigo-600">56</p>
        <p class="text-xs text-gray-500 mt-1">In transit</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">Delivered</p>
        <p class="text-2xl font-bold text-green-600">423</p>
        <p class="text-xs text-gray-500 mt-1">This month</p>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <p class="text-xs text-gray-500 uppercase mb-2">Cancelled</p>
        <p class="text-2xl font-bold text-red-600">3</p>
        <p class="text-xs text-gray-500 mt-1">0.7% rate</p>
    </div>
</div>

<!-- Filters -->
<div class="glass rounded-2xl p-4 mb-6 border border-white/20">
    <div class="flex flex-wrap items-center gap-3">
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Statuses</option>
            <option>New</option>
            <option>Processing</option>
            <option>Ready to Ship</option>
            <option>Shipped</option>
            <option>Delivered</option>
            <option>Cancelled</option>
        </select>
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>Last 7 days</option>
            <option>Last 30 days</option>
            <option>Last 3 months</option>
            <option>Last year</option>
        </select>
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Payment Methods</option>
            <option>Credit Card</option>
            <option>Insurance</option>
            <option>Cash</option>
        </select>
        <div class="ml-auto relative">
            <input type="text" placeholder="Search orders..." class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="glass rounded-2xl border border-white/20 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Priority Order -->
                <tr class="hover:bg-yellow-50 bg-yellow-50/50">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">#ORD-2024-1248</p>
                            <p class="text-xs text-gray-500">Today, 2:30 PM</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                Express Shipping
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                                <span class="text-xs text-white font-medium">MJ</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Maria Johnson</p>
                                <p class="text-xs text-gray-500">Premium Member</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm text-gray-900">5 items</p>
                            <p class="text-xs text-orange-600">2 controlled</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">$458.99</p>
                        <p class="text-xs text-gray-500">Insurance: $120</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-600">Paid</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                            Processing
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-green-600 hover:text-green-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Regular Order -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">#ORD-2024-1247</p>
                            <p class="text-xs text-gray-500">Today, 1:15 PM</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-green-400 rounded-full flex items-center justify-center">
                                <span class="text-xs text-white font-medium">JD</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">John Davis</p>
                                <p class="text-xs text-gray-500">New Customer</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">3 items</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">$127.50</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-600">Paid</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                            New
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-blue-600 hover:text-blue-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Shipped Order -->
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">#ORD-2024-1246</p>
                            <p class="text-xs text-gray-500">Yesterday, 4:20 PM</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-red-400 rounded-full flex items-center justify-center">
                                <span class="text-xs text-white font-medium">EW</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Emma Wilson</p>
                                <p class="text-xs text-gray-500">Regular Customer</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">8 items</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">$342.00</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-600">Paid</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full">
                            Shipped
                        </span>
                        <p class="text-xs text-gray-500 mt-1">Track: UPS1234</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <button class="text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Table Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <select class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                <option>Bulk Actions</option>
                <option>Mark as Shipped</option>
                <option>Export Selected</option>
                <option>Print Labels</option>
            </select>
            <button class="px-3 py-1 bg-purple-600 text-white rounded-lg text-sm">Apply</button>
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" disabled>
                Previous
            </button>
            <button class="px-3 py-1 bg-purple-600 text-white rounded-lg">1</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">2</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">3</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">
                Next
            </button>
        </div>
    </div>
</div>
@endsection