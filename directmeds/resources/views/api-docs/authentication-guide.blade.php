<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Guide - Direct Meds API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <style>
        .code-section { background: #2d3748; }
        .auth-flow-step { border: 2px solid #E5E7EB; border-radius: 8px; transition: all 0.3s ease; }
        .auth-flow-step:hover { border-color: #8B5CF6; transform: translateY(-2px); }
        .sidebar-nav { position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-purple-500">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-purple-500 text-white p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Authentication Guide</h1>
                        <p class="text-gray-600">Complete guide to Direct Meds API authentication</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('api.docs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Back to Docs
                    </a>
                    <a href="{{ route('api.docs.testing') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Test API
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Authentication Topics</h3>
                    <ul class="space-y-2">
                        <li><a href="#overview" class="block text-purple-600 hover:text-purple-800 transition-colors">Overview</a></li>
                        <li><a href="#token-auth" class="block text-purple-600 hover:text-purple-800 transition-colors">Token Authentication</a></li>
                        <li><a href="#registration" class="block text-purple-600 hover:text-purple-800 transition-colors">User Registration</a></li>
                        <li><a href="#login-flow" class="block text-purple-600 hover:text-purple-800 transition-colors">Login Flow</a></li>
                        <li><a href="#two-factor" class="block text-purple-600 hover:text-purple-800 transition-colors">Two-Factor Auth</a></li>
                        <li><a href="#token-management" class="block text-purple-600 hover:text-purple-800 transition-colors">Token Management</a></li>
                        <li><a href="#hipaa-compliance" class="block text-purple-600 hover:text-purple-800 transition-colors">HIPAA Compliance</a></li>
                        <li><a href="#security-best-practices" class="block text-purple-600 hover:text-purple-800 transition-colors">Security Practices</a></li>
                        <li><a href="#troubleshooting" class="block text-purple-600 hover:text-purple-800 transition-colors">Troubleshooting</a></li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4 px-6">
                <!-- Overview Section -->
                <section id="overview" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Authentication Overview</h2>
                    <p class="text-gray-700 mb-6">
                        The Direct Meds API uses <strong>Laravel Sanctum</strong> for stateless API authentication. This provides secure, 
                        token-based authentication suitable for SPAs, mobile applications, and simple token-based APIs.
                    </p>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                            <h3 class="font-semibold text-purple-800 mb-2">Authentication Methods</h3>
                            <ul class="text-purple-700 text-sm space-y-1">
                                <li>• Bearer Token (Sanctum)</li>
                                <li>• Two-Factor Authentication</li>
                                <li>• HIPAA Compliance Required</li>
                                <li>• Role-Based Permissions</li>
                            </ul>
                        </div>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Supported User Types</h3>
                            <ul class="text-blue-700 text-sm space-y-1">
                                <li>• <strong>Patient:</strong> Standard user access</li>
                                <li>• <strong>Pharmacist:</strong> Prescription management</li>
                                <li>• <strong>Admin:</strong> Full system access</li>
                                <li>• <strong>Pharmacy Tech:</strong> Limited pharmacy access</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Authentication Flow Diagram -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Authentication Flow</h3>
                        <div class="grid md:grid-cols-4 gap-4">
                            <div class="auth-flow-step bg-white p-4 text-center">
                                <div class="bg-purple-100 text-purple-800 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">1</div>
                                <h4 class="font-semibold text-gray-900 mb-2">Register/Login</h4>
                                <p class="text-gray-600 text-sm">Submit credentials to receive API token</p>
                            </div>
                            
                            <div class="auth-flow-step bg-white p-4 text-center">
                                <div class="bg-purple-100 text-purple-800 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">2</div>
                                <h4 class="font-semibold text-gray-900 mb-2">Store Token</h4>
                                <p class="text-gray-600 text-sm">Securely store the received bearer token</p>
                            </div>
                            
                            <div class="auth-flow-step bg-white p-4 text-center">
                                <div class="bg-purple-100 text-purple-800 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">3</div>
                                <h4 class="font-semibold text-gray-900 mb-2">Include Token</h4>
                                <p class="text-gray-600 text-sm">Add token to Authorization header</p>
                            </div>
                            
                            <div class="auth-flow-step bg-white p-4 text-center">
                                <div class="bg-purple-100 text-purple-800 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">4</div>
                                <h4 class="font-semibold text-gray-900 mb-2">Make Requests</h4>
                                <p class="text-gray-600 text-sm">Access protected API endpoints</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Token Authentication Section -->
                <section id="token-auth" class="bg-purple-50 border-l-4 border-purple-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Token Authentication</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">How Token Authentication Works</h3>
                        <p class="text-gray-700 mb-4">
                            Sanctum issues API tokens that are included in the Authorization header of each request. 
                            These tokens are tied to specific users and can have different abilities/scopes.
                        </p>
                        
                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-bash"># Authorization Header Format
Authorization: Bearer {your-api-token}

# Example API Request
curl -X GET "{{ config('app.url') }}/api/user" \
  -H "Authorization: Bearer 1|abc123def456ghi789..." \
  -H "Content-Type: application/json"</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Token Structure</h3>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-gray-700 mb-3">Sanctum tokens have the following structure:</p>
                            <div class="bg-gray-100 rounded p-3 font-mono text-sm">
                                <span class="text-blue-600">{token_id}</span><span class="text-gray-500">|</span><span class="text-green-600">{random_string}</span>
                            </div>
                            <ul class="text-gray-600 text-sm mt-3 space-y-1">
                                <li>• <strong>token_id:</strong> Database record identifier</li>
                                <li>• <strong>random_string:</strong> Cryptographically secure random token</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Registration Section -->
                <section id="registration" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">User Registration</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Registration Endpoint</h3>
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center mb-2">
                                <span class="bg-blue-500 text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                <span class="ml-3 font-mono">/api/auth/register</span>
                            </div>
                            <p class="text-gray-600 text-sm">Create a new user account and receive an API token</p>
                        </div>

                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-javascript">// Registration request example
const registerUser = async (userData) => {
  try {
    const response = await fetch('{{ config('app.url') }}/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password,
        user_type: userData.userType, // 'patient', 'pharmacist', 'admin'
        first_name: userData.firstName,
        last_name: userData.lastName
      })
    });

    if (response.ok) {
      const data = await response.json();
      // Store the token securely
      localStorage.setItem('api_token', data.token);
      return { success: true, user: data.user, token: data.token };
    } else {
      const error = await response.json();
      return { success: false, errors: error.errors };
    }
  } catch (error) {
    console.error('Registration failed:', error);
    return { success: false, error: 'Network error' };
  }
};</code></pre>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h4 class="font-semibold text-yellow-800 mb-2">Required Fields</h4>
                            <ul class="text-yellow-700 text-sm space-y-1">
                                <li>• <strong>email:</strong> Valid email address (must be unique)</li>
                                <li>• <strong>password:</strong> Minimum 8 characters</li>
                                <li>• <strong>password_confirmation:</strong> Must match password</li>
                                <li>• <strong>user_type:</strong> One of: patient, pharmacist, admin</li>
                                <li>• <strong>first_name:</strong> User's first name</li>
                                <li>• <strong>last_name:</strong> User's last name</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Registration Response</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-json">{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "user_type": "patient",
    "is_active": true,
    "email_verified_at": null,
    "hipaa_acknowledged_at": null,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z",
    "profile": {
      "first_name": "John",
      "last_name": "Doe"
    }
  },
  "token": "1|abc123def456ghi789...",
  "abilities": ["*"]
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Login Flow Section -->
                <section id="login-flow" class="bg-purple-50 border-l-4 border-purple-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Login Flow</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Login Endpoint</h3>
                        <div class="bg-white rounded-lg p-4 mb-4">
                            <div class="flex items-center mb-2">
                                <span class="bg-blue-500 text-white px-3 py-1 rounded text-sm font-semibold">POST</span>
                                <span class="ml-3 font-mono">/api/auth/login</span>
                            </div>
                            <p class="text-gray-600 text-sm">Authenticate user credentials and receive API token</p>
                        </div>

                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-python"># Python login example
import requests

def login_user(email, password):
    """Login user and return API token"""
    login_data = {
        "email": email,
        "password": password,
        "remember": False  # Optional: for longer-lived tokens
    }
    
    response = requests.post(
        "{{ config('app.url') }}/api/auth/login",
        json=login_data,
        headers={"Content-Type": "application/json"}
    )
    
    if response.status_code == 200:
        data = response.json()
        return {
            "success": True,
            "token": data["token"],
            "user": data["user"]
        }
    else:
        return {
            "success": False,
            "error": response.json().get("message", "Login failed")
        }

# Usage
result = login_user("user@example.com", "password123")
if result["success"]:
    api_token = result["token"]
    print(f"Login successful! Token: {api_token}")
else:
    print(f"Login failed: {result['error']}")</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Using the Token</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Create authenticated API client
class DirectMedsAPI {
  constructor(token) {
    this.token = token;
    this.baseURL = '{{ config('app.url') }}/api';
  }

  // Helper method for authenticated requests
  async makeRequest(method, endpoint, data = null) {
    const config = {
      method: method,
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json'
      }
    };

    if (data) {
      config.body = JSON.stringify(data);
    }

    const response = await fetch(`${this.baseURL}${endpoint}`, config);
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'API request failed');
    }

    return await response.json();
  }

  // Get user profile
  async getUserProfile() {
    return await this.makeRequest('GET', '/user');
  }

  // Search products
  async searchProducts(searchTerm) {
    return await this.makeRequest('GET', `/products?search=${encodeURIComponent(searchTerm)}`);
  }

  // Upload prescription
  async uploadPrescription(prescriptionData) {
    return await this.makeRequest('POST', '/prescriptions', prescriptionData);
  }
}

// Usage
const api = new DirectMedsAPI(localStorage.getItem('api_token'));
api.getUserProfile().then(profile => {
  console.log('User profile:', profile);
}).catch(error => {
  console.error('Failed to get profile:', error);
});</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Two-Factor Authentication Section -->
                <section id="two-factor" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Two-Factor Authentication</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">2FA Overview</h3>
                        <p class="text-gray-700 mb-4">
                            Direct Meds API supports Google Authenticator-compatible TOTP (Time-based One-Time Password) 
                            for additional security. 2FA is strongly recommended for all healthcare providers.
                        </p>

                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="font-semibold text-green-800 mb-2">Enable 2FA</h4>
                                <p class="text-green-700 text-sm">POST /api/2fa/enable</p>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-semibold text-blue-800 mb-2">Confirm Setup</h4>
                                <p class="text-blue-700 text-sm">POST /api/2fa/confirm</p>
                            </div>
                            
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <h4 class="font-semibold text-purple-800 mb-2">Get Status</h4>
                                <p class="text-purple-700 text-sm">GET /api/2fa/status</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Enabling 2FA</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Enable 2FA workflow
const enable2FA = async () => {
  try {
    // Step 1: Enable 2FA and get QR code
    const enableResponse = await api.post('/2fa/enable');
    const { qr_code, secret } = enableResponse.data;
    
    // Step 2: Display QR code to user
    document.getElementById('qr-code').innerHTML = qr_code;
    
    // Step 3: User scans QR code with authenticator app
    // Step 4: User enters verification code
    const verificationCode = prompt('Enter verification code from authenticator app:');
    
    // Step 5: Confirm 2FA setup
    const confirmResponse = await api.post('/2fa/confirm', {
      code: verificationCode
    });
    
    if (confirmResponse.data.success) {
      alert('2FA enabled successfully!');
      // Store recovery codes securely
      const recoveryCodes = confirmResponse.data.recovery_codes;
      console.log('Recovery codes:', recoveryCodes);
    }
    
  } catch (error) {
    console.error('2FA setup failed:', error);
  }
};</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Login with 2FA</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Login with 2FA
const loginWith2FA = async (email, password, twoFactorCode) => {
  try {
    const response = await fetch('{{ config('app.url') }}/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: email,
        password: password,
        two_factor_code: twoFactorCode // Include 2FA code
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      return { success: true, token: data.token, user: data.user };
    } else if (response.status === 422 && data.errors?.two_factor_code) {
      return { success: false, requires2FA: true, message: 'Invalid 2FA code' };
    } else {
      return { success: false, message: data.message };
    }
    
  } catch (error) {
    return { success: false, message: 'Network error' };
  }
};</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Token Management Section -->
                <section id="token-management" class="bg-purple-50 border-l-4 border-purple-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Token Management</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Token Lifecycle</h3>
                        <div class="grid md:grid-cols-4 gap-4">
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-2">Creation</h4>
                                <p class="text-gray-600 text-sm">Tokens created on login/registration</p>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-2">Usage</h4>
                                <p class="text-gray-600 text-sm">Include in Authorization header</p>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-2">Expiration</h4>
                                <p class="text-gray-600 text-sm">Tokens don't expire automatically</p>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-2">Revocation</h4>
                                <p class="text-gray-600 text-sm">Manual logout or admin action</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Managing User Tokens</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Token management functions
class TokenManager {
  constructor(apiClient) {
    this.api = apiClient;
  }

  // List all user tokens
  async listTokens() {
    try {
      const response = await this.api.get('/tokens');
      return response.data.tokens;
    } catch (error) {
      console.error('Failed to list tokens:', error);
      throw error;
    }
  }

  // Revoke specific token
  async revokeToken(tokenId) {
    try {
      await this.api.delete(`/tokens/${tokenId}`);
      return { success: true, message: 'Token revoked successfully' };
    } catch (error) {
      console.error('Failed to revoke token:', error);
      throw error;
    }
  }

  // Logout current session
  async logout() {
    try {
      await this.api.post('/auth/logout');
      // Clear stored token
      localStorage.removeItem('api_token');
      return { success: true };
    } catch (error) {
      console.error('Logout failed:', error);
      throw error;
    }
  }

  // Logout all sessions
  async logoutAll() {
    try {
      await this.api.post('/auth/logout-all');
      localStorage.removeItem('api_token');
      return { success: true };
    } catch (error) {
      console.error('Logout all failed:', error);
      throw error;
    }
  }

  // Check if token is still valid
  async validateToken() {
    try {
      await this.api.get('/user');
      return { valid: true };
    } catch (error) {
      if (error.response?.status === 401) {
        return { valid: false, reason: 'Token expired or invalid' };
      }
      throw error;
    }
  }
}

// Usage
const tokenManager = new TokenManager(apiClient);

// Check token validity
tokenManager.validateToken().then(result => {
  if (!result.valid) {
    // Redirect to login
    window.location.href = '/login';
  }
});</code></pre>
                        </div>
                    </div>
                </section>

                <!-- HIPAA Compliance Section -->
                <section id="hipaa-compliance" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">HIPAA Compliance</h2>
                    
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Important:</strong> HIPAA acknowledgment is required before accessing any endpoints that handle Protected Health Information (PHI).
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">HIPAA Acknowledgment Process</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// HIPAA acknowledgment workflow
const handleHIPAACompliance = async () => {
  try {
    // Step 1: Check current HIPAA status
    const statusResponse = await api.get('/hipaa/status');
    const { hipaa_acknowledged, acknowledged_at } = statusResponse.data;
    
    if (!hipaa_acknowledged) {
      // Step 2: Show HIPAA notice to user
      const userAccepted = await showHIPAANotice();
      
      if (userAccepted) {
        // Step 3: Acknowledge HIPAA compliance
        const acknowledgeResponse = await api.post('/hipaa/acknowledge', {
          acknowledged: true
        });
        
        console.log('HIPAA acknowledged at:', acknowledgeResponse.data.acknowledged_at);
        return true;
      } else {
        throw new Error('HIPAA acknowledgment is required to access PHI');
      }
    }
    
    return true;
    
  } catch (error) {
    console.error('HIPAA compliance check failed:', error);
    throw error;
  }
};

// Example HIPAA notice display
const showHIPAANotice = () => {
  return new Promise((resolve) => {
    const modal = document.createElement('div');
    modal.innerHTML = `
      <div class="hipaa-modal">
        <h3>HIPAA Privacy Notice</h3>
        <p>By continuing, you acknowledge that you understand and agree to 
           comply with HIPAA regulations regarding Protected Health Information (PHI).</p>
        <p>All PHI access will be logged for compliance and audit purposes.</p>
        <button onclick="resolve(true)">I Acknowledge</button>
        <button onclick="resolve(false)">Cancel</button>
      </div>
    `;
    document.body.appendChild(modal);
  });
};

// Use before accessing PHI endpoints
handleHIPAACompliance().then(() => {
  // Now safe to access PHI endpoints
  return api.get('/prescriptions');
}).catch(error => {
  console.error('Cannot access PHI without HIPAA acknowledgment');
});</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Audit Logging</h3>
                        <p class="text-gray-700 mb-4">
                            All API requests that access PHI are automatically logged with the following information:
                        </p>
                        <ul class="text-gray-700 space-y-2">
                            <li>• User identification and authentication status</li>
                            <li>• Timestamp of access</li>
                            <li>• IP address and user agent</li>
                            <li>• Specific data accessed or modified</li>
                            <li>• Purpose of access (implicit from endpoint)</li>
                        </ul>
                    </div>
                </section>

                <!-- Security Best Practices Section -->
                <section id="security-best-practices" class="bg-purple-50 border-l-4 border-purple-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Security Best Practices</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Token Security</h3>
                            <ul class="text-gray-700 space-y-2">
                                <li>• Store tokens securely (encrypted storage)</li>
                                <li>• Never expose tokens in URLs or logs</li>
                                <li>• Use HTTPS for all API communication</li>
                                <li>• Implement token rotation policies</li>
                                <li>• Revoke tokens when compromised</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Application Security</h3>
                            <ul class="text-gray-700 space-y-2">
                                <li>• Implement proper error handling</li>
                                <li>• Validate all user inputs</li>
                                <li>• Use rate limiting to prevent abuse</li>
                                <li>• Monitor for suspicious activity</li>
                                <li>• Keep dependencies updated</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Secure Token Storage Example</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Secure token storage implementation
class SecureTokenStorage {
  constructor() {
    this.storageKey = 'directmeds_api_token';
    this.encryptionKey = this.getOrCreateEncryptionKey();
  }

  // Store token securely
  storeToken(token) {
    try {
      const encryptedToken = this.encrypt(token);
      localStorage.setItem(this.storageKey, encryptedToken);
      
      // Set expiration check
      const expirationTime = Date.now() + (24 * 60 * 60 * 1000); // 24 hours
      localStorage.setItem(this.storageKey + '_exp', expirationTime.toString());
      
    } catch (error) {
      console.error('Failed to store token securely:', error);
    }
  }

  // Retrieve token securely
  getToken() {
    try {
      // Check expiration
      const expiration = localStorage.getItem(this.storageKey + '_exp');
      if (expiration && Date.now() > parseInt(expiration)) {
        this.clearToken();
        return null;
      }

      const encryptedToken = localStorage.getItem(this.storageKey);
      if (!encryptedToken) return null;

      return this.decrypt(encryptedToken);
      
    } catch (error) {
      console.error('Failed to retrieve token:', error);
      this.clearToken(); // Clear potentially corrupted token
      return null;
    }
  }

  // Clear stored token
  clearToken() {
    localStorage.removeItem(this.storageKey);
    localStorage.removeItem(this.storageKey + '_exp');
  }

  // Generate or retrieve encryption key
  getOrCreateEncryptionKey() {
    let key = localStorage.getItem('encryption_key');
    if (!key) {
      key = this.generateRandomKey();
      localStorage.setItem('encryption_key', key);
    }
    return key;
  }

  // Simple encryption (use proper crypto library in production)
  encrypt(text) {
    // This is a simplified example - use proper encryption in production
    return btoa(text + '|' + this.encryptionKey);
  }

  decrypt(encryptedText) {
    const decoded = atob(encryptedText);
    const [text, key] = decoded.split('|');
    if (key !== this.encryptionKey) {
      throw new Error('Invalid encryption key');
    }
    return text;
  }

  generateRandomKey() {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
  }
}

// Usage
const tokenStorage = new SecureTokenStorage();

// Store token after login
tokenStorage.storeToken(apiToken);

// Retrieve token for API calls
const token = tokenStorage.getToken();
if (token) {
  // Use token for API calls
} else {
  // Redirect to login
}</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting Section -->
                <section id="troubleshooting" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Troubleshooting</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Common Authentication Issues</h3>
                            <div class="space-y-4">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-600 mb-2">401 Unauthorized</h4>
                                    <p class="text-gray-700 text-sm mb-2">Token is missing, invalid, or expired</p>
                                    <ul class="text-gray-600 text-sm space-y-1">
                                        <li>• Check Authorization header format: "Bearer {token}"</li>
                                        <li>• Verify token hasn't been revoked</li>
                                        <li>• Re-authenticate if token is expired</li>
                                    </ul>
                                </div>

                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-600 mb-2">403 Forbidden</h4>
                                    <p class="text-gray-700 text-sm mb-2">Valid token but insufficient permissions</p>
                                    <ul class="text-gray-600 text-sm space-y-1">
                                        <li>• Check user role and permissions</li>
                                        <li>• Verify HIPAA acknowledgment for PHI endpoints</li>
                                        <li>• Ensure account is active and not suspended</li>
                                    </ul>
                                </div>

                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-600 mb-2">422 Validation Error</h4>
                                    <p class="text-gray-700 text-sm mb-2">Invalid login credentials or 2FA code</p>
                                    <ul class="text-gray-600 text-sm space-y-1">
                                        <li>• Verify email and password are correct</li>
                                        <li>• Check 2FA code if enabled (6-digit, time-sensitive)</li>
                                        <li>• Ensure all required fields are provided</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Debugging Tools</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-javascript">// Authentication debugging utilities
class AuthDebugger {
  static async debugToken(token) {
    console.log('=== Token Debug Information ===');
    console.log('Token:', token ? token.substring(0, 20) + '...' : 'No token');
    
    if (!token) {
      console.log('❌ No token provided');
      return;
    }

    try {
      // Test token validity
      const response = await fetch('{{ config('app.url') }}/api/user', {
        headers: { 'Authorization': `Bearer ${token}` }
      });

      if (response.ok) {
        const user = await response.json();
        console.log('✅ Token is valid');
        console.log('User ID:', user.user.id);
        console.log('User Type:', user.user.user_type);
        console.log('Permissions:', user.permissions);
        console.log('HIPAA Acknowledged:', user.user.hipaa_acknowledged_at ? 'Yes' : 'No');
      } else {
        console.log('❌ Token validation failed');
        console.log('Status:', response.status);
        console.log('Response:', await response.text());
      }
    } catch (error) {
      console.log('❌ Network error:', error.message);
    }
  }

  static logRequestDetails(method, url, headers, body) {
    console.log('=== API Request Debug ===');
    console.log('Method:', method);
    console.log('URL:', url);
    console.log('Headers:', headers);
    if (body) console.log('Body:', body);
  }
}

// Usage
const token = localStorage.getItem('api_token');
AuthDebugger.debugToken(token);</code></pre>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="text-gray-400">Direct Meds API Authentication Guide - Last updated: {{ date('Y-m-d') }}</p>
            <p class="text-gray-400 mt-2">For support, contact: api-support@directmeds.com</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>