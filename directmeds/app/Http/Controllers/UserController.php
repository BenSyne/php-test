<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Get authenticated user profile",
     *     description="Retrieve the authenticated user's profile information including permissions and roles",
     *     operationId="getUserProfile",
     *     tags={"User Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="user_type", type="string", example="patient"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="hipaa_acknowledged_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="profile",
     *                         type="object",
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="phone_mobile", type="string", example="+1234567890"),
     *                         @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *                         @OA\Property(property="city", type="string", example="San Francisco"),
     *                         @OA\Property(property="state", type="string", example="CA"),
     *                         @OA\Property(property="postal_code", type="string", example="94102")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"view prescriptions", "upload prescriptions"}
     *                 ),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"patient"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Unauthenticated.")
     *             )
     *         )
     *     )
     * )
     * Get authenticated user profile (defined inline in api.php)
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('profile'),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
            'roles' => $request->user()->getRoleNames(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/health",
     *     summary="Public health check",
     *     description="Check the API status and basic system information",
     *     operationId="healthCheck",
     *     tags={"System"},
     *     @OA\Response(
     *         response=200,
     *         description="System is healthy",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="status", type="string", example="healthy"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="service", type="string", example="Direct Meds API")
     *             )
     *         )
     *     )
     * )
     * System health check (defined inline in api.php)
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'service' => 'Direct Meds API',
        ]);
    }

    /**
     * Authenticated health check (defined inline in api.php)
     * This endpoint is documented with the middleware group in api.php
     */
    public function healthAuthenticated(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'user' => $request->user()->only(['id', 'email', 'user_type']),
            'permissions_count' => $request->user()->getAllPermissions()->count(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    }
}