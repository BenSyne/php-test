<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Stripe webhooks
     */
    public function stripe(Request $request): Response
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        try {
            $success = $this->paymentService->processStripeWebhook($payload, $signature);
            
            return response('Webhook processed', $success ? 200 : 400);
            
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            
            return response('Webhook processing failed', 400);
        }
    }

    /**
     * Handle payment status updates from various gateways
     */
    public function paymentStatus(Request $request): Response
    {
        $gateway = $request->get('gateway', 'stripe');
        
        try {
            switch ($gateway) {
                case 'stripe':
                    return $this->stripe($request);
                    
                case 'square':
                    // Square webhook handling would go here
                    return response('Square webhooks not implemented', 501);
                    
                default:
                    return response('Unknown gateway', 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Payment status webhook failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);
            
            return response('Webhook processing failed', 500);
        }
    }
}