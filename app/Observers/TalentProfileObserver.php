<?php

namespace App\Observers;

use App\Models\TalentProfile;
use App\Jobs\GenerateTalentProfileEmbeddings;
use Illuminate\Support\Facades\Log;

class TalentProfileObserver
{
    /**
     * Handle the TalentProfile "saved" event.
     * Fires after both create and update operations.
     */
    public function saved(TalentProfile $profile): void
    {
        // Check if embedding-relevant fields changed
        $embeddingFields = [
            'professional_title',
            'summary',
            'experience_level',
            'hourly_rate_min',
            'hourly_rate_max',
            'currency',
            'preferred_locations',
            'work_preferences',
            'availability_types',
            'languages',
        ];

        $shouldRegenerate = false;

        // For new records, always generate embeddings
        if ($profile->wasRecentlyCreated) {
            $shouldRegenerate = true;
            Log::info('New talent profile created, generating embeddings', [
                'profile_id' => $profile->id
            ]);
        }
        // For updates, check if relevant fields changed
        elseif ($profile->wasChanged($embeddingFields)) {
            $shouldRegenerate = true;
            $changedFields = array_keys($profile->getChanges());
            Log::info('Talent profile updated, regenerating embeddings', [
                'profile_id' => $profile->id,
                'changed_fields' => $changedFields
            ]);
        }

        // Generate embeddings in background queue
        if ($shouldRegenerate) {
            GenerateTalentProfileEmbeddings::dispatch($profile)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds(2)); // Small delay to ensure transaction committed
        }
    }

    /**
     * Handle the TalentProfile "deleted" event.
     */
    public function deleted(TalentProfile $profile): void
    {
        Log::info('Talent profile deleted', [
            'profile_id' => $profile->id,
            'had_embeddings' => !is_null($profile->embeddings_generated_at)
        ]);
    }

    /**
     * Handle the TalentProfile "restored" event.
     */
    public function restored(TalentProfile $profile): void
    {
        // Regenerate embeddings when profile is restored from soft delete
        Log::info('Talent profile restored, regenerating embeddings', [
            'profile_id' => $profile->id
        ]);

        GenerateTalentProfileEmbeddings::dispatch($profile)
            ->onQueue('embeddings');
    }
}