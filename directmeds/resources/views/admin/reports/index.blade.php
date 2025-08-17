@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
    <!-- Page Header -->
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
            <p class="mt-2 text-sm text-gray-700">Comprehensive insights into sales, operations, and performance metrics.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <select id="dateRange" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Monthly Revenue</div>
                        <div class="text-2xl font-semibold text-gray-900">${{ number_format($reportData['sales_summary']['this_month_revenue'] / 100, 2) }}</div>
                        @php
                            $growth = $reportData['sales_summary']['last_month_revenue'] > 0 
                                ? (($reportData['sales_summary']['this_month_revenue'] - $reportData['sales_summary']['last_month_revenue']) / $reportData['sales_summary']['last_month_revenue']) * 100 
                                : 0;
                        @endphp
                        @if($growth >= 0)
                            <div class="text-sm text-green-600">+{{ number_format($growth, 1) }}% from last month</div>
                        @else
                            <div class="text-sm text-red-600">{{ number_format($growth, 1) }}% from last month</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Monthly Orders</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['sales_summary']['this_month_orders']) }}</div>
                        <div class="text-sm text-gray-600">Avg: ${{ number_format($reportData['sales_summary']['average_order_value'] / 100, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Active Users</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['user_summary']['active_users']) }}</div>
                        <div class="text-sm text-gray-600">of {{ number_format($reportData['user_summary']['total_users']) }} total</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Prescriptions</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['prescription_summary']['total_prescriptions']) }}</div>
                        @if($reportData['prescription_summary']['pending_verification'] > 0)
                            <div class="text-sm text-yellow-600">{{ $reportData['prescription_summary']['pending_verification'] }} pending</div>
                        @else
                            <div class="text-sm text-green-600">All up to date</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Revenue Trend -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Revenue Trend</h3>
                <p class="text-sm text-gray-500">Daily revenue over selected period</p>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- Order Volume -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Volume</h3>
                <p class="text-sm text-gray-500">Daily orders over selected period</p>
            </div>
            <div class="p-6">
                <canvas id="orderChart" height="300"></canvas>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top Selling Products</h3>
                <p class="text-sm text-gray-500">Most popular products by volume</p>
            </div>
            <div class="p-6">
                <canvas id="topProductsChart" height="300"></canvas>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Status Distribution</h3>
                <p class="text-sm text-gray-500">Current order statuses</p>
            </div>
            <div class="p-6">
                <canvas id="orderStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- User Analytics -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">User Analytics</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($reportData['user_summary']['users_by_type'] as $type => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3
                                    @if($type === 'patient') bg-blue-500
                                    @elseif($type === 'pharmacist') bg-green-500
                                    @elseif($type === 'prescriber') bg-purple-500
                                    @elseif($type === 'admin') bg-red-500
                                    @else bg-gray-500 @endif"></div>
                                <span class="text-sm font-medium text-gray-900">{{ ucfirst($type) }}s</span>
                            </div>
                            <span class="text-sm text-gray-600">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Compliance Status -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Compliance Status</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Total Reports</span>
                        <span class="text-sm text-gray-600">{{ number_format($reportData['compliance_summary']['total_reports']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Pending Reports</span>
                        <span class="text-sm text-yellow-600">{{ number_format($reportData['compliance_summary']['pending_reports']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Overdue Reports</span>
                        <span class="text-sm text-red-600">{{ number_format($reportData['compliance_summary']['overdue_reports']) }}</span>
                    </div>
                    @if($reportData['compliance_summary']['overdue_reports'] > 0)
                        <div class="mt-4">
                            <a href="{{ route('compliance.reports') }}" 
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                Review Overdue Reports
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Export Reports</h3>
            <p class="text-sm text-gray-500">Download detailed reports for analysis and compliance</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.reports.sales.export') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Sales Report
                </a>

                <a href="{{ route('admin.reports.inventory.export') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"></path>
                    </svg>
                    Inventory Report
                </a>

                <a href="{{ route('admin.reports.users.export') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    User Report
                </a>

                <a href="{{ route('admin.reports.prescriptions.export') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Prescription Report
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = 30;
    let chartInstances = {};

    // Date range selector
    document.getElementById('dateRange').addEventListener('change', function(e) {
        currentPeriod = e.target.value;
        loadAnalyticsData();
    });

    // Load initial data
    loadAnalyticsData();

    function loadAnalyticsData() {
        fetch(`{{ route("admin.analytics") }}?period=${currentPeriod}`)
            .then(response => response.json())
            .then(data => {
                updateCharts(data);
            })
            .catch(error => console.error('Error loading analytics:', error));
    }

    function updateCharts(data) {
        // Destroy existing charts
        Object.values(chartInstances).forEach(chart => chart.destroy());
        chartInstances = {};

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        chartInstances.revenue = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: data.revenue_trend.map(item => new Date(item.date).toLocaleDateString()),
                datasets: [{
                    label: 'Revenue ($)',
                    data: data.revenue_trend.map(item => item.revenue),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Order Chart
        const orderCtx = document.getElementById('orderChart').getContext('2d');
        chartInstances.orders = new Chart(orderCtx, {
            type: 'bar',
            data: {
                labels: data.order_trend.map(item => new Date(item.date).toLocaleDateString()),
                datasets: [{
                    label: 'Orders',
                    data: data.order_trend.map(item => item.orders),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Top Products Chart
        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        chartInstances.topProducts = new Chart(topProductsCtx, {
            type: 'horizontalBar',
            data: {
                labels: data.top_products.map(item => item.name),
                datasets: [{
                    label: 'Quantity Sold',
                    data: data.top_products.map(item => item.total_sold),
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        chartInstances.orderStatus = new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: data.order_status_distribution.map(item => item.status),
                datasets: [{
                    data: data.order_status_distribution.map(item => item.count),
                    backgroundColor: [
                        'rgb(234, 179, 8)',    // Pending - yellow
                        'rgb(59, 130, 246)',   // Processing - blue
                        'rgb(99, 102, 241)',   // Shipped - indigo
                        'rgb(34, 197, 94)',    // Delivered - green
                        'rgb(239, 68, 68)',    // Cancelled - red
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Auto-refresh data every 5 minutes
    setInterval(loadAnalyticsData, 300000);
});
</script>
@endpush