<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Meds API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <style>
        .api-section {
            border-left: 4px solid #3B82F6;
            background: linear-gradient(135deg, #EBF8FF 0%, #F7FAFC 100%);
        }
        .endpoint-card {
            transition: all 0.3s ease;
            border: 1px solid #E5E7EB;
        }
        .endpoint-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .method-get { background-color: #10B981; }
        .method-post { background-color: #3B82F6; }
        .method-put { background-color: #F59E0B; }
        .method-delete { background-color: #EF4444; }
        .sidebar-nav {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-blue-500">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-500 text-white p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Direct Meds API</h1>
                        <p class="text-gray-600">HIPAA-Compliant Pharmacy Management System</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('api.docs.swagger') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Swagger UI
                    </a>
                    <a href="{{ route('api.docs.testing') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        API Testing
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-wrap -mx-6">
            <!-- Sidebar Navigation -->
            <div class="w-full lg:w-1/4 px-6">
                <div class="sidebar-nav bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Navigation</h3>
                    <ul class="space-y-2">
                        <li><a href="#overview" class="block text-blue-600 hover:text-blue-800 transition-colors">Overview</a></li>
                        <li><a href="#authentication" class="block text-blue-600 hover:text-blue-800 transition-colors">Authentication</a></li>
                        <li><a href="#getting-started" class="block text-blue-600 hover:text-blue-800 transition-colors">Getting Started</a></li>
                        <li><a href="#endpoints" class="block text-blue-600 hover:text-blue-800 transition-colors">API Endpoints</a></li>
                        <li><a href="#error-handling" class="block text-blue-600 hover:text-blue-800 transition-colors">Error Handling</a></li>
                        <li><a href="#rate-limiting" class="block text-blue-600 hover:text-blue-800 transition-colors">Rate Limiting</a></li>
                        <li><a href="#compliance" class="block text-blue-600 hover:text-blue-800 transition-colors">HIPAA Compliance</a></li>
                        <li><a href="#examples" class="block text-blue-600 hover:text-blue-800 transition-colors">Code Examples</a></li>
                    </ul>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-3">External Links</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('api.docs.developer-guide') }}" class="text-green-600 hover:text-green-800">Developer Guide</a></li>
                            <li><a href="{{ route('api.docs.authentication-guide') }}" class="text-purple-600 hover:text-purple-800">Auth Guide</a></li>
                            <li><a href="{{ route('api.docs.error-codes') }}" class="text-red-600 hover:text-red-800">Error Codes</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4 px-6">
                <!-- Overview Section -->
                <section id="overview" class="api-section rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">API Overview</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 mb-4">
                            The Direct Meds API is a comprehensive, HIPAA-compliant pharmacy management system that provides secure access to:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 mb-6">
                            <li><strong>Prescription Management:</strong> Upload, review, verify, and dispense prescriptions</li>
                            <li><strong>Product Catalog:</strong> Search and browse pharmaceutical products with drug interaction checking</li>
                            <li><strong>Payment Processing:</strong> Secure payment handling with PCI compliance</li>
                            <li><strong>User Management:</strong> Patient and healthcare provider account management</li>
                            <li><strong>Compliance Features:</strong> HIPAA audit logging and reporting</li>
                        </ul>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Base URL:</strong> <code class="bg-blue-100 px-2 py-1 rounded">{{ config('app.url') }}/api</code>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Authentication Section -->
                <section id="authentication" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Authentication</h2>
                    <p class="text-gray-700 mb-4">
                        The Direct Meds API uses <strong>Laravel Sanctum</strong> for API authentication. Include your bearer token in the Authorization header for all authenticated requests.
                    </p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Authorization Header</h4>
                        <pre class="bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>Authorization: Bearer {your-api-token}</code></pre>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="endpoint-card bg-white rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                <span class="ml-3 font-mono text-sm">/auth/login</span>
                            </div>
                            <p class="text-gray-600">Authenticate and receive an API token</p>
                        </div>
                        
                        <div class="endpoint-card bg-white rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                <span class="ml-3 font-mono text-sm">/auth/register</span>
                            </div>
                            <p class="text-gray-600">Create new user account</p>
                        </div>
                    </div>
                </section>

                <!-- Getting Started Section -->
                <section id="getting-started" class="api-section rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Getting Started</h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">1. Register an Account</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>curl -X POST {{ config('app.url') }}/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "developer@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "user_type": "patient",
    "first_name": "John",
    "last_name": "Doe"
  }'</code></pre>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">2. Authenticate and Get Token</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>curl -X POST {{ config('app.url') }}/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "developer@example.com",
    "password": "SecurePassword123!"
  }'</code></pre>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">3. Make Authenticated Requests</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>curl -X GET {{ config('app.url') }}/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- API Endpoints Section -->
                <section id="endpoints" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">API Endpoints</h2>
                    
                    <!-- Authentication Endpoints -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Authentication</h3>
                        <div class="grid gap-4">
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/auth/register</span>
                                    </div>
                                    <span class="text-xs text-gray-500">Public</span>
                                </div>
                                <p class="text-gray-600 text-sm">Register a new user account</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/auth/login</span>
                                    </div>
                                    <span class="text-xs text-gray-500">Public</span>
                                </div>
                                <p class="text-gray-600 text-sm">Authenticate user and receive API token</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/auth/logout</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">Logout current session</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Endpoints -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Products</h3>
                        <div class="grid gap-4">
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-get text-white px-3 py-1 rounded text-sm font-semibold">GET</span>
                                        <span class="ml-3 font-mono text-sm">/products</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">List products with filtering and search</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-get text-white px-3 py-1 rounded text-sm font-semibold">GET</span>
                                        <span class="ml-3 font-mono text-sm">/products/{id}</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">Get product details by ID, slug, or NDC</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/products/check-interactions</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">Check drug interactions between multiple medications</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prescriptions Endpoints -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Prescriptions</h3>
                        <div class="grid gap-4">
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-get text-white px-3 py-1 rounded text-sm font-semibold">GET</span>
                                        <span class="ml-3 font-mono text-sm">/prescriptions</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">List user prescriptions with filtering</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/prescriptions</span>
                                    </div>
                                    <span class="text-xs text-blue-500">Authenticated</span>
                                </div>
                                <p class="text-gray-600 text-sm">Upload new prescription</p>
                            </div>
                            
                            <div class="endpoint-card bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="method-post text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                        <span class="ml-3 font-mono text-sm">/prescriptions/{id}/verify</span>
                                    </div>
                                    <span class="text-xs text-red-500">Pharmacist</span>
                                </div>
                                <p class="text-gray-600 text-sm">Verify/reject/hold prescription</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Error Handling Section -->
                <section id="error-handling" class="api-section rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Error Handling</h2>
                    <p class="text-gray-700 mb-4">
                        The API uses conventional HTTP status codes and returns JSON error responses with detailed information.
                    </p>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Standard Error Response Format</h4>
                        <pre class="bg-gray-800 text-green-400 p-3 rounded overflow-x-auto"><code>{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "error_code": "VALIDATION_ERROR",
  "timestamp": "2024-01-15T10:30:00Z"
}</code></pre>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4">
                            <h5 class="font-semibold text-red-800">4xx Client Errors</h5>
                            <ul class="text-red-700 text-sm mt-2 space-y-1">
                                <li><strong>400:</strong> Bad Request</li>
                                <li><strong>401:</strong> Unauthorized</li>
                                <li><strong>403:</strong> Forbidden</li>
                                <li><strong>404:</strong> Not Found</li>
                                <li><strong>422:</strong> Validation Error</li>
                            </ul>
                        </div>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h5 class="font-semibold text-yellow-800">5xx Server Errors</h5>
                            <ul class="text-yellow-700 text-sm mt-2 space-y-1">
                                <li><strong>500:</strong> Internal Server Error</li>
                                <li><strong>502:</strong> Bad Gateway</li>
                                <li><strong>503:</strong> Service Unavailable</li>
                                <li><strong>504:</strong> Gateway Timeout</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- HIPAA Compliance Section -->
                <section id="compliance" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">HIPAA Compliance</h2>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Important:</strong> All API access requires HIPAA acknowledgment for endpoints handling Protected Health Information (PHI).
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Audit Logging</h4>
                            <p class="text-gray-700 text-sm">All API requests handling PHI are automatically logged for compliance auditing.</p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Data Retention</h4>
                            <p class="text-gray-700 text-sm">Audit logs and PHI are retained for 7 years in accordance with HIPAA requirements.</p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Required HIPAA Acknowledgment</h4>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <pre class="text-sm"><code>POST /api/hipaa/acknowledge
{
  "acknowledged": true
}</code></pre>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-wrap justify-between">
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-3">Direct Meds API</h3>
                    <p class="text-gray-400">HIPAA-compliant pharmacy management system providing secure access to prescription and healthcare data.</p>
                </div>
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-lg font-semibold mb-3">Resources</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('api.docs.developer-guide') }}" class="hover:text-white transition-colors">Developer Guide</a></li>
                        <li><a href="{{ route('api.docs.authentication-guide') }}" class="hover:text-white transition-colors">Authentication Guide</a></li>
                        <li><a href="{{ route('api.docs.swagger') }}" class="hover:text-white transition-colors">Swagger Documentation</a></li>
                    </ul>
                </div>
                <div class="w-full md:w-1/3">
                    <h3 class="text-lg font-semibold mb-3">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>Email: api-support@directmeds.com</li>
                        <li>API Version: 1.0.0</li>
                        <li>Last Updated: {{ date('Y-m-d') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>