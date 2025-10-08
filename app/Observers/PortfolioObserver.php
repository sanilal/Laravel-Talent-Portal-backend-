<?php

namespace App\Observers;

use App\Models\Portfolio;
use App\Jobs\GeneratePortfolioEmbedding;
use Illuminate\Support\Facades\Log;

class PortfolioObserver
{
    /**
     * Handle the Portfolio "saved" event.
     */
    public function saved(Portfolio $portfolio): void
    {
        // Check if embedding-relevant fields changed
        $embeddingFields = [
            'title',
            'description',
            'project_type',
            'tags',
            'role',
        ];

        $shouldRegenerate = false;

        // For new portfolios, always generate embeddings
        if ($portfolio->wasRecentlyCreated) {
            $shouldRegenerate = true;
            Log::info('New portfolio created, generating embedding', [
                'portfolio_id' => $portfolio->id
            ]);
        }
        // For updates, check if relevant fields changed
        elseif ($portfolio->wasChanged($embeddingFields)) {
            $shouldRegenerate = true;
            $changedFields = array_keys($portfolio->getChanges());
            Log::info('Portfolio updated, regenerating embedding', [
                'portfolio_id' => $portfolio->id,
                'changed_fields' => $changedFields
            ]);
        }

        // Generate embedding in background queue
        if ($shouldRegenerate) {
            GeneratePortfolioEmbedding::dispatch($portfolio)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds(2));
        }
    }

    /**
     * Handle the Portfolio "deleted" event.
     */
    public function deleted(Portfolio $portfolio): void
    {
        Log::info('Portfolio deleted', [
            'portfolio_id' => $portfolio->id,
            'had_embedding' => !is_null($portfolio->embeddings_generated_at)
        ]);
    }

    /**
     * Handle the Portfolio "restored" event.
     */
    public function restored(Portfolio $portfolio): void
    {
        Log::info('Portfolio restored, regenerating embedding', [
            'portfolio_id' => $portfolio->id
        ]);

        GeneratePortfolioEmbedding::dispatch($portfolio)
            ->onQueue('embeddings');
    }
}