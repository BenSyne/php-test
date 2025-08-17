<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing Interface - Direct Meds API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .request-method {
            width: 80px;
            font-weight: bold;
            font-size: 12px;
        }
        .method-GET { background-color: #10B981; }
        .method-POST { background-color: #3B82F6; }
        .method-PUT { background-color: #F59E0B; }
        .method-DELETE { background-color: #EF4444; }
        .endpoint-section { border-left: 4px solid #E5E7EB; }
        .endpoint-section.active { border-left-color: #3B82F6; background-color: #EBF8FF; }
        .response-viewer { background-color: #1A202C; color: #E2E8F0; }
        .json-viewer { font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; }
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
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">API Testing Interface</h1>
                        <p class="text-gray-600">Interactive testing for Direct Meds API endpoints</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('api.docs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Back to Docs
                    </a>
                    <button id="clear-all" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        <!-- Authentication Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Authentication</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Token</label>
                    <div class="flex">
                        <input type="password" id="api-token" placeholder="Enter your API token" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button id="toggle-token" class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300">
                            👁️
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Token will be stored locally for this session</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Login</label>
                    <div class="flex space-x-2">
                        <input type="email" id="login-email" placeholder="Email" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="password" id="login-password" placeholder="Password" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button id="quick-login" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Login
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="auth-status" class="mt-4 p-3 rounded-lg hidden">
                <span id="auth-message"></span>
            </div>
        </div>

        <!-- API Testing Interface -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900">API Endpoints</h2>
                <p class="text-gray-600 mt-1">Click on any endpoint to test it interactively</p>
            </div>

            <div class="flex">
                <!-- Sidebar with endpoints -->
                <div class="w-1/3 border-r border-gray-200 overflow-y-auto max-h-screen">
                    <!-- Authentication Endpoints -->
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3">Authentication</h3>
                        <div class="space-y-2">
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="POST" data-endpoint="/auth/register" data-auth="false">
                                <div class="flex items-center">
                                    <span class="request-method method-POST text-white px-2 py-1 rounded text-xs">POST</span>
                                    <span class="ml-2 text-sm">/auth/register</span>
                                </div>
                            </button>
                            
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="POST" data-endpoint="/auth/login" data-auth="false">
                                <div class="flex items-center">
                                    <span class="request-method method-POST text-white px-2 py-1 rounded text-xs">POST</span>
                                    <span class="ml-2 text-sm">/auth/login</span>
                                </div>
                            </button>
                            
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="POST" data-endpoint="/auth/logout" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-POST text-white px-2 py-1 rounded text-xs">POST</span>
                                    <span class="ml-2 text-sm">/auth/logout</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- User Endpoints -->
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3">User</h3>
                        <div class="space-y-2">
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/user" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/user</span>
                                </div>
                            </button>
                            
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/profile" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/profile</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Products Endpoints -->
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3">Products</h3>
                        <div class="space-y-2">
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/products" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/products</span>
                                </div>
                            </button>
                            
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/products/search" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/products/search</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Prescriptions Endpoints -->
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3">Prescriptions</h3>
                        <div class="space-y-2">
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/prescriptions" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/prescriptions</span>
                                </div>
                            </button>
                            
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="POST" data-endpoint="/prescriptions" data-auth="true">
                                <div class="flex items-center">
                                    <span class="request-method method-POST text-white px-2 py-1 rounded text-xs">POST</span>
                                    <span class="ml-2 text-sm">/prescriptions</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- System Endpoints -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">System</h3>
                        <div class="space-y-2">
                            <button class="endpoint-btn w-full text-left p-2 rounded hover:bg-gray-50" 
                                    data-method="GET" data-endpoint="/health" data-auth="false">
                                <div class="flex items-center">
                                    <span class="request-method method-GET text-white px-2 py-1 rounded text-xs">GET</span>
                                    <span class="ml-2 text-sm">/health</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main testing area -->
                <div class="w-2/3 p-6">
                    <div id="endpoint-tester" class="hidden">
                        <!-- Request Builder -->
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <span id="current-method" class="request-method text-white px-3 py-1 rounded text-sm font-bold">GET</span>
                                <span id="current-endpoint" class="ml-3 font-mono text-lg">/api/health</span>
                            </div>

                            <!-- Parameters -->
                            <div id="parameters-section" class="mb-4 hidden">
                                <h4 class="font-semibold text-gray-900 mb-2">Parameters</h4>
                                <div id="parameters-container" class="space-y-2">
                                    <!-- Dynamic parameters will be added here -->
                                </div>
                                <button id="add-parameter" class="text-blue-500 hover:text-blue-700 text-sm">+ Add Parameter</button>
                            </div>

                            <!-- Request Body -->
                            <div id="body-section" class="mb-4 hidden">
                                <h4 class="font-semibold text-gray-900 mb-2">Request Body (JSON)</h4>
                                <textarea id="request-body" class="w-full h-32 p-3 border border-gray-300 rounded-lg font-mono text-sm"
                                          placeholder='{\n  "key": "value"\n}'></textarea>
                            </div>

                            <!-- Headers -->
                            <div class="mb-4">
                                <h4 class="font-semibold text-gray-900 mb-2">Headers</h4>
                                <div id="headers-container" class="space-y-2">
                                    <!-- Headers will be populated here -->
                                </div>
                            </div>

                            <!-- Send Request Button -->
                            <button id="send-request" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                                Send Request
                            </button>
                        </div>

                        <!-- Response Viewer -->
                        <div id="response-section" class="hidden">
                            <h4 class="font-semibold text-gray-900 mb-3">Response</h4>
                            
                            <!-- Response Status -->
                            <div class="flex items-center mb-3">
                                <span class="text-sm font-medium text-gray-700 mr-2">Status:</span>
                                <span id="response-status" class="px-2 py-1 rounded text-sm font-bold"></span>
                                <span class="text-sm font-medium text-gray-700 ml-4 mr-2">Time:</span>
                                <span id="response-time" class="text-sm text-gray-600"></span>
                            </div>

                            <!-- Response Headers -->
                            <div class="mb-4">
                                <button id="toggle-response-headers" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                    Show Response Headers
                                </button>
                                <div id="response-headers" class="hidden mt-2 p-3 bg-gray-100 rounded text-sm font-mono"></div>
                            </div>

                            <!-- Response Body -->
                            <div class="response-viewer p-4 rounded-lg">
                                <pre id="response-body" class="json-viewer text-sm overflow-x-auto"></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Default message -->
                    <div id="default-message" class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.906-1.476L3 21l2.524-5.094A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Select an endpoint to test</h3>
                        <p class="text-gray-600">Choose an endpoint from the left sidebar to start testing the API</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        class APITester {
            constructor() {
                this.baseURL = '{{ config('app.url') }}/api';
                this.token = localStorage.getItem('directmeds_api_token');
                this.currentEndpoint = null;
                this.currentMethod = null;
                
                this.initializeEventListeners();
                this.updateTokenDisplay();
            }

            initializeEventListeners() {
                // Token management
                document.getElementById('toggle-token').addEventListener('click', this.toggleTokenVisibility.bind(this));
                document.getElementById('api-token').addEventListener('input', this.updateToken.bind(this));
                document.getElementById('quick-login').addEventListener('click', this.quickLogin.bind(this));
                
                // Endpoint selection
                document.querySelectorAll('.endpoint-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => this.selectEndpoint(e.currentTarget));
                });
                
                // Request sending
                document.getElementById('send-request').addEventListener('click', this.sendRequest.bind(this));
                
                // Response headers toggle
                document.getElementById('toggle-response-headers').addEventListener('click', this.toggleResponseHeaders.bind(this));
                
                // Clear all
                document.getElementById('clear-all').addEventListener('click', this.clearAll.bind(this));
                
                // Add parameter button
                document.getElementById('add-parameter').addEventListener('click', this.addParameter.bind(this));
            }

            toggleTokenVisibility() {
                const tokenInput = document.getElementById('api-token');
                const toggleBtn = document.getElementById('toggle-token');
                
                if (tokenInput.type === 'password') {
                    tokenInput.type = 'text';
                    toggleBtn.textContent = '🙈';
                } else {
                    tokenInput.type = 'password';
                    toggleBtn.textContent = '👁️';
                }
            }

            updateToken() {
                const token = document.getElementById('api-token').value;
                this.token = token;
                localStorage.setItem('directmeds_api_token', token);
                this.updateTokenDisplay();
            }

            updateTokenDisplay() {
                const tokenInput = document.getElementById('api-token');
                if (this.token) {
                    tokenInput.value = this.token;
                    this.showAuthStatus('Token loaded', 'success');
                }
            }

            async quickLogin() {
                const email = document.getElementById('login-email').value;
                const password = document.getElementById('login-password').value;
                
                if (!email || !password) {
                    this.showAuthStatus('Please enter email and password', 'error');
                    return;
                }

                try {
                    const response = await fetch(`${this.baseURL}/auth/login`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, password })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.token = data.token;
                        document.getElementById('api-token').value = this.token;
                        localStorage.setItem('directmeds_api_token', this.token);
                        this.showAuthStatus(`Logged in as ${data.user.email}`, 'success');
                        
                        // Clear login fields
                        document.getElementById('login-email').value = '';
                        document.getElementById('login-password').value = '';
                    } else {
                        this.showAuthStatus(data.message || 'Login failed', 'error');
                    }
                } catch (error) {
                    this.showAuthStatus('Network error during login', 'error');
                }
            }

            showAuthStatus(message, type) {
                const statusDiv = document.getElementById('auth-status');
                const messageSpan = document.getElementById('auth-message');
                
                statusDiv.className = `mt-4 p-3 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                messageSpan.textContent = message;
                statusDiv.classList.remove('hidden');
                
                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 5000);
            }

            selectEndpoint(button) {
                // Remove active class from all endpoints
                document.querySelectorAll('.endpoint-btn').forEach(btn => {
                    btn.parentElement.classList.remove('endpoint-section', 'active');
                });
                
                // Add active class to selected endpoint
                button.parentElement.classList.add('endpoint-section', 'active');
                
                const method = button.dataset.method;
                const endpoint = button.dataset.endpoint;
                const requiresAuth = button.dataset.auth === 'true';
                
                this.currentMethod = method;
                this.currentEndpoint = endpoint;
                
                this.setupEndpointTester(method, endpoint, requiresAuth);
            }

            setupEndpointTester(method, endpoint, requiresAuth) {
                // Show tester, hide default message
                document.getElementById('endpoint-tester').classList.remove('hidden');
                document.getElementById('default-message').classList.add('hidden');
                
                // Update method and endpoint display
                const methodSpan = document.getElementById('current-method');
                methodSpan.textContent = method;
                methodSpan.className = `request-method method-${method} text-white px-3 py-1 rounded text-sm font-bold`;
                
                document.getElementById('current-endpoint').textContent = `/api${endpoint}`;
                
                // Show/hide sections based on method
                if (method === 'GET') {
                    document.getElementById('body-section').classList.add('hidden');
                    document.getElementById('parameters-section').classList.remove('hidden');
                } else {
                    document.getElementById('body-section').classList.remove('hidden');
                    document.getElementById('parameters-section').classList.add('hidden');
                }
                
                // Setup headers
                this.setupHeaders(requiresAuth);
                
                // Setup default body for POST/PUT requests
                if (method !== 'GET') {
                    this.setupDefaultBody(endpoint);
                }
                
                // Clear previous response
                document.getElementById('response-section').classList.add('hidden');
            }

            setupHeaders(requiresAuth) {
                const headersContainer = document.getElementById('headers-container');
                headersContainer.innerHTML = '';
                
                // Content-Type header
                const contentTypeDiv = document.createElement('div');
                contentTypeDiv.className = 'flex items-center space-x-2';
                contentTypeDiv.innerHTML = `
                    <input type="text" value="Content-Type" class="w-1/3 px-2 py-1 border border-gray-300 rounded text-sm" readonly>
                    <input type="text" value="application/json" class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" readonly>
                `;
                headersContainer.appendChild(contentTypeDiv);
                
                // Authorization header
                if (requiresAuth) {
                    const authDiv = document.createElement('div');
                    authDiv.className = 'flex items-center space-x-2';
                    authDiv.innerHTML = `
                        <input type="text" value="Authorization" class="w-1/3 px-2 py-1 border border-gray-300 rounded text-sm" readonly>
                        <input type="text" value="Bearer ${this.token || '[TOKEN_REQUIRED]'}" class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" readonly>
                    `;
                    headersContainer.appendChild(authDiv);
                }
            }

            setupDefaultBody(endpoint) {
                const bodyTextarea = document.getElementById('request-body');
                let defaultBody = '';
                
                switch (endpoint) {
                    case '/auth/register':
                        defaultBody = JSON.stringify({
                            email: "test@example.com",
                            password: "TestPassword123!",
                            password_confirmation: "TestPassword123!",
                            user_type: "patient",
                            first_name: "Test",
                            last_name: "User"
                        }, null, 2);
                        break;
                    case '/auth/login':
                        defaultBody = JSON.stringify({
                            email: "test@example.com",
                            password: "TestPassword123!"
                        }, null, 2);
                        break;
                    case '/prescriptions':
                        defaultBody = JSON.stringify({
                            patient_name: "John Doe",
                            prescriber_name: "Dr. Smith",
                            notes: "Test prescription upload"
                        }, null, 2);
                        break;
                    default:
                        defaultBody = JSON.stringify({}, null, 2);
                }
                
                bodyTextarea.value = defaultBody;
            }

            addParameter() {
                const container = document.getElementById('parameters-container');
                const paramDiv = document.createElement('div');
                paramDiv.className = 'flex items-center space-x-2';
                paramDiv.innerHTML = `
                    <input type="text" placeholder="Parameter name" class="w-1/3 px-2 py-1 border border-gray-300 rounded text-sm">
                    <input type="text" placeholder="Parameter value" class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                    <button class="text-red-500 hover:text-red-700 text-sm" onclick="this.parentElement.remove()">Remove</button>
                `;
                container.appendChild(paramDiv);
            }

            async sendRequest() {
                if (!this.currentEndpoint) return;
                
                const startTime = Date.now();
                
                try {
                    let url = `${this.baseURL}${this.currentEndpoint}`;
                    const headers = { 'Content-Type': 'application/json' };
                    
                    // Add authorization header if token exists
                    if (this.token) {
                        headers['Authorization'] = `Bearer ${this.token}`;
                    }
                    
                    // Build request options
                    const options = {
                        method: this.currentMethod,
                        headers: headers
                    };
                    
                    // Add parameters for GET requests
                    if (this.currentMethod === 'GET') {
                        const params = this.getParameters();
                        if (params.length > 0) {
                            const urlParams = new URLSearchParams();
                            params.forEach(param => {
                                if (param.name && param.value) {
                                    urlParams.append(param.name, param.value);
                                }
                            });
                            url += '?' + urlParams.toString();
                        }
                    }
                    
                    // Add body for POST/PUT requests
                    if (this.currentMethod !== 'GET') {
                        const bodyText = document.getElementById('request-body').value;
                        if (bodyText.trim()) {
                            try {
                                JSON.parse(bodyText); // Validate JSON
                                options.body = bodyText;
                            } catch (error) {
                                throw new Error('Invalid JSON in request body');
                            }
                        }
                    }
                    
                    // Send request
                    const response = await fetch(url, options);
                    const responseTime = Date.now() - startTime;
                    
                    // Parse response
                    let responseData;
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        responseData = await response.json();
                    } else {
                        responseData = await response.text();
                    }
                    
                    this.displayResponse(response, responseData, responseTime);
                    
                } catch (error) {
                    const responseTime = Date.now() - startTime;
                    this.displayError(error, responseTime);
                }
            }

            getParameters() {
                const paramInputs = document.querySelectorAll('#parameters-container > div');
                const params = [];
                
                paramInputs.forEach(div => {
                    const nameInput = div.querySelector('input:first-child');
                    const valueInput = div.querySelector('input:nth-child(2)');
                    
                    if (nameInput && valueInput) {
                        params.push({
                            name: nameInput.value,
                            value: valueInput.value
                        });
                    }
                });
                
                return params;
            }

            displayResponse(response, data, responseTime) {
                // Show response section
                document.getElementById('response-section').classList.remove('hidden');
                
                // Update status
                const statusSpan = document.getElementById('response-status');
                statusSpan.textContent = `${response.status} ${response.statusText}`;
                statusSpan.className = `px-2 py-1 rounded text-sm font-bold ${
                    response.status < 400 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`;
                
                // Update response time
                document.getElementById('response-time').textContent = `${responseTime}ms`;
                
                // Update response headers
                const headersDiv = document.getElementById('response-headers');
                const headersList = [];
                response.headers.forEach((value, key) => {
                    headersList.push(`${key}: ${value}`);
                });
                headersDiv.textContent = headersList.join('\n');
                
                // Update response body
                const responseBody = document.getElementById('response-body');
                if (typeof data === 'object') {
                    responseBody.textContent = JSON.stringify(data, null, 2);
                } else {
                    responseBody.textContent = data;
                }
            }

            displayError(error, responseTime) {
                document.getElementById('response-section').classList.remove('hidden');
                
                const statusSpan = document.getElementById('response-status');
                statusSpan.textContent = 'Error';
                statusSpan.className = 'px-2 py-1 rounded text-sm font-bold bg-red-100 text-red-800';
                
                document.getElementById('response-time').textContent = `${responseTime}ms`;
                document.getElementById('response-headers').textContent = '';
                document.getElementById('response-body').textContent = error.message;
            }

            toggleResponseHeaders() {
                const headersDiv = document.getElementById('response-headers');
                const toggleBtn = document.getElementById('toggle-response-headers');
                
                if (headersDiv.classList.contains('hidden')) {
                    headersDiv.classList.remove('hidden');
                    toggleBtn.textContent = 'Hide Response Headers';
                } else {
                    headersDiv.classList.add('hidden');
                    toggleBtn.textContent = 'Show Response Headers';
                }
            }

            clearAll() {
                // Clear token
                this.token = null;
                localStorage.removeItem('directmeds_api_token');
                document.getElementById('api-token').value = '';
                
                // Clear login fields
                document.getElementById('login-email').value = '';
                document.getElementById('login-password').value = '';
                
                // Clear endpoint selection
                document.querySelectorAll('.endpoint-btn').forEach(btn => {
                    btn.parentElement.classList.remove('endpoint-section', 'active');
                });
                
                // Hide tester, show default message
                document.getElementById('endpoint-tester').classList.add('hidden');
                document.getElementById('default-message').classList.remove('hidden');
                
                this.showAuthStatus('All data cleared', 'success');
            }
        }

        // Initialize API Tester when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new APITester();
        });
    </script>
</body>
</html>