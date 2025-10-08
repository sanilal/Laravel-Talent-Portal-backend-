<?php

namespace App\Observers;

use App\Models\Skill;
use App\Jobs\GenerateSkillEmbedding;
use Illuminate\Support\Facades\Log;

class SkillObserver
{
    /**
     * Handle the Skill "saved" event.
     */
    public function saved(Skill $skill): void
    {
        // Check if embedding-relevant fields changed
        $embeddingFields = [
            'name',
            'category_id', // Category context affects embedding
        ];

        $shouldRegenerate = false;

        // For new skills, always generate embeddings
        if ($skill->wasRecentlyCreated) {
            $shouldRegenerate = true;
            Log::info('New skill created, generating embedding', [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name
            ]);
        }
        // For updates, check if relevant fields changed
        elseif ($skill->wasChanged($embeddingFields)) {
            $shouldRegenerate = true;
            $changedFields = array_keys($skill->getChanges());
            Log::info('Skill updated, regenerating embedding', [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name,
                'changed_fields' => $changedFields
            ]);
        }

        // Generate embedding in background queue
        if ($shouldRegenerate) {
            GenerateSkillEmbedding::dispatch($skill)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds(2));
        }
    }

    /**
     * Handle the Skill "deleted" event.
     */
    public function deleted(Skill $skill): void
    {
        Log::info('Skill deleted', [
            'skill_id' => $skill->id,
            'skill_name' => $skill->name,
            'had_embedding' => !is_null($skill->embeddings_generated_at)
        ]);
    }

    /**
     * Handle the Skill "restored" event.
     */
    public function restored(Skill $skill): void
    {
        Log::info('Skill restored, regenerating embedding', [
            'skill_id' => $skill->id,
            'skill_name' => $skill->name
        ]);

        GenerateSkillEmbedding::dispatch($skill)
            ->onQueue('embeddings');
    }
}