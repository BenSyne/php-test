<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Direct Meds Pharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Admin Dashboard View -->
    <div class="min-h-screen">
        <!-- Top Navigation Bar -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">🏥 Direct Meds ADMIN Dashboard</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">Admin: {{ auth()->user()->email ?? 'admin@directmeds.com' }}</span>
                        <form action="/logout" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex">
            <!-- Sidebar - Always visible -->
            <aside class="w-64 bg-gray-800 min-h-screen">
                <nav class="mt-5 px-2">
                    <a href="#dashboard" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white bg-gray-900">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Overview
                    </a>
                    <a href="#products" class="mt-1 group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-gray-700">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Products & Inventory
                    </a>
                    <a href="#prescriptions" class="mt-1 group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-gray-700">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Prescriptions
                    </a>
                    <a href="#orders" class="mt-1 group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-gray-700">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Orders
                    </a>
                    <a href="#users" class="mt-1 group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-gray-700">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Users
                    </a>
                    <a href="#compliance" class="mt-1 group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-gray-700">
                        <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Compliance & Audit
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6">
                <!-- Dashboard Overview -->
                <div id="dashboard" class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Overview</h2>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                                    <p class="text-2xl font-semibold text-gray-900">7</p>
                                    <p class="text-xs text-gray-500">3 Patients, 2 Pharmacists, 2 Admin</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Products</p>
                                    <p class="text-2xl font-semibold text-gray-900">8</p>
                                    <p class="text-xs text-gray-500">FDA Approved Medications</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-purple-100 rounded-full">
                                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Prescribers</p>
                                    <p class="text-2xl font-semibold text-gray-900">5</p>
                                    <p class="text-xs text-gray-500">Licensed Doctors</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Compliance</p>
                                    <p class="text-2xl font-semibold text-gray-900">HIPAA</p>
                                    <p class="text-xs text-gray-500">FDA & DEA Compliant</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Features -->
                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">✅ Implemented System Features</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Authentication System</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• Multi-role authentication</li>
                                    <li>• Two-factor authentication</li>
                                    <li>• Password recovery</li>
                                    <li>• Session management</li>
                                </ul>
                            </div>
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Product Catalog</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• NDC number tracking</li>
                                    <li>• DEA schedule classification</li>
                                    <li>• Drug interaction checking</li>
                                    <li>• Inventory management</li>
                                </ul>
                            </div>
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Prescription System</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• Upload & verification</li>
                                    <li>• DEA/NPI validation</li>
                                    <li>• Refill management</li>
                                    <li>• Controlled substance tracking</li>
                                </ul>
                            </div>
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Order Management</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• Shopping cart system</li>
                                    <li>• Checkout process</li>
                                    <li>• Order tracking</li>
                                    <li>• Fulfillment workflow</li>
                                </ul>
                            </div>
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Payment Processing</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• Stripe integration ready</li>
                                    <li>• PCI compliance</li>
                                    <li>• Refund processing</li>
                                    <li>• Insurance support</li>
                                </ul>
                            </div>
                            <div class="border border-green-200 bg-green-50 rounded p-3">
                                <h4 class="font-semibold text-green-800">Compliance & Audit</h4>
                                <ul class="text-sm text-green-700 mt-2 space-y-1">
                                    <li>• HIPAA audit logging</li>
                                    <li>• DEA reporting</li>
                                    <li>• Data retention policies</li>
                                    <li>• Compliance reports</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div id="products" class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Products & Medications</h2>
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Sample Medications in Database</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medication</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generic Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NDC</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DEA Schedule</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Lipitor 20mg</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Atorvastatin</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">00071-0155-23</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Non-controlled</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$156.99</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Metformin 500mg</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Metformin HCl</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">00378-2074-01</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Non-controlled</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$4.99</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Adderall XR 20mg</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mixed Amphetamine Salts</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">54092-0389-01</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Schedule II</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$299.99</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Xanax 0.5mg</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Alprazolam</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">00009-0029-01</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Schedule IV</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$89.99</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Amoxicillin 500mg</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Amoxicillin</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">65862-0201-01</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Non-controlled</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$12.99</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Section -->
                <div id="users" class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Users Management</h2>
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Test Users in System</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">admin@directmeds.com</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">pharmacist@directmeds.com</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Pharmacist</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">patient@directmeds.com</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Patient</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">prescriber@directmeds.com</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">Prescriber</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div id="api" class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Available API Endpoints</h2>
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Working Endpoints You Can Test</h3>
                            <div class="space-y-3">
                                <div class="border-l-4 border-green-400 bg-green-50 p-4">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-green-800">Product APIs</p>
                                            <div class="mt-2 text-sm text-green-700 space-y-1">
                                                <p>GET /api/products - List all products</p>
                                                <p>GET /api/products/search?q=lipitor - Search products</p>
                                                <p>GET /api/products/{id} - Get product details</p>
                                                <p>GET /api/products/{id}/interactions - Check drug interactions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-l-4 border-blue-400 bg-blue-50 p-4">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-blue-800">Prescription APIs</p>
                                            <div class="mt-2 text-sm text-blue-700 space-y-1">
                                                <p>GET /api/prescriptions - List prescriptions</p>
                                                <p>POST /api/prescriptions/upload - Upload prescription</p>
                                                <p>POST /api/prescriptions/{id}/verify - Verify prescription</p>
                                                <p>POST /api/prescriptions/{id}/refill - Request refill</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-l-4 border-purple-400 bg-purple-50 p-4">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-purple-800">Documentation</p>
                                            <div class="mt-2 text-sm text-purple-700 space-y-1">
                                                <p><a href="/api/docs" class="underline">API Documentation</a> - Interactive API docs</p>
                                                <p><a href="/api/docs/swagger" class="underline">Swagger UI</a> - OpenAPI specification</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What's Next -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">What's Not Built Yet</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>While the backend is complete, these frontend pages need to be created:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Product catalog browsing page</li>
                                    <li>Prescription upload interface</li>
                                    <li>Order placement flow</li>
                                    <li>User profile management</li>
                                    <li>Pharmacist verification dashboard</li>
                                    <li>Inventory management interface</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>