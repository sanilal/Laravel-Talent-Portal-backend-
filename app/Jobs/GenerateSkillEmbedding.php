<?php

namespace App\Jobs;

use App\Models\Skill;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSkillEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Skill $skill
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        try {
            Log::info('Starting embedding generation for skill', [
                'skill_id' => $this->skill->id,
                'skill_name' => $this->skill->name,
                'attempt' => $this->attempts()
            ]);

            $success = $embeddingService->generateSkillEmbedding($this->skill);

            if ($success) {
                Log::info('Successfully generated embedding for skill', [
                    'skill_id' => $this->skill->id,
                    'skill_name' => $this->skill->name
                ]);
            } else {
                Log::warning('Failed to generate embedding for skill', [
                    'skill_id' => $this->skill->id,
                    'skill_name' => $this->skill->name
                ]);
                
                $this->release(10);
            }
        } catch (\Exception $e) {
            Log::error('Exception while generating skill embedding', [
                'skill_id' => $this->skill->id,
                'skill_name' => $this->skill->name,
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
        Log::error('Skill embedding generation failed permanently', [
            'skill_id' => $this->skill->id,
            'skill_name' => $this->skill->name,
            'error' => $exception->getMessage()
        ]);
    }
}