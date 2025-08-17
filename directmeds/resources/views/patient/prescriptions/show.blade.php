@extends('layouts.patient')

@section('title', 'Prescription Details')

@section('content')
<div class="mb-6">
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
            <li>
                <div class="flex">
                    <a href="{{ route('patient.prescriptions') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">My Prescriptions</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-500">Prescription Details</span>
                </div>
            </li>
        </ol>
    </nav>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <!-- Header -->
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $prescription->medication_name ?? 'Medication Name' }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Prescription #{{ $prescription->prescription_number ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                    {{ $prescription->status === 'active' ? 'bg-green-100 text-green-800' : 
                       ($prescription->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($prescription->status ?? 'pending') }}
                </span>
                @if($prescription->status === 'active' && ($prescription->refills_remaining ?? 0) > 0)
                    <form action="{{ route('patient.refills.request', $prescription) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Request Refill
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Medication Details -->
            <div class="lg:col-span-2">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Medication Information</h4>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Medication Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $prescription->medication_name ?? 'Medication Name' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dosage</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $prescription->dosage ?? 'Dosage information' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $prescription->quantity ?? 30 }} tablets/capsules</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Days Supply</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $prescription->days_supply ?? 30 }} days</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Instructions</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $prescription->instructions ?? 'Take as directed by your healthcare provider.' }}</dd>
                    </div>
                    @if($prescription->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Additional Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->notes }}</dd>
                        </div>
                    @endif
                </dl>

                <!-- Prescriber Information -->
                <div class="mt-8">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Prescriber Information</h4>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Doctor Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->prescriber->name ?? 'Dr. Name' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Practice</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->prescriber->practice_name ?? 'Medical Practice' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->prescriber->phone ?? '(555) 123-4567' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">DEA Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->prescriber->dea_number ?? 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Prescription Status & Refills -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Prescription Status</h4>
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $prescription->status === 'active' ? 'bg-green-100 text-green-800' : 
                                       ($prescription->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($prescription->status ?? 'pending') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date Prescribed</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $prescription->created_at->format('M j, Y') }}</dd>
                        </div>
                        @if($prescription->status === 'active')
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Refills Remaining</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $prescription->refills_remaining ?? 0 }} of {{ $prescription->total_refills ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Next Refill Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ($prescription->next_refill_date ?? now())->format('M j, Y') }}</dd>
                            </div>
                            @if(($prescription->next_refill_date ?? now()) <= now()->addDays(7))
                                <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-orange-800">
                                                Refill Available
                                            </h3>
                                            <div class="mt-2 text-sm text-orange-700">
                                                <p>This prescription is ready for refill.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </dl>
                </div>

                <!-- Safety Information -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h5 class="text-sm font-medium text-blue-800 mb-2">Important Safety Information</h5>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Take exactly as prescribed</li>
                        <li>• Don't skip doses or stop early</li>
                        <li>• Report any side effects to your doctor</li>
                        <li>• Keep medications in original containers</li>
                        <li>• Store as directed on label</li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('patient.messages') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            Ask pharmacist a question →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($prescription->auditLogs) && $prescription->auditLogs->count() > 0)
    <!-- Activity Timeline -->
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Prescription History</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Timeline of changes and updates to this prescription</p>
        </div>
        <div class="border-t border-gray-200">
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($prescription->auditLogs as $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                {{ $log->event_description ?? 'Prescription activity' }}
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            {{ $log->created_at->format('M j, Y g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Action Buttons -->
<div class="mt-6 flex justify-end space-x-3">
    <a href="{{ route('patient.prescriptions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Back to Prescriptions
    </a>
    @if($prescription->status === 'active')
        <a href="{{ route('patient.messages') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            Ask Pharmacist
        </a>
    @endif
</div>
@endsection