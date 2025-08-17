<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    protected array $config;
    protected array $stripeConfig;

    public function __construct()
    {
        $this->config = config('services.payments', [
            'default_gateway' => 'stripe',
            'test_mode' => env('PAYMENT_TEST_MODE', true),
            'webhook_tolerance' => 300, // 5 minutes
        ]);

        $this->stripeConfig = [
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'api_version' => '2023-10-16',
        ];
    }

    /**
     * Process a payment
     */
    public function processPayment(array $paymentData): Payment
    {
        // Create payment record
        $payment = $this->createPaymentRecord($paymentData);

        try {
            // Perform pre-payment checks
            $this->performPrePaymentChecks($payment);

            // Process based on gateway
            $gateway = $paymentData['gateway'] ?? $this->config['default_gateway'];
            
            switch ($gateway) {
                case 'stripe':
                    return $this->processStripePayment($payment);
                case 'square':
                    return $this->processSquarePayment($payment);
                default:
                    throw new \Exception("Unsupported payment gateway: {$gateway}");
            }

        } catch (\Exception $e) {
            $payment->fail(
                'processing_error',
                $e->getMessage(),
                ['exception' => get_class($e)]
            );
            throw $e;
        }
    }

    /**
     * Create payment method
     */
    public function createPaymentMethod(User $user, array $paymentMethodData, array $billingAddress = []): PaymentMethod
    {
        $gateway = $paymentMethodData['gateway'] ?? $this->config['default_gateway'];

        DB::beginTransaction();
        
        try {
            // Create gateway payment method first
            $gatewayMethodData = $this->createGatewayPaymentMethod($gateway, $paymentMethodData, $user);

            // Create local payment method record
            $paymentMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'type' => $paymentMethodData['type'],
                'gateway' => $gateway,
                'gateway_method_id' => $gatewayMethodData['id'],
                'card_brand' => $gatewayMethodData['card_brand'] ?? null,
                'card_last_four' => $gatewayMethodData['card_last_four'] ?? null,
                'card_exp_month' => $gatewayMethodData['card_exp_month'] ?? null,
                'card_exp_year' => $gatewayMethodData['card_exp_year'] ?? null,
                'card_fingerprint' => $gatewayMethodData['card_fingerprint'] ?? null,
                'bank_name' => $gatewayMethodData['bank_name'] ?? null,
                'bank_account_type' => $gatewayMethodData['bank_account_type'] ?? null,
                'bank_account_last_four' => $gatewayMethodData['bank_account_last_four'] ?? null,
                'billing_name' => $billingAddress['name'] ?? null,
                'billing_street_address' => $billingAddress['street_address'] ?? null,
                'billing_city' => $billingAddress['city'] ?? null,
                'billing_state' => $billingAddress['state'] ?? null,
                'billing_postal_code' => $billingAddress['postal_code'] ?? null,
                'billing_country' => $billingAddress['country'] ?? 'US',
                'tokenization_method' => 'gateway',
                'is_verified' => $gatewayMethodData['verified'] ?? false,
            ]);

            // Set as default if this is the user's first payment method
            if (!$user->paymentMethods()->where('is_active', true)->exists()) {
                $paymentMethod->setAsDefault();
            }

            // Perform compliance checks
            $paymentMethod->validatePciCompliance();
            $paymentMethod->performFraudChecks();

            DB::commit();
            return $paymentMethod;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create payment method', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'gateway' => $gateway,
            ]);
            throw $e;
        }
    }

    /**
     * Capture a payment
     */
    public function capturePayment(Payment $payment, ?float $amount = null): Payment
    {
        if (!$payment->can_be_captured) {
            throw new \Exception('Payment cannot be captured');
        }

        $captureAmount = $amount ?? $payment->amount_authorized;

        try {
            // Capture through gateway
            $result = $this->captureGatewayPayment($payment, $captureAmount);

            // Update payment record
            $payment->capture($captureAmount);

            // Update gateway transaction details
            $payment->update([
                'gateway_charge_id' => $result['charge_id'] ?? null,
                'amount_fee' => $result['fee'] ?? null,
                'amount_net' => $captureAmount - ($result['fee'] ?? 0),
                'gateway_response' => $result,
            ]);

            return $payment;

        } catch (\Exception $e) {
            Log::error('Payment capture failed', [
                'payment_id' => $payment->id,
                'amount' => $captureAmount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, float $amount, string $reason, ?string $notes = null): Payment
    {
        if (!$payment->can_be_refunded) {
            throw new \Exception('Payment cannot be refunded');
        }

        try {
            // Process refund through gateway
            $refundResult = $this->processGatewayRefund($payment, $amount, $reason);

            // Create refund payment record
            $refund = $payment->refund($amount, $reason);

            // Update with gateway details
            $refund->update([
                'gateway_transaction_id' => $refundResult['id'] ?? null,
                'refund_reference' => $refundResult['id'] ?? null,
                'refund_notes' => $notes,
                'gateway_response' => $refundResult,
            ]);

            return $refund;

        } catch (\Exception $e) {
            Log::error('Payment refund failed', [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(Payment $payment, ?string $reason = null): Payment
    {
        try {
            // Cancel through gateway if needed
            if ($payment->gateway_payment_intent_id) {
                $this->cancelGatewayPayment($payment);
            }

            // Update payment record
            $payment->cancel($reason);

            return $payment;

        } catch (\Exception $e) {
            Log::error('Payment cancellation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retry a failed payment
     */
    public function retryPayment(Payment $payment): Payment
    {
        if (!$payment->attemptRetry()) {
            throw new \Exception('Payment retry not allowed at this time');
        }

        try {
            // Retry the payment processing
            return $this->processPayment([
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'payment_method_id' => $payment->payment_method_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'description' => $payment->description,
                'metadata' => array_merge($payment->metadata ?? [], ['retry_of' => $payment->id]),
            ]);

        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'original_payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process Stripe webhook
     */
    public function processStripeWebhook(array $payload, string $signature): bool
    {
        try {
            // Verify webhook signature
            if (!$this->verifyStripeWebhookSignature($payload, $signature)) {
                throw new \Exception('Invalid webhook signature');
            }

            $event = $payload;
            $eventType = $event['type'];

            Log::info('Processing Stripe webhook', [
                'event_type' => $eventType,
                'event_id' => $event['id'],
            ]);

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentIntentSucceeded($event['data']['object']);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentIntentFailed($event['data']['object']);

                case 'charge.dispute.created':
                    return $this->handleChargeDispute($event['data']['object']);

                case 'invoice.payment_succeeded':
                    return $this->handleInvoicePaymentSucceeded($event['data']['object']);

                default:
                    Log::info('Unhandled Stripe webhook event type', ['type' => $eventType]);
                    return true;
            }

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Protected helper methods
     */

    protected function createPaymentRecord(array $paymentData): Payment
    {
        // Capture payment method snapshot for audit trail
        $paymentMethodSnapshot = null;
        if (isset($paymentData['payment_method_id'])) {
            $paymentMethod = PaymentMethod::find($paymentData['payment_method_id']);
            if ($paymentMethod) {
                $paymentMethodSnapshot = $paymentMethod->only([
                    'type', 'gateway', 'card_brand', 'card_last_four',
                    'card_exp_month', 'card_exp_year', 'bank_name',
                    'insurance_provider', 'billing_name', 'billing_city',
                    'billing_state', 'billing_postal_code'
                ]);
            }
        }

        return Payment::create([
            'user_id' => $paymentData['user_id'],
            'order_id' => $paymentData['order_id'] ?? null,
            'payment_method_id' => $paymentData['payment_method_id'] ?? null,
            'type' => Payment::TYPE_PAYMENT,
            'status' => Payment::STATUS_PENDING,
            'gateway' => $paymentData['gateway'] ?? $this->config['default_gateway'],
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'USD',
            'description' => $paymentData['description'] ?? null,
            'customer_notes' => $paymentData['customer_notes'] ?? null,
            'auto_capture' => $paymentData['auto_capture'] ?? true,
            'requires_3ds' => $paymentData['requires_3ds'] ?? false,
            'insurance_copay' => $paymentData['insurance_copay'] ?? null,
            'insurance_coverage' => $paymentData['insurance_coverage'] ?? null,
            'metadata' => $paymentData['metadata'] ?? null,
            'payment_method_snapshot' => $paymentMethodSnapshot,
            'payment_method_type' => $paymentMethodSnapshot['type'] ?? null,
            'card_last_four' => $paymentMethodSnapshot['card_last_four'] ?? null,
            'card_brand' => $paymentMethodSnapshot['card_brand'] ?? null,
        ]);
    }

    protected function performPrePaymentChecks(Payment $payment): void
    {
        // Fraud checks
        if ($payment->paymentMethod) {
            $fraudCheck = $payment->paymentMethod->performFraudChecks([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);

            $payment->update([
                'fraud_score' => $fraudCheck['fraud_score'],
                'fraud_check_result' => $fraudCheck['checks'],
            ]);

            if ($fraudCheck['recommendation'] === 'block') {
                throw new \Exception('Payment blocked due to fraud concerns');
            } elseif ($fraudCheck['recommendation'] === 'manual_review') {
                $payment->requireManualReview(['reason' => 'fraud_check_flagged']);
            }
        }

        // Compliance checks
        $complianceChecks = $payment->performComplianceChecks();
        if (!$complianceChecks['overall_compliant']) {
            throw new \Exception('Payment failed compliance checks');
        }

        // Amount validation
        if ($payment->amount <= 0) {
            throw new \Exception('Invalid payment amount');
        }

        // Order validation
        if ($payment->order && $payment->order->payment_status === Order::PAYMENT_PAID) {
            throw new \Exception('Order is already paid');
        }
    }

    protected function processStripePayment(Payment $payment): Payment
    {
        try {
            // Create Stripe PaymentIntent
            $intentData = [
                'amount' => (int)($payment->amount * 100), // Convert to cents
                'currency' => strtolower($payment->currency),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'order_id' => $payment->order_id,
                ],
                'description' => $payment->description,
                'capture_method' => $payment->auto_capture ? 'automatic' : 'manual',
            ];

            // Add payment method if available
            if ($payment->paymentMethod && $payment->paymentMethod->gateway_method_id) {
                $intentData['payment_method'] = $payment->paymentMethod->gateway_method_id;
                $intentData['confirm'] = true;
            }

            // Add 3D Secure if required
            if ($payment->requires_3ds) {
                $intentData['confirmation_method'] = 'manual';
                $intentData['confirm'] = false;
            }

            $intent = $this->makeStripeRequest('POST', '/payment_intents', $intentData);

            // Update payment with Stripe details
            $payment->update([
                'gateway_payment_intent_id' => $intent['id'],
                'status' => $this->mapStripeStatus($intent['status']),
                'gateway_response' => $intent,
            ]);

            // Handle different Stripe statuses
            switch ($intent['status']) {
                case 'succeeded':
                    $payment->capture();
                    break;

                case 'requires_action':
                    $payment->update([
                        'requires_3ds' => true,
                        '3ds_data' => $intent['next_action'] ?? null,
                    ]);
                    break;

                case 'requires_payment_method':
                    $payment->fail('payment_method_required', 'Payment method required');
                    break;

                default:
                    $payment->update(['status' => Payment::STATUS_PROCESSING]);
            }

            return $payment;

        } catch (\Exception $e) {
            Log::error('Stripe payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function processSquarePayment(Payment $payment): Payment
    {
        // Square payment processing would go here
        // This is a placeholder for Square integration
        throw new \Exception('Square payment processing not yet implemented');
    }

    protected function createGatewayPaymentMethod(string $gateway, array $paymentMethodData, User $user): array
    {
        switch ($gateway) {
            case 'stripe':
                return $this->createStripePaymentMethod($paymentMethodData, $user);
            case 'square':
                return $this->createSquarePaymentMethod($paymentMethodData, $user);
            default:
                throw new \Exception("Unsupported gateway: {$gateway}");
        }
    }

    protected function createStripePaymentMethod(array $paymentMethodData, User $user): array
    {
        try {
            // Create customer if needed
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Create payment method
            $method = $this->makeStripeRequest('POST', '/payment_methods', [
                'type' => $paymentMethodData['type'],
                'card' => $paymentMethodData['card'] ?? null,
                'billing_details' => $paymentMethodData['billing_details'] ?? null,
            ]);

            // Attach to customer
            $this->makeStripeRequest('POST', "/payment_methods/{$method['id']}/attach", [
                'customer' => $stripeCustomerId,
            ]);

            return [
                'id' => $method['id'],
                'card_brand' => $method['card']['brand'] ?? null,
                'card_last_four' => $method['card']['last4'] ?? null,
                'card_exp_month' => $method['card']['exp_month'] ?? null,
                'card_exp_year' => $method['card']['exp_year'] ?? null,
                'card_fingerprint' => $method['card']['fingerprint'] ?? null,
                'verified' => true,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment method creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function createSquarePaymentMethod(array $paymentMethodData, User $user): array
    {
        // Square payment method creation would go here
        throw new \Exception('Square payment method creation not yet implemented');
    }

    protected function captureGatewayPayment(Payment $payment, float $amount): array
    {
        switch ($payment->gateway) {
            case 'stripe':
                return $this->captureStripePayment($payment, $amount);
            default:
                throw new \Exception("Unsupported gateway: {$payment->gateway}");
        }
    }

    protected function captureStripePayment(Payment $payment, float $amount): array
    {
        $result = $this->makeStripeRequest('POST', "/payment_intents/{$payment->gateway_payment_intent_id}/capture", [
            'amount_to_capture' => (int)($amount * 100),
        ]);

        return [
            'charge_id' => $result['charges']['data'][0]['id'] ?? null,
            'fee' => ($result['charges']['data'][0]['balance_transaction']['fee'] ?? 0) / 100,
        ];
    }

    protected function processGatewayRefund(Payment $payment, float $amount, string $reason): array
    {
        switch ($payment->gateway) {
            case 'stripe':
                return $this->processStripeRefund($payment, $amount, $reason);
            default:
                throw new \Exception("Unsupported gateway: {$payment->gateway}");
        }
    }

    protected function processStripeRefund(Payment $payment, float $amount, string $reason): array
    {
        return $this->makeStripeRequest('POST', '/refunds', [
            'payment_intent' => $payment->gateway_payment_intent_id,
            'amount' => (int)($amount * 100),
            'reason' => $this->mapRefundReasonToStripe($reason),
            'metadata' => [
                'original_payment_id' => $payment->id,
                'reason' => $reason,
            ],
        ]);
    }

    protected function cancelGatewayPayment(Payment $payment): array
    {
        switch ($payment->gateway) {
            case 'stripe':
                return $this->cancelStripePayment($payment);
            default:
                throw new \Exception("Unsupported gateway: {$payment->gateway}");
        }
    }

    protected function cancelStripePayment(Payment $payment): array
    {
        return $this->makeStripeRequest('POST', "/payment_intents/{$payment->gateway_payment_intent_id}/cancel");
    }

    protected function makeStripeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = 'https://api.stripe.com/v1' . $endpoint;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->stripeConfig['secret_key'],
            'Stripe-Version' => $this->stripeConfig['api_version'],
        ])->asForm();

        $response = match(strtoupper($method)) {
            'GET' => $response->get($url, $data),
            'POST' => $response->post($url, $data),
            'PUT' => $response->put($url, $data),
            'DELETE' => $response->delete($url, $data),
            default => throw new \Exception("Unsupported HTTP method: {$method}"),
        };

        if (!$response->successful()) {
            $error = $response->json()['error'] ?? ['message' => 'Unknown Stripe error'];
            throw new \Exception("Stripe API error: {$error['message']}");
        }

        return $response->json();
    }

    protected function getOrCreateStripeCustomer(User $user): string
    {
        // Check if user already has a Stripe customer ID
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        // Create new Stripe customer
        $customer = $this->makeStripeRequest('POST', '/customers', [
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        // Save customer ID to user record
        $user->update(['stripe_customer_id' => $customer['id']]);

        return $customer['id'];
    }

    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'requires_payment_method', 'requires_confirmation', 'requires_action' => Payment::STATUS_PENDING,
            'processing' => Payment::STATUS_PROCESSING,
            'succeeded' => Payment::STATUS_COMPLETED,
            'canceled' => Payment::STATUS_CANCELLED,
            default => Payment::STATUS_FAILED,
        };
    }

    protected function mapRefundReasonToStripe(string $reason): string
    {
        return match(strtolower($reason)) {
            'duplicate', 'fraudulent', 'requested_by_customer' => strtolower($reason),
            default => 'requested_by_customer',
        };
    }

    protected function verifyStripeWebhookSignature(array $payload, string $signature): bool
    {
        // Stripe webhook signature verification would go here
        // This is a simplified version
        return !empty($this->stripeConfig['webhook_secret']);
    }

    protected function handlePaymentIntentSucceeded(array $paymentIntent): bool
    {
        $payment = Payment::where('gateway_payment_intent_id', $paymentIntent['id'])->first();
        
        if ($payment && $payment->status !== Payment::STATUS_COMPLETED) {
            $payment->capture();
        }

        return true;
    }

    protected function handlePaymentIntentFailed(array $paymentIntent): bool
    {
        $payment = Payment::where('gateway_payment_intent_id', $paymentIntent['id'])->first();
        
        if ($payment && $payment->status !== Payment::STATUS_FAILED) {
            $lastError = $paymentIntent['last_payment_error'] ?? [];
            $payment->fail(
                $lastError['code'] ?? 'unknown_error',
                $lastError['message'] ?? 'Payment failed',
                $paymentIntent
            );
        }

        return true;
    }

    protected function handleChargeDispute(array $dispute): bool
    {
        // Handle chargeback/dispute
        Log::warning('Charge dispute received', ['dispute' => $dispute]);
        
        // You would implement dispute handling logic here
        
        return true;
    }

    protected function handleInvoicePaymentSucceeded(array $invoice): bool
    {
        // Handle recurring payment success
        Log::info('Invoice payment succeeded', ['invoice' => $invoice]);
        
        // You would implement recurring payment logic here
        
        return true;
    }
}