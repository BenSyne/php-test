<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Guide - Direct Meds API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <style>
        .code-section { background: #2d3748; }
        .sidebar-nav { position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-green-500">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-500 text-white p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Developer Guide</h1>
                        <p class="text-gray-600">Complete guide to integrating with Direct Meds API</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('api.docs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Back to Docs
                    </a>
                    <a href="{{ route('api.docs.swagger') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Swagger UI
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Guide Sections</h3>
                    <ul class="space-y-2">
                        <li><a href="#quick-start" class="block text-green-600 hover:text-green-800 transition-colors">Quick Start</a></li>
                        <li><a href="#authentication" class="block text-green-600 hover:text-green-800 transition-colors">Authentication Flow</a></li>
                        <li><a href="#sdk-examples" class="block text-green-600 hover:text-green-800 transition-colors">SDK Examples</a></li>
                        <li><a href="#common-workflows" class="block text-green-600 hover:text-green-800 transition-colors">Common Workflows</a></li>
                        <li><a href="#best-practices" class="block text-green-600 hover:text-green-800 transition-colors">Best Practices</a></li>
                        <li><a href="#error-handling" class="block text-green-600 hover:text-green-800 transition-colors">Error Handling</a></li>
                        <li><a href="#testing" class="block text-green-600 hover:text-green-800 transition-colors">Testing</a></li>
                        <li><a href="#security" class="block text-green-600 hover:text-green-800 transition-colors">Security Guidelines</a></li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4 px-6">
                <!-- Quick Start Section -->
                <section id="quick-start" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Quick Start</h2>
                    <p class="text-gray-700 mb-6">
                        Get started with the Direct Meds API in minutes. This guide covers the essential steps to make your first API call.
                    </p>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">1. Set Up Your Environment</h3>
                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-bash"># Set environment variables
export DIRECTMEDS_API_URL="{{ config('app.url') }}/api"
export DIRECTMEDS_API_TOKEN="your-api-token-here"

# Test connectivity
curl -X GET $DIRECTMEDS_API_URL/health</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">2. Register and Authenticate</h3>
                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-javascript">// JavaScript/Node.js example
const axios = require('axios');

const API_BASE = '{{ config('app.url') }}/api';

// Register new user
const registerUser = async () => {
  try {
    const response = await axios.post(`${API_BASE}/auth/register`, {
      email: 'developer@example.com',
      password: 'SecurePassword123!',
      password_confirmation: 'SecurePassword123!',
      user_type: 'patient',
      first_name: 'John',
      last_name: 'Doe'
    });
    
    console.log('Registration successful:', response.data);
    return response.data.token;
  } catch (error) {
    console.error('Registration failed:', error.response.data);
  }
};

// Login user
const loginUser = async () => {
  try {
    const response = await axios.post(`${API_BASE}/auth/login`, {
      email: 'developer@example.com',
      password: 'SecurePassword123!'
    });
    
    console.log('Login successful:', response.data);
    return response.data.token;
  } catch (error) {
    console.error('Login failed:', error.response.data);
  }
};</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">3. Make Your First API Call</h3>
                        <div class="code-section text-white rounded-lg p-4 mb-4">
                            <pre><code class="language-javascript">// Set up authenticated API client
const apiClient = axios.create({
  baseURL: API_BASE,
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});

// Get user profile
const getUserProfile = async () => {
  try {
    const response = await apiClient.get('/user');
    console.log('User profile:', response.data);
  } catch (error) {
    console.error('Failed to get user profile:', error.response.data);
  }
};

// Search products
const searchProducts = async (searchTerm) => {
  try {
    const response = await apiClient.get('/products', {
      params: { search: searchTerm }
    });
    console.log('Products found:', response.data);
  } catch (error) {
    console.error('Product search failed:', error.response.data);
  }
};</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Authentication Flow Section -->
                <section id="authentication" class="bg-green-50 border-l-4 border-green-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Authentication Flow</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Token-Based Authentication</h3>
                        <p class="text-gray-700 mb-4">
                            Direct Meds API uses Laravel Sanctum for stateless API authentication. Here's the complete flow:
                        </p>
                        
                        <div class="bg-white rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Authentication Steps</h4>
                            <ol class="list-decimal list-inside text-gray-700 space-y-2">
                                <li>Register a new account or use existing credentials</li>
                                <li>Send login request to receive API token</li>
                                <li>Include token in Authorization header for all requests</li>
                                <li>Handle token expiration and refresh as needed</li>
                            </ol>
                        </div>

                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-python"># Python example
import requests

class DirectMedsAPI:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        self.session = requests.Session()
    
    def login(self, email, password):
        """Authenticate and store token"""
        response = self.session.post(f"{self.base_url}/auth/login", json={
            "email": email,
            "password": password
        })
        
        if response.status_code == 200:
            data = response.json()
            self.token = data['token']
            self.session.headers.update({
                'Authorization': f'Bearer {self.token}'
            })
            return True
        return False
    
    def get_user_profile(self):
        """Get authenticated user profile"""
        if not self.token:
            raise Exception("Must authenticate first")
        
        response = self.session.get(f"{self.base_url}/user")
        return response.json()

# Usage
api = DirectMedsAPI("{{ config('app.url') }}/api")
api.login("user@example.com", "password")
profile = api.get_user_profile()</code></pre>
                        </div>
                    </div>
                </section>

                <!-- SDK Examples Section -->
                <section id="sdk-examples" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">SDK Examples</h2>
                    
                    <div class="grid md:grid-cols-1 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">PHP/Laravel Integration</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-php"><?php
// PHP SDK example
class DirectMedsAPIClient
{
    private $baseUrl;
    private $token;
    private $httpClient;

    public function __construct($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = new GuzzleHttp\Client();
    }

    public function authenticate($email, $password)
    {
        $response = $this->httpClient->post($this->baseUrl . '/auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $this->token = $data['token'];
        
        return $this->token;
    }

    public function getProducts($filters = [])
    {
        return $this->makeRequest('GET', '/products', ['query' => $filters]);
    }

    public function createPrescription($prescriptionData)
    {
        return $this->makeRequest('POST', '/prescriptions', ['json' => $prescriptionData]);
    }

    private function makeRequest($method, $endpoint, $options = [])
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ]);

        $response = $this->httpClient->request($method, $this->baseUrl . $endpoint, $options);
        return json_decode($response->getBody(), true);
    }
}

// Usage
$api = new DirectMedsAPIClient('{{ config('app.url') }}/api');
$api->authenticate('user@example.com', 'password');
$products = $api->getProducts(['search' => 'ibuprofen']);</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">React/JavaScript Integration</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-javascript">// React hooks for API integration
import { useState, useEffect, useContext, createContext } from 'react';
import axios from 'axios';

const APIContext = createContext();

export const APIProvider = ({ children }) => {
  const [token, setToken] = useState(localStorage.getItem('api_token'));
  const [user, setUser] = useState(null);

  const api = axios.create({
    baseURL: '{{ config('app.url') }}/api',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  // Add token to requests
  api.interceptors.request.use((config) => {
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });

  const login = async (email, password) => {
    try {
      const response = await api.post('/auth/login', { email, password });
      const { token: newToken, user } = response.data;
      
      setToken(newToken);
      setUser(user);
      localStorage.setItem('api_token', newToken);
      
      return { success: true, user };
    } catch (error) {
      return { success: false, error: error.response?.data };
    }
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setToken(null);
      setUser(null);
      localStorage.removeItem('api_token');
    }
  };

  return (
    <APIContext.Provider value={{ api, token, user, login, logout }}>
      {children}
    </APIContext.Provider>
  );
};

// Custom hook for API access
export const useAPI = () => {
  const context = useContext(APIContext);
  if (!context) {
    throw new Error('useAPI must be used within APIProvider');
  }
  return context;
};

// Component example
const ProductSearch = () => {
  const { api } = useAPI();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);

  const searchProducts = async (searchTerm) => {
    setLoading(true);
    try {
      const response = await api.get('/products', {
        params: { search: searchTerm }
      });
      setProducts(response.data.data);
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      {/* Search UI components */}
    </div>
  );
};</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Common Workflows Section -->
                <section id="common-workflows" class="bg-green-50 border-l-4 border-green-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Common Workflows</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">1. Patient Prescription Upload</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-javascript">// Complete prescription upload workflow
const uploadPrescription = async (prescriptionFile, patientInfo) => {
  try {
    // 1. Acknowledge HIPAA compliance
    await api.post('/hipaa/acknowledge', { acknowledged: true });
    
    // 2. Upload prescription file
    const formData = new FormData();
    formData.append('prescription_file', prescriptionFile);
    formData.append('patient_name', patientInfo.name);
    formData.append('prescriber_name', patientInfo.prescriber);
    formData.append('notes', patientInfo.notes || '');
    
    const response = await api.post('/prescriptions', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    console.log('Prescription uploaded:', response.data);
    return response.data;
    
  } catch (error) {
    console.error('Upload failed:', error.response.data);
    throw error;
  }
};</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">2. Product Search with Drug Interactions</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-javascript">// Search products and check interactions
const searchWithInteractions = async (searchTerm, currentMedications = []) => {
  try {
    // Search for products
    const searchResponse = await api.get('/products/search', {
      params: { search: searchTerm }
    });
    
    const products = searchResponse.data.data;
    
    // Check interactions if user has current medications
    if (currentMedications.length > 0 && products.length > 0) {
      const interactionResponse = await api.post('/products/check-interactions', {
        product_ids: products.map(p => p.id),
        current_medications: currentMedications
      });
      
      // Combine product data with interaction warnings
      return products.map(product => ({
        ...product,
        interactions: interactionResponse.data.interactions[product.id] || []
      }));
    }
    
    return products;
    
  } catch (error) {
    console.error('Search with interactions failed:', error);
    throw error;
  }
};</code></pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">3. Payment Processing Flow</h3>
                            <div class="code-section text-white rounded-lg p-4">
                                <pre><code class="language-javascript">// Complete payment processing workflow
const processPayment = async (prescriptionId, paymentMethod) => {
  try {
    // 1. Create payment intent
    const paymentResponse = await api.post('/payments', {
      prescription_id: prescriptionId,
      payment_method: paymentMethod,
      amount: paymentMethod.amount
    });
    
    const payment = paymentResponse.data;
    
    // 2. Process payment (integrate with Stripe/payment processor)
    const stripeResult = await processStripePayment(payment.client_secret, paymentMethod);
    
    if (stripeResult.error) {
      throw new Error(stripeResult.error.message);
    }
    
    // 3. Confirm payment completion
    const confirmResponse = await api.post(`/payments/${payment.id}/confirm`, {
      payment_intent_id: stripeResult.paymentIntent.id
    });
    
    console.log('Payment processed successfully:', confirmResponse.data);
    return confirmResponse.data;
    
  } catch (error) {
    console.error('Payment processing failed:', error);
    throw error;
  }
};</code></pre>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Best Practices Section -->
                <section id="best-practices" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Best Practices</h2>
                    
                    <div class="grid md:grid-cols-1 gap-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <h3 class="text-lg font-semibold text-blue-800 mb-3">Security Best Practices</h3>
                            <ul class="text-blue-700 space-y-2">
                                <li><strong>Never expose API tokens:</strong> Store tokens securely and never commit them to version control</li>
                                <li><strong>Use HTTPS only:</strong> All API communication must use SSL/TLS encryption</li>
                                <li><strong>Implement token rotation:</strong> Regularly refresh API tokens</li>
                                <li><strong>Validate all inputs:</strong> Always validate and sanitize user inputs</li>
                                <li><strong>Handle PHI carefully:</strong> Follow HIPAA guidelines for Protected Health Information</li>
                            </ul>
                        </div>

                        <div class="bg-green-50 border-l-4 border-green-400 p-4">
                            <h3 class="text-lg font-semibold text-green-800 mb-3">Performance Best Practices</h3>
                            <ul class="text-green-700 space-y-2">
                                <li><strong>Use pagination:</strong> Request data in manageable chunks using limit/offset parameters</li>
                                <li><strong>Implement caching:</strong> Cache frequently accessed data with appropriate TTL</li>
                                <li><strong>Batch requests:</strong> Combine multiple operations when possible</li>
                                <li><strong>Use compression:</strong> Enable gzip compression for large responses</li>
                                <li><strong>Monitor rate limits:</strong> Respect API rate limits and implement backoff strategies</li>
                            </ul>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-3">Error Handling Best Practices</h3>
                            <ul class="text-yellow-700 space-y-2">
                                <li><strong>Implement retry logic:</strong> Retry failed requests with exponential backoff</li>
                                <li><strong>Handle all error scenarios:</strong> Account for network, authentication, and business logic errors</li>
                                <li><strong>Log errors appropriately:</strong> Log errors for debugging while respecting privacy</li>
                                <li><strong>Provide user feedback:</strong> Give meaningful error messages to users</li>
                                <li><strong>Use circuit breakers:</strong> Prevent cascading failures with circuit breaker pattern</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Testing Section -->
                <section id="testing" class="bg-green-50 border-l-4 border-green-400 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Testing Your Integration</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Unit Testing Example</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Jest testing example
describe('DirectMeds API Integration', () => {
  let apiClient;

  beforeEach(() => {
    apiClient = new DirectMedsAPI('{{ config('app.url') }}/api');
  });

  test('should authenticate successfully', async () => {
    const mockResponse = {
      user: { id: 1, email: 'test@example.com' },
      token: 'mock-token'
    };

    jest.spyOn(axios, 'post').mockResolvedValue({ data: mockResponse });

    const result = await apiClient.login('test@example.com', 'password');
    expect(result).toBe(true);
    expect(apiClient.token).toBe('mock-token');
  });

  test('should handle authentication failure', async () => {
    jest.spyOn(axios, 'post').mockRejectedValue({
      response: { status: 401, data: { message: 'Invalid credentials' } }
    });

    const result = await apiClient.login('test@example.com', 'wrong-password');
    expect(result).toBe(false);
  });

  test('should search products successfully', async () => {
    const mockProducts = {
      data: [
        { id: 1, name: 'Ibuprofen 200mg', ndc_number: '12345-6789-01' }
      ]
    };

    jest.spyOn(axios, 'get').mockResolvedValue({ data: mockProducts });

    const products = await apiClient.searchProducts('ibuprofen');
    expect(products.data).toHaveLength(1);
    expect(products.data[0].name).toBe('Ibuprofen 200mg');
  });
});</code></pre>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Integration Testing</h3>
                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-bash"># Test script for API integration
#!/bin/bash

API_BASE="{{ config('app.url') }}/api"
TEST_EMAIL="test@example.com"
TEST_PASSWORD="TestPassword123!"

echo "Testing Direct Meds API Integration..."

# Test 1: Health check
echo "1. Testing health check..."
curl -s -o /dev/null -w "%{http_code}" "$API_BASE/health"

# Test 2: User registration
echo "2. Testing user registration..."
REGISTER_RESPONSE=$(curl -s -X POST "$API_BASE/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "'$TEST_EMAIL'",
    "password": "'$TEST_PASSWORD'",
    "password_confirmation": "'$TEST_PASSWORD'",
    "user_type": "patient",
    "first_name": "Test",
    "last_name": "User"
  }')

# Test 3: User login
echo "3. Testing user login..."
LOGIN_RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "'$TEST_EMAIL'",
    "password": "'$TEST_PASSWORD'"
  }')

# Extract token
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')

# Test 4: Authenticated request
echo "4. Testing authenticated request..."
curl -s -X GET "$API_BASE/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"

echo "Integration tests completed!"</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Security Guidelines Section -->
                <section id="security" class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Security Guidelines</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4">
                            <h3 class="text-lg font-semibold text-red-800 mb-3">HIPAA Compliance Requirements</h3>
                            <ul class="text-red-700 space-y-2">
                                <li><strong>Data Encryption:</strong> All PHI must be encrypted in transit and at rest</li>
                                <li><strong>Access Logging:</strong> All PHI access is automatically logged for audit purposes</li>
                                <li><strong>User Authentication:</strong> Strong authentication required for all PHI access</li>
                                <li><strong>Data Retention:</strong> PHI retained for 7 years minimum per HIPAA requirements</li>
                                <li><strong>Business Associate Agreement:</strong> Required for all third-party integrations</li>
                            </ul>
                        </div>

                        <div class="code-section text-white rounded-lg p-4">
                            <pre><code class="language-javascript">// Security implementation example
class SecureAPIClient {
  constructor(baseUrl, apiKey) {
    this.baseUrl = baseUrl;
    this.apiKey = apiKey;
    this.setupSecureDefaults();
  }

  setupSecureDefaults() {
    // Enforce HTTPS
    if (!this.baseUrl.startsWith('https://')) {
      throw new Error('API must use HTTPS for security');
    }

    // Set up secure HTTP client
    this.client = axios.create({
      baseURL: this.baseUrl,
      timeout: 30000,
      headers: {
        'User-Agent': 'DirectMeds-Client/1.0',
        'Content-Type': 'application/json'
      }
    });

    // Add request interceptor for security headers
    this.client.interceptors.request.use((config) => {
      config.headers['Authorization'] = `Bearer ${this.apiKey}`;
      config.headers['X-Request-ID'] = this.generateRequestId();
      return config;
    });

    // Add response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        this.logSecurityEvent(error);
        return Promise.reject(error);
      }
    );
  }

  generateRequestId() {
    return 'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  logSecurityEvent(error) {
    // Log security-relevant events (avoid logging sensitive data)
    if (error.response?.status === 401) {
      console.warn('Authentication failed - token may be expired');
    } else if (error.response?.status === 403) {
      console.warn('Access denied - insufficient permissions');
    }
  }

  // Secure data handling
  async handlePHI(data) {
    // Ensure HIPAA acknowledgment before processing PHI
    await this.ensureHipaaAcknowledgment();
    
    // Process PHI with additional security measures
    return this.client.post('/secure-endpoint', data, {
      headers: {
        'X-PHI-Processing': 'true',
        'X-HIPAA-Acknowledged': 'true'
      }
    });
  }

  async ensureHipaaAcknowledgment() {
    // Check if HIPAA has been acknowledged
    const response = await this.client.get('/hipaa/status');
    if (!response.data.hipaa_acknowledged) {
      throw new Error('HIPAA acknowledgment required before accessing PHI');
    }
  }
}</code></pre>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="text-gray-400">Direct Meds API Developer Guide - Last updated: {{ date('Y-m-d') }}</p>
            <p class="text-gray-400 mt-2">For support, contact: api-support@directmeds.com</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>