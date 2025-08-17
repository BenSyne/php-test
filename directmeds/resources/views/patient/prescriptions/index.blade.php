@extends('layouts.patient')

@section('title', 'My Prescriptions')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            My Prescriptions
        </h1>
        <p class="mt-2 text-sm text-gray-600">
            Manage your active prescriptions and medication history
        </p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('patient.upload-prescription') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Upload Prescription
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="#" class="border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                All Prescriptions
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Active
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Expired
            </a>
            <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Pending
            </a>
        </nav>
    </div>
</div>

@if($prescriptions->count() > 0)
    <!-- Prescriptions List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @foreach($prescriptions as $prescription)
                <li>
                    <div class="px-4 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center flex-1">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-lg flex items-center justify-center
                                    {{ $prescription->status === 'active' ? 'bg-green-100' : 
                                       ($prescription->status === 'pending' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                                    <svg class="h-6 w-6 
                                        {{ $prescription->status === 'active' ? 'text-green-600' : 
                                           ($prescription->status === 'pending' ? 'text-yellow-600' : 'text-gray-600') }}" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">
                                            {{ $prescription->medication_name ?? 'Medication Name' }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $prescription->dosage ?? 'Dosage information' }} - {{ $prescription->instructions ?? 'Take as directed' }}
                                        </p>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <span>Prescribed by {{ $prescription->prescriber->name ?? 'Dr. Name' }}</span>
                                            <span class="mx-2">•</span>
                                            <span>{{ $prescription->created_at->format('M j, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $prescription->status === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($prescription->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($prescription->status ?? 'pending') }}
                                    </span>
                                    @if($prescription->status === 'active')
                                        <span class="ml-4 text-gray-500">
                                            {{ $prescription->refills_remaining ?? 0 }} refills remaining
                                        </span>
                                        @if(($prescription->next_refill_date ?? now()) <= now()->addDays(7))
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                Due for refill
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($prescription->status === 'active' && ($prescription->refills_remaining ?? 0) > 0)
                                <form action="{{ route('patient.refills.request', $prescription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Request Refill
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('patient.prescriptions.show', $prescription) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                View Details
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Pagination -->
    @if($prescriptions->hasPages())
        <div class="mt-6">
            {{ $prescriptions->links() }}
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No prescriptions</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by uploading your first prescription.</p>
        <div class="mt-6">
            <a href="{{ route('patient.upload-prescription') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Upload Prescription
            </a>
        </div>
    </div>
@endif

<!-- Quick Actions -->
<div class="mt-8 bg-gray-50 rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('patient.upload-prescription') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Upload New Prescription</h4>
                <p class="text-sm text-gray-500">Add a new prescription from your doctor</p>
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
                <h4 class="text-sm font-medium text-gray-900">Request Refills</h4>
                <p class="text-sm text-gray-500">Refill your existing medications</p>
            </div>
        </a>
        
        <a href="{{ route('patient.messages') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Ask Pharmacist</h4>
                <p class="text-sm text-gray-500">Get medication advice from experts</p>
            </div>
        </a>
    </div>
</div>
@endsection