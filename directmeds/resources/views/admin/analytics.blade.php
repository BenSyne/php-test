@extends('layouts.admin')

@section('title', 'Analytics')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="text-gray-500 mt-2">Comprehensive business intelligence and insights</p>
    </div>
    <div class="flex space-x-3">
        <select class="px-4 py-2 border border-gray-300 rounded-xl focus:border-purple-500 focus:outline-none">
            <option>Last 30 days</option>
            <option>Last 90 days</option>
            <option>Last year</option>
            <option>All time</option>
        </select>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Export Report
        </button>
    </div>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">+23.5%</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">$1.2M</h3>
        <p class="text-sm text-gray-500 mt-1">Total Revenue</p>
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">vs last period</span>
                <span class="text-green-600 font-medium">↑ $234K</span>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">+18.2%</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">4,892</h3>
        <p class="text-sm text-gray-500 mt-1">Total Orders</p>
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Avg order value</span>
                <span class="text-gray-900 font-medium">$245.32</span>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">+45.8%</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">12,458</h3>
        <p class="text-sm text-gray-500 mt-1">Active Customers</p>
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">New this month</span>
                <span class="text-purple-600 font-medium">+1,832</span>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-2xl p-6 border border-white/20 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded-full">98.2%</span>
        </div>
        <h3 class="text-3xl font-bold text-gray-900">4.8/5.0</h3>
        <p class="text-sm text-gray-500 mt-1">Customer Satisfaction</p>
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Reviews</span>
                <span class="text-orange-600 font-medium">2,341</span>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Trend -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs bg-purple-600 text-white rounded-lg">Daily</button>
                <button class="px-3 py-1 text-xs bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Weekly</button>
                <button class="px-3 py-1 text-xs bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Monthly</button>
            </div>
        </div>
        <div style="height: 300px;">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>
    
    <!-- Product Performance -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Top Products</h3>
            <a href="/admin/products" class="text-sm text-purple-600 hover:text-purple-700">View All</a>
        </div>
        <div style="height: 300px;">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Conversion Funnel -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversion Funnel</h3>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Visitors</span>
                    <span class="text-sm font-medium">45,892</span>
                </div>
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: 100%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Add to Cart</span>
                    <span class="text-sm font-medium">8,234 (17.9%)</span>
                </div>
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: 75%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Checkout</span>
                    <span class="text-sm font-medium">5,123 (11.2%)</span>
                </div>
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: 50%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Purchase</span>
                    <span class="text-sm font-medium">4,892 (10.7%)</span>
                </div>
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full" style="width: 45%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Geographic Distribution -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Locations</h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-900">California</span>
                </div>
                <span class="text-sm text-gray-600">2,834 orders</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-900">Texas</span>
                </div>
                <span class="text-sm text-gray-600">1,923 orders</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-900">New York</span>
                </div>
                <span class="text-sm text-gray-600">1,542 orders</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-900">Florida</span>
                </div>
                <span class="text-sm text-gray-600">1,234 orders</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-900">Illinois</span>
                </div>
                <span class="text-sm text-gray-600">892 orders</span>
            </div>
        </div>
    </div>
    
    <!-- Customer Metrics -->
    <div class="glass rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Metrics</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <span class="text-sm text-gray-600">Retention Rate</span>
                <span class="text-sm font-bold text-green-600">89.2%</span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <span class="text-sm text-gray-600">Lifetime Value</span>
                <span class="text-sm font-bold text-gray-900">$1,234</span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <span class="text-sm text-gray-600">Churn Rate</span>
                <span class="text-sm font-bold text-orange-600">2.3%</span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <span class="text-sm text-gray-600">NPS Score</span>
                <span class="text-sm font-bold text-purple-600">72</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Repeat Purchase</span>
                <span class="text-sm font-bold text-blue-600">64.5%</span>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Activity -->
<div class="glass rounded-2xl p-6 border border-white/20">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Real-time Activity</h3>
        <div class="flex items-center space-x-2">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-sm text-gray-600">Live</span>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center">
            <p class="text-3xl font-bold text-gray-900">142</p>
            <p class="text-sm text-gray-500">Active Users</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-blue-600">23</p>
            <p class="text-sm text-gray-500">Carts Active</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-green-600">$4,234</p>
            <p class="text-sm text-gray-500">Last Hour Revenue</p>
        </div>
        <div class="text-center">
            <p class="text-3xl font-bold text-purple-600">8</p>
            <p class="text-sm text-gray-500">Orders Processing</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
new Chart(revenueTrendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: 'Revenue',
                data: [65000, 72000, 68000, 85000, 92000, 98000, 105000, 112000, 108000, 115000, 122000, 135000],
                borderColor: 'rgb(139, 92, 246)',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Profit',
                data: [28000, 32000, 30000, 38000, 42000, 45000, 48000, 52000, 50000, 54000, 58000, 65000],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
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

// Top Products Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: ['Lipitor', 'Metformin', 'Lisinopril', 'Levothyroxine', 'Amlodipine', 'Omeprazole'],
        datasets: [{
            label: 'Units Sold',
            data: [450, 380, 320, 290, 250, 220],
            backgroundColor: [
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(251, 146, 60, 0.8)',
                'rgba(250, 204, 21, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush