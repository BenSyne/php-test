<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Payment::with(['order', 'paymentMethod', 'user'])
            ->where('user_id', $user->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $payments = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => [
                'total_amount' => $payments->sum('amount'),
                'completed_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
                'refunded_amount' => $payments->sum('amount_refunded'),
                'pending_count' => $payments->where('status', Payment::STATUS_PENDING)->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get user's payment methods
        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available payment gateways
        $gateways = PaymentMethod::getGateways();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => $paymentMethods,
                'gateways' => $gateways,
                'supported_currencies' => ['USD'],
                'default_currency' => 'USD',
            ]
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01|max:99999.99',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'customer_notes' => 'nullable|string|max:1000',
            'auto_capture' => 'boolean',
            'requires_3ds' => 'boolean',
            'insurance_copay' => 'nullable|numeric|min:0',
            'insurance_coverage' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
            // For new payment method creation
            'payment_method' => 'nullable|array',
            'payment_method.type' => 'required_with:payment_method|in:card,bank_account,insurance',
            'payment_method.gateway_method_id' => 'required_with:payment_method|string',
            'billing_address' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $paymentData = $request->validated();
            $paymentData['user_id'] = $user->id;

            // Validate order ownership if order_id provided
            if ($paymentData['order_id']) {
                $order = Order::where('id', $paymentData['order_id'])
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found or access denied'
                    ], 404);
                }
            }

            // Create or validate payment method
            if ($request->has('payment_method')) {
                $paymentMethod = $this->paymentService->createPaymentMethod(
                    $user,
                    $request->payment_method,
                    $request->billing_address ?? []
                );
                $paymentData['payment_method_id'] = $paymentMethod->id;
            } elseif ($paymentData['payment_method_id']) {
                $paymentMethod = PaymentMethod::where('id', $paymentData['payment_method_id'])
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$paymentMethod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment method not found or inactive'
                    ], 404);
                }
            }

            // Process the payment
            $payment = $this->paymentService->processPayment($paymentData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment' => $payment->load(['paymentMethod', 'order']),
                    'requires_action' => $payment->requires_3ds && !$payment->passed_3ds,
                    'next_action' => $payment->requires_3ds && !$payment->passed_3ds ? '3ds_authentication' : null,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Payment processing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->except(['payment_method']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        // Check if user owns this payment or has admin access
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $payment->load([
            'paymentMethod', 
            'order', 
            'parentPayment', 
            'childPayments',
            'reviewedBy',
            'refundedBy'
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        // Check permissions
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:255',
            'customer_notes' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only allow updating certain fields after payment is processed
        $allowedFields = ['description', 'customer_notes', 'metadata'];
        if (Gate::allows('admin-access')) {
            $allowedFields[] = 'internal_notes';
        }

        $updateData = $request->only($allowedFields);
        $payment->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment->fresh()
        ]);
    }

    /**
     * Cancel a payment.
     */
    public function cancel(Request $request, Payment $payment): JsonResponse
    {
        // Check permissions
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if (!in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])) {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be cancelled in current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->paymentService->cancelPayment($payment, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Capture an authorized payment.
     */
    public function capture(Request $request, Payment $payment): JsonResponse
    {
        // Only admins can capture payments
        if (!Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if (!$payment->can_be_captured) {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be captured'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $captureAmount = $request->amount ?? $payment->amount_authorized;
            $this->paymentService->capturePayment($payment, $captureAmount);

            return response()->json([
                'success' => true,
                'message' => 'Payment captured successfully',
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to capture payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refund a payment.
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        // Check permissions
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if (!$payment->can_be_refunded) {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be refunded'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:' . $payment->remaining_refundable_amount,
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $refund = $this->paymentService->refundPayment(
                $payment,
                $request->amount,
                $request->reason,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'original_payment' => $payment->fresh(),
                    'refund_payment' => $refund,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual review operations (admin only).
     */
    public function reviewPayment(Request $request, Payment $payment): JsonResponse
    {
        if (!Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if (!$payment->manual_review_required) {
            return response()->json([
                'success' => false,
                'message' => 'Payment does not require manual review'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->action === 'approve') {
                $payment->passManualReview(Auth::id());
                $message = 'Payment approved successfully';
            } else {
                $payment->failManualReview(Auth::id(), $request->notes);
                $message = 'Payment rejected';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to review payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Payment::where('user_id', $user->id);

        // Apply date filter
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $payments = $query->get();

        $statistics = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_payments' => $payments->where('status', Payment::STATUS_COMPLETED)->count(),
            'completed_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'pending_payments' => $payments->where('status', Payment::STATUS_PENDING)->count(),
            'pending_amount' => $payments->where('status', Payment::STATUS_PENDING)->sum('amount'),
            'failed_payments' => $payments->where('status', Payment::STATUS_FAILED)->count(),
            'refunded_amount' => $payments->sum('amount_refunded'),
            'average_payment' => $payments->avg('amount'),
            'by_gateway' => $payments->groupBy('gateway')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            }),
            'by_method_type' => $payments->groupBy('payment_method_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Retry a failed payment.
     */
    public function retry(Payment $payment): JsonResponse
    {
        // Check permissions
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if ($payment->status !== Payment::STATUS_FAILED) {
            return response()->json([
                'success' => false,
                'message' => 'Only failed payments can be retried'
            ], 422);
        }

        if ($payment->retry_count >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum retry attempts reached'
            ], 422);
        }

        try {
            $this->paymentService->retryPayment($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment retry initiated',
                'data' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment methods for current user.
     */
    public function paymentMethods(): JsonResponse
    {
        $user = Auth::user();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    /**
     * Remove the specified payment.
     * Note: Payments are soft-deleted for audit purposes.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        // Only allow deletion of cancelled or failed payments
        if (!in_array($payment->status, [Payment::STATUS_CANCELLED, Payment::STATUS_FAILED])) {
            return response()->json([
                'success' => false,
                'message' => 'Only cancelled or failed payments can be deleted'
            ], 422);
        }

        // Check permissions
        if ($payment->user_id !== Auth::id() && !Gate::allows('admin-access')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }
}