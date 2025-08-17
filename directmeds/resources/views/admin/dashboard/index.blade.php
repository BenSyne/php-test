@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Revenue Card -->
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
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-medium text-gray-500">Monthly Revenue</div>
                        <div class="text-2xl font-semibold text-gray-900">${{ number_format($metrics['revenue']['month'] / 100, 2) }}</div>
                        @if($metrics['revenue']['growth'] >= 0)
                            <div class="text-sm text-green-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                +{{ $metrics['revenue']['growth'] }}%
                            </div>
                        @else
                            <div class="text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 112 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $metrics['revenue']['growth'] }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
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
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-medium text-gray-500">Monthly Orders</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['orders']['month']) }}</div>
                        <div class="text-sm text-orange-600">{{ $metrics['orders']['pending'] }} pending</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
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
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-medium text-gray-500">Total Users</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['users']['total']) }}</div>
                        <div class="text-sm text-green-600">{{ $metrics['users']['new_month'] }} new this month</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescriptions Card -->
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
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-medium text-gray-500">Prescriptions</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($metrics['prescriptions']['total']) }}</div>
                        @if($metrics['prescriptions']['pending'] > 0)
                            <div class="text-sm text-yellow-600">{{ $metrics['prescriptions']['pending'] }} pending verification</div>
                        @else
                            <div class="text-sm text-green-600">All verified</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if(count($alerts) > 0)
    <div class="mb-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">System Alerts</h2>
        <div class="space-y-3">
            @foreach($alerts as $alert)
                <div class="@if($alert['type'] === 'error') bg-red-50 border-red-200 @elseif($alert['type'] === 'warning') bg-yellow-50 border-yellow-200 @else bg-blue-50 border-blue-200 @endif border rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            @if($alert['type'] === 'error')
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            @elseif($alert['type'] === 'warning')
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium @if($alert['type'] === 'error') text-red-800 @elseif($alert['type'] === 'warning') text-yellow-800 @else text-blue-800 @endif">
                                {{ $alert['title'] }}
                            </h3>
                            <div class="mt-1 text-sm @if($alert['type'] === 'error') text-red-700 @elseif($alert['type'] === 'warning') text-yellow-700 @else text-blue-700 @endif">
                                {{ $alert['message'] }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <a href="{{ $alert['action_url'] }}" 
                               class="@if($alert['type'] === 'error') bg-red-100 text-red-800 hover:bg-red-200 @elseif($alert['type'] === 'warning') bg-yellow-100 text-yellow-800 hover:bg-yellow-200 @else bg-blue-100 text-blue-800 hover:bg-blue-200 @endif inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md transition duration-150 ease-in-out">
                                {{ $alert['action_text'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Revenue Trend Chart -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Revenue Trend</h3>
                <p class="text-sm text-gray-500">Last 30 days</p>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- Order Trend Chart -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Trend</h3>
                <p class="text-sm text-gray-500">Last 30 days</p>
            </div>
            <div class="p-6">
                <canvas id="orderChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity as $activity)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $activity['user'] }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $activity['action'] }} {{ class_basename($activity['resource']) }}
                                    @if($activity['resource_id'])
                                        #{{ $activity['resource_id'] }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0 text-sm text-gray-400">
                                {{ $activity['timestamp']->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-4 text-center text-gray-500">
                        No recent activity
                    </div>
                @endforelse
            </div>
            @if(count($recentActivity) > 0)
                <div class="px-6 py-3 border-t border-gray-200 text-center">
                    <a href="{{ route('compliance.audit-logs') }}" class="text-sm text-blue-600 hover:text-blue-500">
                        View all activity
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.users.create') }}" 
                       class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Add User</span>
                    </a>

                    <a href="{{ route('admin.inventory.create') }}" 
                       class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Add Product</span>
                    </a>

                    <a href="{{ route('admin.orders.pending') }}" 
                       class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Process Orders</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">View Reports</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch analytics data
    fetch('{{ route("admin.analytics") }}?period=30')
        .then(response => response.json())
        .then(data => {
            initializeCharts(data);
        })
        .catch(error => console.error('Error fetching analytics:', error));

    function initializeCharts(data) {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
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
        new Chart(orderCtx, {
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
    }
});
</script>
@endpush