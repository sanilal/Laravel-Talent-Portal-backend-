<?php

namespace App\Observers;

use App\Models\Project;
use App\Jobs\GenerateProjectEmbeddings;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    /**
     * Handle the Project "saved" event.
     */
    public function saved(Project $project): void
    {
        // Check if embedding-relevant fields changed
        $embeddingFields = [
            'title',
            'description',
            'requirements',
            'responsibilities',
            'deliverables',
            'project_type',
            'work_type',
            'experience_level',
            'location',
            'skills_required',
            'budget_min',
            'budget_max',
            'budget_currency',
            'budget_type',
            'duration',
        ];

        $shouldRegenerate = false;

        // For new projects, always generate embeddings
        if ($project->wasRecentlyCreated) {
            $shouldRegenerate = true;
            Log::info('New project created, generating embeddings', [
                'project_id' => $project->id
            ]);
        }
        // For updates, check if relevant fields changed
        elseif ($project->wasChanged($embeddingFields)) {
            $shouldRegenerate = true;
            $changedFields = array_keys($project->getChanges());
            Log::info('Project updated, regenerating embeddings', [
                'project_id' => $project->id,
                'changed_fields' => $changedFields
            ]);
        }

        // Generate embeddings in background queue
        if ($shouldRegenerate) {
            GenerateProjectEmbeddings::dispatch($project)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds(2));
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        Log::info('Project deleted', [
            'project_id' => $project->id,
            'had_embeddings' => !is_null($project->embeddings_generated_at)
        ]);
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        Log::info('Project restored, regenerating embeddings', [
            'project_id' => $project->id
        ]);

        GenerateProjectEmbeddings::dispatch($project)
            ->onQueue('embeddings');
    }
}