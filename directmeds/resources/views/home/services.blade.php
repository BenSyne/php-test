@extends('layouts.app')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                Our Services
            </h1>
            <p class="mt-6 text-xl text-gray-600 max-w-3xl mx-auto">
                Comprehensive pharmaceutical services designed to meet all your medication needs with convenience, safety, and professional care.
            </p>
        </div>

        <!-- Main Services Grid -->
        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Prescription Fulfillment -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-blue-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Prescription Fulfillment</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Fast, accurate prescription processing with licensed pharmacist review and drug interaction checking.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Licensed pharmacist review
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Drug interaction screening
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Prescription synchronization
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Medication therapy management
                    </li>
                </ul>
            </div>

            <!-- Prescription Transfers -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-green-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Prescription Transfers</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Seamlessly transfer your prescriptions from any pharmacy with our easy online process.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Quick online transfer form
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        We handle all pharmacy communication
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Maintain prescription history
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Same-day processing
                    </li>
                </ul>
            </div>

            <!-- Auto Refills -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-purple-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Automatic Refills</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Never run out of medication with our automatic refill program and delivery scheduling.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Automatic refill reminders
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Scheduled delivery options
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Easy pause/resume controls
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Insurance coordination
                    </li>
                </ul>
            </div>

            <!-- Delivery Services -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-orange-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Fast Delivery</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Multiple delivery options to get your medications when and where you need them.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Free standard shipping (2-3 days)
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Expedited overnight delivery
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Temperature-controlled shipping
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Real-time tracking
                    </li>
                </ul>
            </div>

            <!-- Consultation Services -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-teal-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Pharmacist Consultations</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Direct access to licensed pharmacists for medication questions and counseling.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        24/7 pharmacist support
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Medication counseling
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Side effect guidance
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Drug interaction advice
                    </li>
                </ul>
            </div>

            <!-- Specialty Medications -->
            <div class="bg-white rounded-lg shadow-lg p-8 border border-gray-200">
                <div class="flex items-center justify-center h-16 w-16 rounded-md bg-red-600 text-white mx-auto">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-medium text-gray-900 text-center">Specialty Medications</h3>
                <p class="mt-4 text-gray-600 text-center">
                    Specialized handling and delivery for complex medications requiring special care.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Cold-chain medications
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Injectable medications
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Compounded medications
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Patient support programs
                    </li>
                </ul>
            </div>
        </div>

        <!-- Process Section -->
        <div class="mt-16">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Our Service Process</h2>
                <p class="mt-4 text-xl text-gray-600">
                    Simple, secure, and professional every step of the way
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mx-auto font-bold text-xl">
                        1
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Upload or Transfer</h3>
                    <p class="mt-2 text-gray-600">Upload your prescription or transfer from another pharmacy</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mx-auto font-bold text-xl">
                        2
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Pharmacist Review</h3>
                    <p class="mt-2 text-gray-600">Licensed pharmacist verifies and checks for interactions</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mx-auto font-bold text-xl">
                        3
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Secure Processing</h3>
                    <p class="mt-2 text-gray-600">Medication is prepared in our FDA-compliant facility</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mx-auto font-bold text-xl">
                        4
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Safe Delivery</h3>
                    <p class="mt-2 text-gray-600">Fast, secure delivery directly to your door</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-16 bg-blue-50 rounded-lg p-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900">Ready to Get Started?</h2>
            <p class="mt-4 text-xl text-gray-600">
                Experience the convenience and professional care of Direct Meds today.
            </p>
            <div class="mt-8 space-y-4 sm:space-y-0 sm:space-x-4 sm:flex sm:justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Create Account
                    </a>
                @endauth
                <a href="{{ route('home.contact') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</div>
@endsection