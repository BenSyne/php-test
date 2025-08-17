<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\ApiDocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\HealthController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('home.about');
Route::get('/services', [HomeController::class, 'services'])->name('home.services');
Route::get('/contact', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('home.contact.submit');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('home.privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('home.terms');
Route::get('/faq', [HomeController::class, 'faq'])->name('home.faq');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth', 'user.status'])->group(function () {
    // Simple dashboard for all authenticated users
    Route::get('/dashboard', function () {
        // Show the new admin dashboard
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    // HIPAA Acknowledgment routes
    Route::get('/hipaa/acknowledge', function () {
        return view('auth.hipaa-acknowledgment');
    })->name('hipaa.form');

    Route::post('/hipaa/acknowledge', function (Illuminate\Http\Request $request) {
        $request->validate([
            'acknowledged' => ['required', 'boolean', 'accepted'],
        ]);

        $user = auth()->user();
        $user->acknowledgeHipaa($request->ip());

        activity('hipaa_acknowledged')
            ->causedBy($user)
            ->withProperties([
                'acknowledgment_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log("HIPAA acknowledgment completed for user: {$user->email}");

        return redirect()->intended(route('dashboard'))
            ->with('success', 'HIPAA compliance acknowledged successfully.');
    })->name('hipaa.acknowledge');

    // Two-factor authentication routes
    Route::get('/2fa', [TwoFactorController::class, 'show'])
        ->name('2fa.show');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])
        ->name('2fa.enable');
    Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm'])
        ->name('2fa.confirm');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])
        ->name('2fa.disable');
    Route::post('/2fa/recovery-codes', [TwoFactorController::class, 'generateRecoveryCodes'])
        ->name('2fa.recovery-codes');

    // Dashboard routes requiring HIPAA acknowledgment
    Route::middleware(['hipaa.acknowledge'])->group(function () {

        // User type specific dashboards
        Route::get('/patient/dashboard', [PatientController::class, 'dashboard'])->name('patient.dashboard')->middleware('role:patient');

        Route::get('/pharmacist/dashboard', function () {
            return view('dashboards.pharmacist', [
                'user' => auth()->user()->load('profile'),
            ]);
        })->name('pharmacist.dashboard')->middleware('role:pharmacist|pharmacy-manager|pharmacy-technician');

        Route::get('/prescriber/dashboard', function () {
            return view('dashboards.prescriber', [
                'user' => auth()->user()->load('profile'),
            ]);
        })->name('prescriber.dashboard')->middleware('role:prescriber');

        Route::get('/admin/dashboard', function () {
            return view('dashboards.admin', [
                'user' => auth()->user()->load('profile'),
            ]);
        })->name('admin.dashboard')->middleware('role:admin|super-admin');

        // Profile management routes
        Route::get('/profile', function () {
            return view('profile.show', [
                'user' => auth()->user()->load('profile'),
            ]);
        })->name('profile.show');

        Route::get('/profile/edit', function () {
            return view('profile.edit', [
                'user' => auth()->user()->load('profile'),
            ]);
        })->name('profile.edit');

        // Patient Portal Routes
        Route::prefix('patient')->name('patient.')->middleware('role:patient')->group(function () {
            // Prescriptions
            Route::get('/prescriptions', [PatientController::class, 'prescriptions'])->name('prescriptions');
            Route::get('/prescriptions/{prescription}', [PatientController::class, 'showPrescription'])->name('prescriptions.show');
            
            // Orders
            Route::get('/orders', [PatientController::class, 'orders'])->name('orders');
            Route::get('/orders/{order}', [PatientController::class, 'showOrder'])->name('orders.show');
            
            // Refills
            Route::get('/refills', [PatientController::class, 'refills'])->name('refills');
            Route::post('/refills/{prescription}', [PatientController::class, 'requestRefill'])->name('refills.request');
            
            // Profile Management
            Route::get('/profile', [PatientController::class, 'profile'])->name('profile');
            Route::get('/profile/edit', [PatientController::class, 'editProfile'])->name('profile.edit');
            Route::put('/profile', [PatientController::class, 'updateProfile'])->name('profile.update');
            
            // Prescription Upload
            Route::get('/upload-prescription', [PatientController::class, 'uploadPrescription'])->name('upload-prescription');
            Route::post('/upload-prescription', [PatientController::class, 'storePrescription'])->name('upload-prescription.store');
            
            // Messages & Help (placeholders)
            Route::get('/messages', [PatientController::class, 'messages'])->name('messages');
            Route::get('/help', [PatientController::class, 'help'])->name('help');
        });

        // Shopping Cart routes
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add-item', [CartController::class, 'addItem'])->name('add-item');
            Route::patch('/items/{cartItem}', [CartController::class, 'updateItem'])->name('update-item');
            Route::delete('/items/{cartItem}', [CartController::class, 'removeItem'])->name('remove-item');
            Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
            Route::get('/count', [CartController::class, 'count'])->name('count');
            Route::get('/validate', [CartController::class, 'validateForCheckout'])->name('validate');
            Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('apply-coupon');
            Route::patch('/shipping', [CartController::class, 'updateShipping'])->name('update-shipping');
            Route::post('/merge-guest', [CartController::class, 'mergeGuestCart'])->name('merge-guest');
            Route::get('/abandoned', [CartController::class, 'abandonedCarts'])->name('abandoned');
            Route::post('/restore/{abandonedCart}', [CartController::class, 'restore'])->name('restore');
        });

        // Checkout routes
        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::get('/', [CheckoutController::class, 'index'])->name('index');
            Route::post('/process', [CheckoutController::class, 'process'])->name('process');
            Route::post('/validate', [CheckoutController::class, 'validate'])->name('validate');
            Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('calculate-shipping');
            Route::post('/apply-discount', [CheckoutController::class, 'applyDiscount'])->name('apply-discount');
            Route::post('/save-draft', [CheckoutController::class, 'saveDraft'])->name('save-draft');
            Route::get('/load-draft', [CheckoutController::class, 'loadDraft'])->name('load-draft');
        });

        // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::get('/{order}/confirmation', [OrderController::class, 'confirmation'])->name('confirmation');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            
            // Pharmacy staff routes
            Route::middleware(['role:pharmacist|pharmacy-manager|admin|super-admin'])->group(function () {
                Route::post('/{order}/process', [OrderController::class, 'process'])->name('process');
                Route::post('/{order}/ship', [OrderController::class, 'ship'])->name('ship');
                Route::post('/{order}/delivered', [OrderController::class, 'delivered'])->name('delivered');
                Route::post('/{order}/verify-prescriptions', [OrderController::class, 'verifyPrescriptions'])->name('verify-prescriptions');
                Route::post('/items/{orderItem}/fulfill', [OrderController::class, 'fulfillItem'])->name('fulfill-item');
                Route::get('/analytics', [OrderController::class, 'analytics'])->name('analytics');
            });
        });

        // Public order tracking (no authentication required for tracking)
        Route::get('/track/{orderNumber}', [OrderController::class, 'track'])->name('orders.track');

        // Admin dashboard and management routes
        Route::prefix('admin')->name('admin.')->middleware(['role:admin|super-admin'])->group(function () {
            // Main dashboard
            Route::get('/dashboard', function () {
                return view('admin.dashboard');
            })->name('dashboard');
            
            // Products management
            Route::get('/products', function () {
                return view('admin.products');
            })->name('products');
            
            // Prescriptions management
            Route::get('/prescriptions', function () {
                return view('admin.prescriptions');
            })->name('prescriptions');
            
            // Orders management
            Route::get('/orders', function () {
                return view('admin.orders');
            })->name('orders');
            
            // Users management
            Route::get('/users', function () {
                return view('admin.users');
            })->name('users');
            
            // Compliance dashboard
            Route::get('/compliance', function () {
                return view('admin.compliance');
            })->name('compliance');
            
            // Analytics
            Route::get('/analytics', function () {
                return view('admin.analytics');
            })->name('analytics');
            
            // Settings
            Route::get('/settings', function () {
                return view('admin.settings');
            })->name('settings');
            
            // User management
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [App\Http\Controllers\AdminDashboardController::class, 'users'])->name('index');
                Route::get('/create', function () { return view('admin.users.create'); })->name('create');
                Route::get('/roles', function () { return view('admin.users.roles'); })->name('roles');
                Route::get('/{user}', function ($user) { return view('admin.users.show', compact('user')); })->name('show');
                Route::get('/{user}/edit', function ($user) { return view('admin.users.edit', compact('user')); })->name('edit');
                Route::patch('/{user}/activate', function ($user) { /* Activate user logic */ })->name('activate');
                Route::patch('/{user}/deactivate', function ($user) { /* Deactivate user logic */ })->name('deactivate');
            });
            
            // Inventory management
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [App\Http\Controllers\AdminDashboardController::class, 'inventory'])->name('index');
                Route::get('/low-stock', function () { return view('admin.inventory.low-stock'); })->name('low-stock');
                Route::get('/create', function () { return view('admin.inventory.create'); })->name('create');
                Route::get('/{product}', function ($product) { return view('admin.inventory.show', compact('product')); })->name('show');
                Route::get('/{product}/edit', function ($product) { return view('admin.inventory.edit', compact('product')); })->name('edit');
                Route::get('/{product}/restock', function ($product) { return view('admin.inventory.restock', compact('product')); })->name('restock');
                Route::get('/export', function () { /* Export inventory logic */ })->name('export');
                Route::get('/bulk-update', function () { return view('admin.inventory.bulk-update'); })->name('bulk-update');
            });
            
            // Order management
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [App\Http\Controllers\AdminDashboardController::class, 'orders'])->name('index');
                Route::get('/pending', function () { return redirect()->route('admin.orders.index', ['status' => 'pending']); })->name('pending');
                Route::get('/fulfillment', function () { return view('admin.orders.fulfillment'); })->name('fulfillment');
                Route::get('/{order}', function ($order) { return view('admin.orders.show', compact('order')); })->name('show');
                Route::post('/{order}/process', function ($order) { /* Process order logic */ })->name('process');
                Route::post('/{order}/ship', function ($order) { /* Ship order logic */ })->name('ship');
                Route::post('/{order}/delivered', function ($order) { /* Mark delivered logic */ })->name('delivered');
                Route::post('/{order}/cancel', function ($order) { /* Cancel order logic */ })->name('cancel');
                Route::get('/export', function () { /* Export orders logic */ })->name('export');
                Route::get('/batch-actions', function () { return view('admin.orders.batch-actions'); })->name('batch-actions');
                Route::get('/status-check', function () { return response()->json(['hasUpdates' => false]); })->name('status-check');
            });
            
            // Prescription management
            Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
                Route::get('/', function () { return view('admin.prescriptions.index'); })->name('index');
                Route::get('/pending', function () { return view('admin.prescriptions.pending'); })->name('pending');
            });
            
            // Reports and analytics
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [App\Http\Controllers\AdminDashboardController::class, 'reports'])->name('index');
                Route::get('/sales', function () { return view('admin.reports.sales'); })->name('sales');
                Route::get('/inventory', function () { return view('admin.reports.inventory'); })->name('inventory');
                Route::get('/sales/export', function () { /* Export sales report */ })->name('sales.export');
                Route::get('/inventory/export', function () { /* Export inventory report */ })->name('inventory.export');
                Route::get('/users/export', function () { /* Export users report */ })->name('users.export');
                Route::get('/prescriptions/export', function () { /* Export prescriptions report */ })->name('prescriptions.export');
            });
            
            // Payment management
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/failed', function () { return view('admin.payments.failed'); })->name('failed');
            });
            
            // Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/general', function () { return view('admin.settings.general'); })->name('general');
                Route::get('/notifications', function () { return view('admin.settings.notifications'); })->name('notifications');
                Route::get('/integrations', function () { return view('admin.settings.integrations'); })->name('integrations');
            });
        });

        // Compliance and audit routes
        Route::prefix('compliance')->name('compliance.')->middleware(['role:admin|compliance_officer|pharmacist_manager'])->group(function () {
            Route::get('/dashboard', [ComplianceController::class, 'dashboard'])->name('dashboard');
            Route::get('/audit-logs', [ComplianceController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/reports', [ComplianceController::class, 'reports'])->name('reports');
            
            // Report generation and management
            Route::post('/generate-report', [ComplianceController::class, 'generateReport'])->name('generate-report');
            Route::get('/reports/{report}/download', [ComplianceController::class, 'downloadReport'])->name('download-report');
            Route::post('/reports/{report}/review', [ComplianceController::class, 'reviewReport'])->name('review-report');
            
            // API endpoints for dashboard
            Route::get('/metrics', [ComplianceController::class, 'metrics'])->name('metrics');
            Route::get('/hipaa-summary', [ComplianceController::class, 'hipaaAuditSummary'])->name('hipaa-summary');
            Route::get('/dea-summary', [ComplianceController::class, 'deaReportingSummary'])->name('dea-summary');
            Route::get('/retention-summary', [ComplianceController::class, 'dataRetentionSummary'])->name('retention-summary');
            
            // Data retention management
            Route::post('/retention/execute', [ComplianceController::class, 'executeRetentionCleanup'])->name('retention.execute');
        });
    });
});

// Guest cart routes (no authentication required)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('guest.index');
    Route::post('/add-item', [CartController::class, 'addItem'])->name('guest.add-item');
    Route::patch('/items/{cartItem}', [CartController::class, 'updateItem'])->name('guest.update-item');
    Route::delete('/items/{cartItem}', [CartController::class, 'removeItem'])->name('guest.remove-item');
    Route::delete('/clear', [CartController::class, 'clear'])->name('guest.clear');
    Route::get('/count', [CartController::class, 'count'])->name('guest.count');
    Route::patch('/shipping', [CartController::class, 'updateShipping'])->name('guest.update-shipping');
});

// API Documentation routes (public)
Route::prefix('api/docs')->name('api.docs.')->group(function () {
    Route::get('/', [ApiDocumentationController::class, 'index'])->name('index');
    Route::get('/developer-guide', [ApiDocumentationController::class, 'developerGuide'])->name('developer-guide');
    Route::get('/authentication-guide', [ApiDocumentationController::class, 'authenticationGuide'])->name('authentication-guide');
    Route::get('/testing', [ApiDocumentationController::class, 'testingInterface'])->name('testing');
    Route::get('/error-codes', [ApiDocumentationController::class, 'errorCodes'])->name('error-codes');
    Route::get('/json', [ApiDocumentationController::class, 'json'])->name('json');
});

// L5-Swagger routes
Route::get('/api/docs/swagger', function () {
    return view('l5-swagger::index');
})->name('api.docs.swagger');

// System status route (public)
Route::get('/status', function () {
    return response()->json([
        'status' => 'operational',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'environment' => app()->environment(),
        'database' => 'connected',
        'features' => [
            'authentication' => 'enabled',
            'two_factor_auth' => 'enabled',
            'hipaa_compliance' => 'enabled',
            'audit_logging' => 'enabled',
        ],
    ]);
})->name('system.status');

// Health check routes for Docker/Kubernetes
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/health/liveness', [HealthController::class, 'liveness'])->name('health.liveness');
Route::get('/health/readiness', [HealthController::class, 'readiness'])->name('health.readiness');
