@extends('layouts.app')

@section('content')
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                About Direct Meds
            </h1>
            <p class="mt-6 text-xl text-gray-600 max-w-3xl mx-auto">
                We're committed to providing safe, reliable, and affordable access to prescription medications with the highest level of professional pharmaceutical care.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="mt-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Our Mission</h2>
                    <p class="mt-4 text-lg text-gray-600">
                        To revolutionize pharmacy services by combining the convenience of online ordering with the trust and expertise of licensed pharmacists, ensuring every patient receives the highest quality pharmaceutical care.
                    </p>
                    <div class="mt-6 space-y-4">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">Patient-First Approach</h3>
                                <p class="mt-1 text-gray-600">Every decision we make prioritizes patient safety, convenience, and well-being.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">Uncompromising Safety</h3>
                                <p class="mt-1 text-gray-600">All medications are sourced from FDA-approved manufacturers with rigorous quality controls.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-blue-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">Innovation & Convenience</h3>
                                <p class="mt-1 text-gray-600">Leveraging technology to make healthcare more accessible and convenient for everyone.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-100 rounded-lg p-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-blue-600 text-white mb-4">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Founded on Trust</h3>
                        <p class="text-gray-600">
                            Established by experienced healthcare professionals who understand the importance of reliable pharmaceutical care.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="mt-16">
            <div class="bg-blue-50 rounded-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-3xl font-bold text-blue-600">10,000+</div>
                        <div class="text-sm text-gray-600 mt-1">Patients Served</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-blue-600">99.9%</div>
                        <div class="text-sm text-gray-600 mt-1">Customer Satisfaction</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-blue-600">24/7</div>
                        <div class="text-sm text-gray-600 mt-1">Support Available</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-blue-600">2-Day</div>
                        <div class="text-sm text-gray-600 mt-1">Average Delivery</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="mt-16">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Our Leadership Team</h2>
                <p class="mt-4 text-xl text-gray-600">
                    Experienced healthcare professionals dedicated to your well-being
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Team Member 1 -->
                <div class="text-center">
                    <div class="h-32 w-32 bg-gray-300 rounded-full mx-auto flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-700">DR</span>
                    </div>
                    <h3 class="mt-4 text-xl font-medium text-gray-900">Dr. Rachel Thompson</h3>
                    <p class="text-blue-600 font-medium">Chief Pharmacist</p>
                    <p class="mt-2 text-gray-600">PharmD, 15+ years experience in clinical pharmacy and pharmaceutical care.</p>
                </div>

                <!-- Team Member 2 -->
                <div class="text-center">
                    <div class="h-32 w-32 bg-gray-300 rounded-full mx-auto flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-700">MJ</span>
                    </div>
                    <h3 class="mt-4 text-xl font-medium text-gray-900">Michael Johnson</h3>
                    <p class="text-blue-600 font-medium">CEO & Co-Founder</p>
                    <p class="mt-2 text-gray-600">Former healthcare executive with 20+ years in pharmaceutical operations and patient care.</p>
                </div>

                <!-- Team Member 3 -->
                <div class="text-center">
                    <div class="h-32 w-32 bg-gray-300 rounded-full mx-auto flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-700">SL</span>
                    </div>
                    <h3 class="mt-4 text-xl font-medium text-gray-900">Sarah Lee</h3>
                    <p class="text-blue-600 font-medium">Chief Technology Officer</p>
                    <p class="mt-2 text-gray-600">Technology leader focused on healthcare innovation and HIPAA-compliant systems.</p>
                </div>
            </div>
        </div>

        <!-- Certifications Section -->
        <div class="mt-16">
            <div class="bg-gray-50 rounded-lg p-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900">Certifications & Compliance</h2>
                    <p class="mt-4 text-xl text-gray-600">
                        Fully licensed and compliant with all regulatory requirements
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center p-4 bg-white rounded-lg shadow">
                        <div class="h-12 w-12 bg-green-100 rounded-lg mx-auto flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 font-medium text-gray-900">FDA Approved</h3>
                        <p class="text-sm text-gray-600">All medications from verified sources</p>
                    </div>

                    <div class="text-center p-4 bg-white rounded-lg shadow">
                        <div class="h-12 w-12 bg-blue-100 rounded-lg mx-auto flex items-center justify-center">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 font-medium text-gray-900">HIPAA Compliant</h3>
                        <p class="text-sm text-gray-600">Full privacy protection</p>
                    </div>

                    <div class="text-center p-4 bg-white rounded-lg shadow">
                        <div class="h-12 w-12 bg-purple-100 rounded-lg mx-auto flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 font-medium text-gray-900">State Licensed</h3>
                        <p class="text-sm text-gray-600">Licensed in all 50 states</p>
                    </div>

                    <div class="text-center p-4 bg-white rounded-lg shadow">
                        <div class="h-12 w-12 bg-yellow-100 rounded-lg mx-auto flex items-center justify-center">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 font-medium text-gray-900">24/7 Monitoring</h3>
                        <p class="text-sm text-gray-600">Continuous security oversight</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-16 text-center">
            <h2 class="text-3xl font-bold text-gray-900">Ready to Experience Better Pharmacy Care?</h2>
            <p class="mt-4 text-xl text-gray-600">
                Join thousands of patients who trust Direct Meds for their medication needs.
            </p>
            <div class="mt-8">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Get Started Today
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection