<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'apiStore'])
        ->name('api.auth.register');
    
    Route::post('/login', [LoginController::class, 'apiLogin'])
        ->name('api.auth.login');
});

// Protected routes requiring authentication
Route::middleware(['auth:sanctum'])->group(function () {
    
    // User profile information
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load('profile'),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
            'roles' => $request->user()->getRoleNames(),
        ]);
    })->name('api.user.profile');

    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'apiLogout'])
            ->name('api.auth.logout');
        
        Route::post('/logout-all', [LoginController::class, 'apiLogoutAll'])
            ->name('api.auth.logout-all');
    });

    // Two-factor authentication routes
    Route::prefix('2fa')->group(function () {
        Route::get('/status', [TwoFactorController::class, 'apiStatus'])
            ->name('api.2fa.status');
        
        Route::post('/enable', [TwoFactorController::class, 'apiEnable'])
            ->name('api.2fa.enable');
        
        Route::post('/confirm', [TwoFactorController::class, 'apiConfirm'])
            ->name('api.2fa.confirm');
        
        Route::post('/disable', [TwoFactorController::class, 'apiDisable'])
            ->name('api.2fa.disable');
        
        Route::post('/recovery-codes', [TwoFactorController::class, 'apiGenerateRecoveryCodes'])
            ->name('api.2fa.recovery-codes');
    });

    // HIPAA Acknowledgment routes
    Route::prefix('hipaa')->group(function () {
        Route::post('/acknowledge', function (Request $request) {
            $request->validate([
                'acknowledged' => ['required', 'boolean', 'accepted'],
            ]);

            $user = $request->user();
            $user->acknowledgeHipaa($request->ip());

            activity('hipaa_acknowledged')
                ->causedBy($user)
                ->withProperties([
                    'acknowledgment_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'api_request' => true,
                ])
                ->log("HIPAA acknowledgment completed via API for user: {$user->email}");

            return response()->json([
                'message' => 'HIPAA compliance acknowledged successfully',
                'acknowledged_at' => $user->fresh()->hipaa_acknowledged_at,
            ]);
        })->name('api.hipaa.acknowledge');

        Route::get('/status', function (Request $request) {
            $user = $request->user();
            
            return response()->json([
                'hipaa_acknowledged' => $user->hasAcknowledgedHipaa(),
                'acknowledged_at' => $user->hipaa_acknowledged_at,
                'acknowledgment_ip' => $user->hipaa_acknowledgment_ip,
            ]);
        })->name('api.hipaa.status');
    });

    // User Tokens Management
    Route::prefix('tokens')->group(function () {
        Route::get('/', function (Request $request) {
            return response()->json([
                'tokens' => $request->user()->tokens()->select('id', 'name', 'abilities', 'last_used_at', 'created_at')->get(),
            ]);
        })->name('api.tokens.index');

        Route::delete('/{token}', function (Request $request, $tokenId) {
            $user = $request->user();
            $token = $user->tokens()->where('id', $tokenId)->first();

            if (!$token) {
                return response()->json(['message' => 'Token not found'], 404);
            }

            $token->delete();

            activity('api_token_revoked')
                ->causedBy($user)
                ->withProperties([
                    'token_id' => $tokenId,
                    'token_name' => $token->name,
                    'revoked_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("API token revoked: {$token->name}");

            return response()->json(['message' => 'Token revoked successfully']);
        })->name('api.tokens.revoke');
    });

    // User Profile Management
    Route::prefix('profile')->middleware(['hipaa.acknowledge'])->group(function () {
        Route::get('/', function (Request $request) {
            return response()->json([
                'profile' => $request->user()->profile,
            ]);
        })->name('api.profile.show');

        Route::put('/', function (Request $request) {
            $user = $request->user();
            $profile = $user->profile;

            if (!$profile) {
                return response()->json(['message' => 'Profile not found'], 404);
            }

            $validatedData = $request->validate([
                'first_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'preferred_name' => ['nullable', 'string', 'max:255'],
                'phone_mobile' => ['nullable', 'string', 'max:20'],
                'address_line_1' => ['nullable', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['nullable', 'string', 'max:10'],
                'consent_to_email' => ['boolean'],
                'consent_to_text' => ['boolean'],
                'consent_to_marketing' => ['boolean'],
            ]);

            $profile->update($validatedData);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => $profile->fresh(),
            ]);
        })->name('api.profile.update');
    });

    // Product Catalog API Routes
    Route::prefix('products')->middleware(['hipaa.acknowledge'])->group(function () {
        // Product listing and search
        Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
        Route::get('/search', [ProductController::class, 'search'])->name('api.products.search');
        
        // Product details
        Route::get('/{product}', [ProductController::class, 'show'])->name('api.products.show');
        
        // Drug interactions
        Route::get('/{product}/interactions', [ProductController::class, 'interactions'])->name('api.products.interactions');
        Route::post('/check-interactions', [ProductController::class, 'checkInteractions'])->name('api.products.check-interactions');
        
        // Category and manufacturer filtering
        Route::get('/category/{categorySlug}', [ProductController::class, 'byCategory'])->name('api.products.by-category');
        Route::get('/manufacturer/{manufacturerId}', [ProductController::class, 'byManufacturer'])->name('api.products.by-manufacturer');
        
        // Pharmacy management endpoints (require pharmacy role)
        Route::middleware(['role:pharmacy_admin|pharmacy_tech'])->group(function () {
            Route::get('/inventory/low-stock', [ProductController::class, 'lowStock'])->name('api.products.low-stock');
            Route::get('/inventory/expiring', [ProductController::class, 'expiring'])->name('api.products.expiring');
        });
    });

    // Prescription Management API Routes
    Route::prefix('prescriptions')->middleware(['hipaa.acknowledge'])->group(function () {
        // Main prescription CRUD operations
        Route::get('/', [PrescriptionController::class, 'index'])->name('api.prescriptions.index');
        Route::post('/', [PrescriptionController::class, 'store'])->name('api.prescriptions.store');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('api.prescriptions.show');
        Route::put('/{prescription}', [PrescriptionController::class, 'update'])->name('api.prescriptions.update');
        
        // Prescription workflow actions
        Route::post('/{prescription}/start-review', [PrescriptionController::class, 'startReview'])->name('api.prescriptions.start-review');
        Route::post('/{prescription}/verify', [PrescriptionController::class, 'verify'])->name('api.prescriptions.verify');
        Route::post('/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('api.prescriptions.dispense');
        Route::post('/{prescription}/refill', [PrescriptionController::class, 'refill'])->name('api.prescriptions.refill');
        Route::post('/{prescription}/cancel', [PrescriptionController::class, 'cancel'])->name('api.prescriptions.cancel');
        
        // Prescription file management
        Route::get('/{prescription}/files/{filename}', [PrescriptionController::class, 'downloadFile'])->name('api.prescriptions.download-file');
        
        // Audit and compliance
        Route::get('/{prescription}/audit-trail', [PrescriptionController::class, 'auditTrail'])->name('api.prescriptions.audit-trail');
        
        // Pharmacist-specific endpoints
        Route::middleware(['role:pharmacist|pharmacy_admin'])->group(function () {
            Route::get('/requires-review', [PrescriptionController::class, 'requiresReview'])->name('api.prescriptions.requires-review');
            Route::get('/controlled-substances', [PrescriptionController::class, 'controlledSubstances'])->name('api.prescriptions.controlled-substances');
        });
    });

    // Payment Management API Routes
    Route::prefix('payments')->middleware(['hipaa.acknowledge', 'payment.security'])->group(function () {
        // Payment CRUD operations
        Route::get('/', [PaymentController::class, 'index'])->name('api.payments.index');
        Route::get('/create', [PaymentController::class, 'create'])->name('api.payments.create');
        Route::post('/', [PaymentController::class, 'store'])->name('api.payments.store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('api.payments.show');
        Route::put('/{payment}', [PaymentController::class, 'update'])->name('api.payments.update');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('api.payments.destroy');
        
        // Payment actions
        Route::post('/{payment}/cancel', [PaymentController::class, 'cancel'])->name('api.payments.cancel');
        Route::post('/{payment}/refund', [PaymentController::class, 'refund'])->name('api.payments.refund');
        Route::post('/{payment}/retry', [PaymentController::class, 'retry'])->name('api.payments.retry');
        
        // Payment statistics and reporting
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('api.payments.statistics');
        Route::get('/payment-methods', [PaymentController::class, 'paymentMethods'])->name('api.payments.methods');
        
        // Admin-only endpoints
        Route::middleware(['role:admin|pharmacy_admin'])->group(function () {
            Route::post('/{payment}/capture', [PaymentController::class, 'capture'])->name('api.payments.capture');
            Route::post('/{payment}/review', [PaymentController::class, 'reviewPayment'])->name('api.payments.review');
        });
    });

    // System Health Check for authenticated users
    Route::get('/health', function (Request $request) {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'user' => $request->user()->only(['id', 'email', 'user_type']),
            'permissions_count' => $request->user()->getAllPermissions()->count(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    })->name('api.health.authenticated');
});

// Webhook endpoints (public, no authentication required)
Route::prefix('webhooks')->group(function () {
    Route::post('/stripe', [WebhookController::class, 'stripe'])->name('api.webhooks.stripe');
    Route::post('/payment-status', [WebhookController::class, 'paymentStatus'])->name('api.webhooks.payment-status');
});

// Public health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'service' => 'Direct Meds API',
    ]);
})->name('api.health.public');

// API Documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'api_name' => 'Direct Meds Pharmacy API',
        'version' => '1.0.0',
        'description' => 'HIPAA-compliant pharmacy management system API',
        'endpoints' => [
            'authentication' => [
                'POST /api/auth/register' => 'Register new user',
                'POST /api/auth/login' => 'Login user',
                'POST /api/auth/logout' => 'Logout current session',
                'POST /api/auth/logout-all' => 'Logout all sessions',
            ],
            'two_factor' => [
                'GET /api/2fa/status' => 'Get 2FA status',
                'POST /api/2fa/enable' => 'Enable 2FA',
                'POST /api/2fa/confirm' => 'Confirm 2FA setup',
                'POST /api/2fa/disable' => 'Disable 2FA',
                'POST /api/2fa/recovery-codes' => 'Generate new recovery codes',
            ],
            'user' => [
                'GET /api/user' => 'Get authenticated user profile',
                'GET /api/profile' => 'Get user profile details',
                'PUT /api/profile' => 'Update user profile',
            ],
            'tokens' => [
                'GET /api/tokens' => 'List API tokens',
                'DELETE /api/tokens/{id}' => 'Revoke API token',
            ],
            'hipaa' => [
                'GET /api/hipaa/status' => 'Get HIPAA acknowledgment status',
                'POST /api/hipaa/acknowledge' => 'Acknowledge HIPAA compliance',
            ],
            'products' => [
                'GET /api/products' => 'List products with filtering and search',
                'GET /api/products/search' => 'Search products by various criteria',
                'GET /api/products/{id}' => 'Get product details by ID, slug, or NDC',
                'GET /api/products/{id}/interactions' => 'Get drug interaction information',
                'POST /api/products/check-interactions' => 'Check interactions between multiple drugs',
                'GET /api/products/category/{slug}' => 'Get products by category',
                'GET /api/products/manufacturer/{id}' => 'Get products by manufacturer',
                'GET /api/products/inventory/low-stock' => 'Get low stock items (pharmacy only)',
                'GET /api/products/inventory/expiring' => 'Get expiring products (pharmacy only)',
            ],
            'prescriptions' => [
                'GET /api/prescriptions' => 'List prescriptions with filtering and search',
                'POST /api/prescriptions' => 'Upload new prescription',
                'GET /api/prescriptions/{id}' => 'Get prescription details',
                'PUT /api/prescriptions/{id}' => 'Update prescription (pharmacist only)',
                'POST /api/prescriptions/{id}/start-review' => 'Start pharmacist review',
                'POST /api/prescriptions/{id}/verify' => 'Verify/reject/hold prescription',
                'POST /api/prescriptions/{id}/dispense' => 'Dispense prescription to patient',
                'POST /api/prescriptions/{id}/refill' => 'Request prescription refill',
                'POST /api/prescriptions/{id}/cancel' => 'Cancel prescription',
                'GET /api/prescriptions/{id}/files/{filename}' => 'Download prescription file',
                'GET /api/prescriptions/{id}/audit-trail' => 'Get prescription audit trail',
                'GET /api/prescriptions/requires-review' => 'List prescriptions requiring review (pharmacist only)',
                'GET /api/prescriptions/controlled-substances' => 'List controlled substance prescriptions (pharmacist only)',
            ],
            'payments' => [
                'GET /api/payments' => 'List payments with filtering and search',
                'GET /api/payments/create' => 'Get payment creation options',
                'POST /api/payments' => 'Process new payment',
                'GET /api/payments/{id}' => 'Get payment details',
                'PUT /api/payments/{id}' => 'Update payment information',
                'DELETE /api/payments/{id}' => 'Delete cancelled/failed payment',
                'POST /api/payments/{id}/cancel' => 'Cancel pending payment',
                'POST /api/payments/{id}/refund' => 'Process payment refund',
                'POST /api/payments/{id}/retry' => 'Retry failed payment',
                'GET /api/payments/statistics' => 'Get payment statistics',
                'GET /api/payments/payment-methods' => 'Get user payment methods',
                'POST /api/payments/{id}/capture' => 'Capture authorized payment (admin only)',
                'POST /api/payments/{id}/review' => 'Manual payment review (admin only)',
            ],
            'webhooks' => [
                'POST /api/webhooks/stripe' => 'Stripe webhook endpoint',
                'POST /api/webhooks/payment-status' => 'Payment status updates',
            ],
            'system' => [
                'GET /api/health' => 'System health check',
                'GET /api/docs' => 'API documentation',
            ],
        ],
        'authentication' => [
            'type' => 'Bearer Token (Sanctum)',
            'header' => 'Authorization: Bearer {token}',
        ],
        'compliance' => [
            'hipaa' => 'All PHI access is logged and audited',
            'audit_logging' => 'Comprehensive audit trail maintained',
            'data_retention' => '7 years for HIPAA compliance',
        ],
    ]);
})->name('api.docs');