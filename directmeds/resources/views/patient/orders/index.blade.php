@extends('layouts.patient')

@section('title', 'Order History')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
        Order History
    </h1>
    <p class="mt-2 text-sm text-gray-600">
        Track your medication orders and delivery status
    </p>
</div>

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="#" class="border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                All Orders
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                In Transit
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Delivered
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Cancelled
            </a>
        </nav>
    </div>
</div>

@if($orders->count() > 0)
    <!-- Orders List -->
    <div class="space-y-6">
        @foreach($orders as $order)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Order Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Order #{{ $order->order_number ?? 'N/A' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Placed on {{ $order->created_at->format('F j, Y') }} at {{ $order->created_at->format('g:i A') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' :
                                   ($order->status === 'shipped' ? 'bg-blue-100 text-blue-800' :
                                   ($order->status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                   ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                            <span class="text-lg font-medium text-gray-900">
                                ${{ number_format($order->total_amount ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="px-6 py-4">
                    @if($order->items && $order->items->count() > 0)
                        <div class="space-y-3">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name ?? 'Medication Name' }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                Quantity: {{ $item->quantity ?? 1 }} • ${{ number_format($item->price ?? 0, 2) }} each
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            ${{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-500">
                            Order items information not available
                        </div>
                    @endif
                </div>

                <!-- Order Actions & Status -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            @if($order->status === 'shipped' && $order->tracking_number)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">Tracking:</span>
                                    <span class="font-mono text-blue-600">{{ $order->tracking_number }}</span>
                                </div>
                            @endif
                            @if($order->estimated_delivery_date)
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Expected delivery:</span>
                                    {{ Carbon\Carbon::parse($order->estimated_delivery_date)->format('M j, Y') }}
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($order->status === 'shipped')
                                <a href="#" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Track Package
                                </a>
                            @endif
                            <a href="{{ route('patient.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                View Details
                            </a>
                            @if(in_array($order->status, ['pending', 'processing']) && !$order->prescription_id)
                                <form action="{{ route('patient.orders.cancel', $order) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Progress Bar -->
                @if(in_array($order->status, ['pending', 'processing', 'shipped']))
                    <div class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex items-center flex-1">
                                <!-- Step 1: Order Placed -->
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 bg-green-600 rounded-full">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-xs font-medium text-gray-900">Order Placed</span>
                                </div>

                                <!-- Line -->
                                <div class="flex-1 h-0.5 mx-4 
                                    {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'bg-green-600' : 'bg-gray-300' }}">
                                </div>

                                <!-- Step 2: Processing -->
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 
                                        {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'bg-green-600' : 'bg-gray-300' }} 
                                        rounded-full">
                                        @if(in_array($order->status, ['processing', 'shipped', 'delivered']))
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-white rounded-full"></div>
                                        @endif
                                    </div>
                                    <span class="ml-2 text-xs font-medium 
                                        {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'text-gray-900' : 'text-gray-500' }}">
                                        Processing
                                    </span>
                                </div>

                                <!-- Line -->
                                <div class="flex-1 h-0.5 mx-4 
                                    {{ in_array($order->status, ['shipped', 'delivered']) ? 'bg-green-600' : 'bg-gray-300' }}">
                                </div>

                                <!-- Step 3: Shipped -->
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 
                                        {{ in_array($order->status, ['shipped', 'delivered']) ? 'bg-green-600' : 'bg-gray-300' }} 
                                        rounded-full">
                                        @if(in_array($order->status, ['shipped', 'delivered']))
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-white rounded-full"></div>
                                        @endif
                                    </div>
                                    <span class="ml-2 text-xs font-medium 
                                        {{ in_array($order->status, ['shipped', 'delivered']) ? 'text-gray-900' : 'text-gray-500' }}">
                                        Shipped
                                    </span>
                                </div>

                                <!-- Line -->
                                <div class="flex-1 h-0.5 mx-4 
                                    {{ $order->status === 'delivered' ? 'bg-green-600' : 'bg-gray-300' }}">
                                </div>

                                <!-- Step 4: Delivered -->
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-8 h-8 
                                        {{ $order->status === 'delivered' ? 'bg-green-600' : 'bg-gray-300' }} 
                                        rounded-full">
                                        @if($order->status === 'delivered')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-white rounded-full"></div>
                                        @endif
                                    </div>
                                    <span class="ml-2 text-xs font-medium 
                                        {{ $order->status === 'delivered' ? 'text-gray-900' : 'text-gray-500' }}">
                                        Delivered
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6a2 2 0 002 2h4a2 2 0 002-2v-6M8 11h8" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
        <p class="mt-1 text-sm text-gray-500">When you place orders, they'll appear here.</p>
        <div class="mt-6">
            <a href="{{ route('patient.prescriptions') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
                </svg>
                View My Prescriptions
            </a>
        </div>
    </div>
@endif

<!-- Quick Links -->
<div class="mt-8 bg-gray-50 rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Need Help?</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('patient.messages') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Contact Support</h4>
                <p class="text-sm text-gray-500">Get help with your orders</p>
            </div>
        </a>
        
        <a href="{{ route('patient.refills') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Request Refill</h4>
                <p class="text-sm text-gray-500">Refill existing prescriptions</p>
            </div>
        </a>
        
        <a href="{{ route('patient.help') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Help Center</h4>
                <p class="text-sm text-gray-500">Find answers to common questions</p>
            </div>
        </div>
    </div>
</div>
@endsection