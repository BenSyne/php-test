<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Direct Meds Pharmacy API",
 *     version="1.0.0",
 *     description="HIPAA-compliant pharmacy management system API for Direct Meds. This API provides secure access to prescription management, product catalog, payment processing, and compliance features.",
 *     @OA\Contact(
 *         name="Direct Meds API Support",
 *         email="api-support@directmeds.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="Direct Meds API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use Sanctum bearer token for authentication. Include the token in the Authorization header as 'Bearer {token}'."
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and session management"
 * )
 * 
 * @OA\Tag(
 *     name="Two-Factor Authentication",
 *     description="Two-factor authentication management"
 * )
 * 
 * @OA\Tag(
 *     name="User Profile",
 *     description="User profile and account management"
 * )
 * 
 * @OA\Tag(
 *     name="HIPAA Compliance",
 *     description="HIPAA acknowledgment and compliance management"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="Pharmaceutical product catalog and search"
 * )
 * 
 * @OA\Tag(
 *     name="Prescriptions",
 *     description="Prescription management and workflow"
 * )
 * 
 * @OA\Tag(
 *     name="Payments",
 *     description="Payment processing and transaction management"
 * )
 * 
 * @OA\Tag(
 *     name="System",
 *     description="System health and status endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Webhooks",
 *     description="External webhook endpoints"
 * )
 */
class SwaggerController extends Controller
{
    //
}