<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessFailedPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:process-failed {--limit=50 : Maximum number of payments to process}';

    /**
     * The console command description.
     */
    protected $description = 'Process failed payments that are eligible for retry';

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Processing failed payments (limit: {$limit})...");

        // Get payments eligible for retry
        $payments = Payment::where('status', Payment::STATUS_FAILED)
            ->where('retry_count', '<', 3)
            ->where(function ($query) {
                $query->whereNull('next_retry_at')
                      ->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No payments eligible for retry found.');
            return self::SUCCESS;
        }

        $processed = 0;
        $successful = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($payments->count());
        $progressBar->start();

        foreach ($payments as $payment) {
            try {
                $this->paymentService->retryPayment($payment);
                $successful++;
                
                Log::info('Payment retry successful', [
                    'payment_id' => $payment->id,
                    'retry_count' => $payment->retry_count,
                ]);
                
            } catch (\Exception $e) {
                $failed++;
                
                Log::error('Payment retry failed', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $processed++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Processing completed:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $processed],
                ['Successful Retries', $successful],
                ['Failed Retries', $failed],
            ]
        );

        // Send summary to logs
        Log::info('Failed payments processing summary', [
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed,
        ]);

        return self::SUCCESS;
    }
}