<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * API Documentation Controller
 * 
 * Handles API documentation display and management
 */
class ApiDocumentationController extends Controller
{
    /**
     * Display the main API documentation interface.
     */
    public function index()
    {
        return view('api-docs.index');
    }

    /**
     * Get API documentation in JSON format.
     */
    public function json(): JsonResponse
    {
        $documentation = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Direct Meds Pharmacy API',
                'version' => '1.0.0',
                'description' => 'HIPAA-compliant pharmacy management system API',
                'contact' => [
                    'name' => 'Direct Meds API Support',
                    'email' => 'api-support@directmeds.com'
                ]
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'Direct Meds API Server'
                ]
            ],
            'security' => [
                ['sanctum' => []]
            ],
            'paths' => $this->getPaths(),
            'components' => $this->getComponents()
        ];

        return response()->json($documentation);
    }

    /**
     * Get developer guide content.
     */
    public function developerGuide()
    {
        return view('api-docs.developer-guide');
    }

    /**
     * Get authentication guide.
     */
    public function authenticationGuide()
    {
        return view('api-docs.authentication-guide');
    }

    /**
     * Get API testing interface.
     */
    public function testingInterface()
    {
        return view('api-docs.testing-interface');
    }

    /**
     * Get error codes documentation.
     */
    public function errorCodes(): JsonResponse
    {
        $errorCodes = [
            '400' => [
                'description' => 'Bad Request',
                'common_causes' => [
                    'Invalid JSON in request body',
                    'Missing required parameters',
                    'Invalid parameter values'
                ]
            ],
            '401' => [
                'description' => 'Unauthorized',
                'common_causes' => [
                    'Missing authentication token',
                    'Invalid authentication token',
                    'Expired authentication token'
                ]
            ],
            '403' => [
                'description' => 'Forbidden',
                'common_causes' => [
                    'Insufficient permissions',
                    'HIPAA acknowledgment required',
                    'Account suspended or deactivated'
                ]
            ],
            '404' => [
                'description' => 'Not Found',
                'common_causes' => [
                    'Resource does not exist',
                    'Invalid endpoint URL',
                    'Deleted resource'
                ]
            ],
            '422' => [
                'description' => 'Unprocessable Entity',
                'common_causes' => [
                    'Validation errors',
                    'Business logic violations',
                    'Data constraints not met'
                ]
            ],
            '429' => [
                'description' => 'Too Many Requests',
                'common_causes' => [
                    'Rate limit exceeded',
                    'Too many login attempts'
                ]
            ],
            '500' => [
                'description' => 'Internal Server Error',
                'common_causes' => [
                    'Server configuration error',
                    'Database connection issues',
                    'Unexpected system error'
                ]
            ]
        ];

        return response()->json([
            'error_codes' => $errorCodes,
            'standard_error_format' => [
                'message' => 'Human-readable error message',
                'errors' => [
                    'field_name' => ['Specific validation error message']
                ],
                'error_code' => 'SPECIFIC_ERROR_CODE',
                'timestamp' => 'ISO 8601 timestamp'
            ]
        ]);
    }

    /**
     * Generate API paths documentation.
     */
    private function getPaths(): array
    {
        return [
            '/auth/register' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Register new user',
                    'operationId' => 'registerUser',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/RegisterRequest'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'User registered successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/AuthResponse'
                                    ]
                                ]
                            ]
                        ],
                        '422' => [
                            'description' => 'Validation error',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ValidationError'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'Login user',
                    'operationId' => 'loginUser',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/LoginRequest'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/AuthResponse'
                                    ]
                                ]
                            ]
                        ],
                        '401' => [
                            'description' => 'Invalid credentials',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/ErrorResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate API components (schemas, responses, etc.).
     */
    private function getComponents(): array
    {
        return [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Sanctum bearer token authentication'
                ]
            ],
            'schemas' => [
                'RegisterRequest' => [
                    'type' => 'object',
                    'required' => ['email', 'password', 'password_confirmation', 'user_type', 'first_name', 'last_name'],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'example' => 'user@example.com'
                        ],
                        'password' => [
                            'type' => 'string',
                            'minLength' => 8,
                            'example' => 'SecurePassword123!'
                        ],
                        'password_confirmation' => [
                            'type' => 'string',
                            'example' => 'SecurePassword123!'
                        ],
                        'user_type' => [
                            'type' => 'string',
                            'enum' => ['patient', 'pharmacist', 'admin'],
                            'example' => 'patient'
                        ],
                        'first_name' => [
                            'type' => 'string',
                            'example' => 'John'
                        ],
                        'last_name' => [
                            'type' => 'string',
                            'example' => 'Doe'
                        ]
                    ]
                ],
                'LoginRequest' => [
                    'type' => 'object',
                    'required' => ['email', 'password'],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'example' => 'user@example.com'
                        ],
                        'password' => [
                            'type' => 'string',
                            'example' => 'SecurePassword123!'
                        ],
                        'remember' => [
                            'type' => 'boolean',
                            'example' => false
                        ]
                    ]
                ],
                'AuthResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'user' => [
                            '$ref' => '#/components/schemas/User'
                        ],
                        'token' => [
                            'type' => 'string',
                            'example' => '1|abc123def456ghi789'
                        ],
                        'message' => [
                            'type' => 'string',
                            'example' => 'Authentication successful'
                        ]
                    ]
                ],
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'example' => 1
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'example' => 'user@example.com'
                        ],
                        'user_type' => [
                            'type' => 'string',
                            'enum' => ['patient', 'pharmacist', 'admin'],
                            'example' => 'patient'
                        ],
                        'is_active' => [
                            'type' => 'boolean',
                            'example' => true
                        ],
                        'email_verified_at' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'nullable' => true
                        ],
                        'hipaa_acknowledged_at' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'nullable' => true
                        ],
                        'created_at' => [
                            'type' => 'string',
                            'format' => 'date-time'
                        ],
                        'updated_at' => [
                            'type' => 'string',
                            'format' => 'date-time'
                        ]
                    ]
                ],
                'ErrorResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => [
                            'type' => 'string',
                            'example' => 'The given data was invalid.'
                        ],
                        'error_code' => [
                            'type' => 'string',
                            'example' => 'VALIDATION_ERROR'
                        ]
                    ]
                ],
                'ValidationError' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => [
                            'type' => 'string',
                            'example' => 'The given data was invalid.'
                        ],
                        'errors' => [
                            'type' => 'object',
                            'additionalProperties' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string'
                                ]
                            ],
                            'example' => [
                                'email' => ['The email field is required.'],
                                'password' => ['The password must be at least 8 characters.']
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}