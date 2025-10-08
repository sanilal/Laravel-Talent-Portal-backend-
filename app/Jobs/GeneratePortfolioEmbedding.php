<?php

namespace App\Jobs;

use App\Models\TalentProfile;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTalentProfileEmbeddings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public TalentProfile $profile
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        try {
            Log::info('Starting embedding generation for talent profile', [
                'profile_id' => $this->profile->id,
                'attempt' => $this->attempts()
            ]);

            $success = $embeddingService->generateTalentProfileEmbeddings($this->profile);

            if ($success) {
                Log::info('Successfully generated embeddings for talent profile', [
                    'profile_id' => $this->profile->id
                ]);
            } else {
                Log::warning('Failed to generate embeddings for talent profile', [
                    'profile_id' => $this->profile->id
                ]);
                
                // Retry the job
                $this->release(10);
            }
        } catch (\Exception $e) {
            Log::error('Exception while generating talent profile embeddings', [
                'profile_id' => $this->profile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Let the job fail and retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Talent profile embedding generation failed permanently', [
            'profile_id' => $this->profile->id,
            'error' => $exception->getMessage()
        ]);

        // Optionally notify administrators or mark the profile for manual review
    }
}