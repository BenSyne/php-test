@extends('layouts.patient')

@section('title', 'My Profile')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            My Profile
        </h1>
        <p class="mt-2 text-sm text-gray-600">
            Manage your personal information and account settings
        </p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('patient.profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Profile
        </a>
    </div>
</div>

<!-- Profile Overview -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Personal Information</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Your account and contact details</p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Full name</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Email address</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="flex items-center">
                        {{ $user->email }}
                        @if($user->email_verified_at)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="-ml-1 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Verified
                            </span>
                        @else
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Unverified
                            </span>
                        @endif
                    </div>
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Phone number</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $user->profile->phone ?? 'Not provided' }}
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Date of birth</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $user->profile->date_of_birth ? Carbon\Carbon::parse($user->profile->date_of_birth)->format('F j, Y') : 'Not provided' }}
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @if($user->profile && ($user->profile->address_line_1 || $user->profile->city))
                        <div class="space-y-1">
                            @if($user->profile->address_line_1)
                                <div>{{ $user->profile->address_line_1 }}</div>
                            @endif
                            @if($user->profile->address_line_2)
                                <div>{{ $user->profile->address_line_2 }}</div>
                            @endif
                            @if($user->profile->city || $user->profile->state || $user->profile->zip_code)
                                <div>
                                    {{ $user->profile->city }}@if($user->profile->city && ($user->profile->state || $user->profile->zip_code)), @endif{{ $user->profile->state }} {{ $user->profile->zip_code }}
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-500">Not provided</span>
                    @endif
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Account type</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($user->user_type ?? 'patient') }}
                    </span>
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Member since</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $user->created_at->format('F j, Y') }}
                </dd>
            </div>
        </dl>
    </div>
</div>

<!-- Account Security -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Account Security</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage your account security settings</p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
        <dl class="sm:divide-y sm:divide-gray-200">
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Two-Factor Authentication</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($user->two_factor_secret)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="-ml-1 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Disabled
                                </span>
                            @endif
                            <span class="ml-2 text-sm text-gray-500">
                                {{ $user->two_factor_secret ? 'Your account is protected with 2FA' : 'Add an extra layer of security' }}
                            </span>
                        </div>
                        <a href="{{ route('2fa.show') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            {{ $user->two_factor_secret ? 'Manage' : 'Enable' }}
                        </a>
                    </div>
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Password</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Last changed: Unknown</span>
                        <button class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            Change Password
                        </button>
                    </div>
                </dd>
            </div>
            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">HIPAA Acknowledgment</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="flex items-center">
                        @if($user->hipaa_acknowledged_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="-ml-1 mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Acknowledged
                            </span>
                            <span class="ml-2 text-sm text-gray-500">
                                on {{ $user->hipaa_acknowledged_at->format('M j, Y') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Required
                            </span>
                        @endif
                    </div>
                </dd>
            </div>
        </dl>
    </div>
</div>

<!-- Account Statistics -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Account Activity</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Overview of your account usage</p>
    </div>
    <div class="border-t border-gray-200">
        <div class="grid grid-cols-1 divide-y divide-gray-200 sm:grid-cols-3 sm:divide-y-0 sm:divide-x">
            <div class="px-6 py-5 text-center">
                <span class="text-2xl font-bold text-gray-900">{{ $activePrescriptions ?? 3 }}</span>
                <div class="mt-1 text-sm text-gray-500">Active Prescriptions</div>
            </div>
            <div class="px-6 py-5 text-center">
                <span class="text-2xl font-bold text-gray-900">{{ $totalOrders ?? 12 }}</span>
                <div class="mt-1 text-sm text-gray-500">Total Orders</div>
            </div>
            <div class="px-6 py-5 text-center">
                <span class="text-2xl font-bold text-gray-900">{{ $refillsDue ?? 2 }}</span>
                <div class="mt-1 text-sm text-gray-500">Refills Due</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-gray-50 rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('patient.profile.edit') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Edit Profile</h4>
                <p class="text-sm text-gray-500">Update your information</p>
            </div>
        </a>
        
        <a href="{{ route('2fa.show') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Security Settings</h4>
                <p class="text-sm text-gray-500">Manage 2FA and passwords</p>
            </div>
        </a>
        
        <a href="{{ route('patient.prescriptions') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">My Prescriptions</h4>
                <p class="text-sm text-gray-500">View active medications</p>
            </div>
        </a>
        
        <a href="{{ route('patient.messages') }}" class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-medium text-gray-900">Contact Support</h4>
                <p class="text-sm text-gray-500">Get help and answers</p>
            </div>
        </a>
    </div>
</div>
@endsection