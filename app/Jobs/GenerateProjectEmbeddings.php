<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProjectEmbeddings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Project $project
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        try {
            Log::info('Starting embedding generation for project', [
                'project_id' => $this->project->id,
                'attempt' => $this->attempts()
            ]);

            $success = $embeddingService->generateProjectEmbeddings($this->project);

            if ($success) {
                Log::info('Successfully generated embeddings for project', [
                    'project_id' => $this->project->id
                ]);
            } else {
                Log::warning('Failed to generate embeddings for project', [
                    'project_id' => $this->project->id
                ]);
                
                $this->release(10);
            }
        } catch (\Exception $e) {
            Log::error('Exception while generating project embeddings', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Project embedding generation failed permanently', [
            'project_id' => $this->project->id,
            'error' => $exception->getMessage()
        ]);
    }
}