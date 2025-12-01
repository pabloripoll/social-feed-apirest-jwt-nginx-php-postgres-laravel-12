<?php

namespace App\Domain\Member\Jobs;

use App\Domain\Member\Mail\UserRegisterMail;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Basic validation: ensure we have a recipient email
            if (empty($this->payload['email'])) {
                Log::error('UserRegisterJob: missing email in payload', ['payload' => $this->payload]);

                return; // or throw new Exception('Missing email') if you want retries
            }

            // Use the Mailable to build the message
            Mail::to($this->payload['email'])->send(new UserRegisterMail($this->payload));

        } catch (Exception $e) {
            // Handling for unexpected errors
            Log::error('Unexpected error while processing UserRegisterJob: '.$e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $this->payload]);
            throw $e; // Re-throw the exception to trigger the retry mechanism
        }
    }

    /**
     * This method is executed if the job fails after all retry attempts
     */
    public function failed(Exception $exception)
    {
        // Logic to be executed when the job completely fails
        Log::critical('Critical failure while processing order after multiple retries: '.$exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
        // Consider sending notifications to support teams or implementing other failure handling strategies
    }
}
