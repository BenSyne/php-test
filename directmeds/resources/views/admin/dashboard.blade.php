@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
    <p class="text-gray-500 mt-2">Welcome back! Here's what's happening with your pharmacy today.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Revenue Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">+12.5%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">$45,231</h3>
        <p class="text-sm text-gray-500 mt-1">Today's Revenue</p>
        <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full w-3/4 bg-gradient-to-r from-green-400 to-green-600 rounded-full"></div>
        </div>
    </div>
    
    <!-- Orders Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">+8 new</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">156</h3>
        <p class="text-sm text-gray-500 mt-1">Total Orders</p>
        <div class="mt-4 flex space-x-2">
            <span class="text-xs text-gray-500">12 pending</span>
            <span class="text-xs text-gray-500">•</span>
            <span class="text-xs text-gray-500">144 completed</span>
        </div>
    </div>
    
    <!-- Prescriptions Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
            </span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">23</h3>
        <p class="text-sm text-gray-500 mt-1">Pending Review</p>
        <div class="mt-4 flex space-x-2">
            <span class="text-xs text-orange-600 font-medium">5 urgent</span>
            <span class="text-xs text-gray-500">•</span>
            <span class="text-xs text-gray-500">18 regular</span>
        </div>
    </div>
    
    <!-- Customers Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-pink-600 bg-pink-100 px-2 py-1 rounded-full">+18%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">1,429</h3>
        <p class="text-sm text-gray-500 mt-1">Active Customers</p>
        <div class="mt-4 flex space-x-2">
            <span class="text-xs text-gray-500">89 new this week</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Overview</h3>
            <select class="text-sm border border-gray-200 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-purple-200">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 90 days</option>
            </select>
        </div>
        <div style="height: 250px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Orders by Status -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
            <button class="text-sm text-purple-600 hover:text-purple-700 font-medium">View All</button>
        </div>
        <div style="height: 250px;">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Orders -->
    <div class="lg:col-span-2 glass rounded-2xl border border-white/20">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                <a href="/admin/orders" class="text-sm text-purple-600 hover:text-purple-700 font-medium">View All</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3 text-left">Order ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">#ORD-2024-1234</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">JD</span>
                                </div>
                                <span class="text-sm text-gray-900">John Doe</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">$245.00</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Processing</span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">#ORD-2024-1233</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-green-400 rounded-full flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">AS</span>
                                </div>
                                <span class="text-sm text-gray-900">Alice Smith</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">$189.50</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Delivered</span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-purple-600 hover:text-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
        <div class="space-y-3">
            <button class="w-full flex items-center space-x-3 p-3 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Add New Product</span>
            </button>
            
            <button class="w-full flex items-center space-x-3 p-3 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Review Prescriptions</span>
            </button>
            
            <button class="w-full flex items-center space-x-3 p-3 rounded-xl border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Generate Reports</span>
            </button>
        </div>
        
        <!-- Activity Feed -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-4">Recent Activity</h4>
            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5"></div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-600">New prescription verified</p>
                        <p class="text-xs text-gray-400 mt-1">2 min ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5"></div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-600">Order #1232 shipped</p>
                        <p class="text-xs text-gray-400 mt-1">15 min ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-1.5"></div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-600">Inventory updated</p>
                        <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 250);
revenueGradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');
revenueGradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Revenue',
            data: [30000, 35000, 32000, 38000, 45231, 41000, 43000],
            borderColor: 'rgb(139, 92, 246)',
            backgroundColor: revenueGradient,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(139, 92, 246)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 10
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000) + 'k';
                    }
                }
            }
        }
    }
});

// Orders Chart
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
    type: 'doughnut',
    data: {
        labels: ['Delivered', 'Processing', 'Pending', 'Cancelled'],
        datasets: [{
            data: [144, 45, 12, 8],
            backgroundColor: [
                'rgb(34, 197, 94)',
                'rgb(251, 191, 36)',
                'rgb(59, 130, 246)',
                'rgb(239, 68, 68)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 10
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            }
        }
    }
});
</script>
@endpush