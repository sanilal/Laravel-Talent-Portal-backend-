<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TalentProfile;
use App\Models\Project;
use App\Models\Portfolio;
use App\Models\Skill;

class EmbeddingService
{
    private string $embeddingApiUrl;
    private string $modelName;
    private int $dimensions;

    public function __construct()
    {
        // Using local Python service (FREE)
        $this->embeddingApiUrl = config('services.embeddings.url', 'http://localhost:5001');
        $this->modelName = config('services.embeddings.model', 'all-MiniLM-L6-v2');
        $this->dimensions = config('services.embeddings.dimensions', 384);
    }

    /**
     * Generate embedding for a single text
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            if (empty(trim($text))) {
                Log::warning('Attempted to generate embedding for empty text');
                return null;
            }

            $response = Http::timeout(30)
                ->post("{$this->embeddingApiUrl}/embed", [
                    'text' => $text
                ]);

            if ($response->successful()) {
                return $response->json()['embedding'];
            }

            Log::error('Embedding API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding', [
                'error' => $e->getMessage(),
                'text_length' => strlen($text)
            ]);
            return null;
        }
    }

    /**
     * Generate embeddings for multiple texts (more efficient)
     */
    public function generateBatchEmbeddings(array $texts): ?array
    {
        try {
            if (empty($texts)) {
                return null;
            }

            $response = Http::timeout(60)
                ->post("{$this->embeddingApiUrl}/embed/batch", [
                    'texts' => $texts
                ]);

            if ($response->successful()) {
                return $response->json()['embeddings'];
            }

            Log::error('Batch embedding API error', [
                'status' => $response->status(),
                'count' => count($texts)
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to generate batch embeddings', [
                'error' => $e->getMessage(),
                'count' => count($texts)
            ]);
            return null;
        }
    }

    /**
     * Generate all embeddings for a talent profile
     */
    public function generateTalentProfileEmbeddings(TalentProfile $profile): bool
    {
        try {
            // Build comprehensive profile text
            $profileText = $this->buildTalentProfileText($profile);
            $skillsText = $this->buildTalentSkillsText($profile);
            $experienceText = $this->buildTalentExperienceText($profile);

            // Generate embeddings in batch (more efficient)
            $embeddings = $this->generateBatchEmbeddings([
                $profileText,
                $skillsText,
                $experienceText
            ]);

            if (!$embeddings || count($embeddings) !== 3) {
                return false;
            }

            // Update profile with embeddings
            $profile->update([
                'profile_embedding' => $embeddings[0],
                'skills_embedding' => $embeddings[1],
                'experience_embedding' => $embeddings[2],
                'embeddings_generated_at' => now(),
                'embedding_model' => $this->modelName
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate talent profile embeddings', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate embeddings for a project
     */
    public function generateProjectEmbeddings(Project $project): bool
    {
        try {
            $requirementsText = $this->buildProjectRequirementsText($project);
            $skillsText = $this->buildProjectSkillsText($project);

            $embeddings = $this->generateBatchEmbeddings([
                $requirementsText,
                $skillsText
            ]);

            if (!$embeddings || count($embeddings) !== 2) {
                return false;
            }

            $project->update([
                 'requirements_embedding' => $embeddings[0],
                'required_skills_embedding' => $embeddings[1],
                'embeddings_generated_at' => now(),
                'embedding_model' => $this->modelName
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate project embeddings', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate embedding for a portfolio
     */
    public function generatePortfolioEmbedding(Portfolio $portfolio): bool
    {
        try {
            $descriptionText = $this->buildPortfolioText($portfolio);
            $embedding = $this->generateEmbedding($descriptionText);

            if (!$embedding) {
                return false;
            }

            $portfolio->update([
                'description_embedding' => $embedding,
                'embeddings_generated_at' => now(),
                'embedding_model' => $this->modelName
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate portfolio embedding', [
                'portfolio_id' => $portfolio->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate embedding for a skill
     */
    public function generateSkillEmbedding(Skill $skill): bool
    {
        try {
            // Combine skill name with category context for better embeddings
            $skillText = $skill->name;
            if ($skill->category) {
                $skillText .= " ({$skill->category->name} category)";
            }

            $embedding = $this->generateEmbedding($skillText);

            if (!$embedding) {
                return false;
            }

            $skill->update([
                'skill_embedding' => $embedding,
                'embeddings_generated_at' => now(),
                'embedding_model' => $this->modelName
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate skill embedding', [
                'skill_id' => $skill->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Build comprehensive talent profile text for embedding
     * Matches actual TalentProfile schema
     */
    private function buildTalentProfileText(TalentProfile $profile): string
    {
        $parts = array_filter([
            $profile->professional_title,
            $profile->summary,
            $profile->experience_level ? "Experience Level: {$profile->experience_level}" : null,
            $profile->preferred_locations ? "Preferred Locations: " . implode(', ', $profile->preferred_locations) : null,
            $profile->work_preferences ? "Work Preferences: " . implode(', ', $profile->work_preferences) : null,
            $profile->availability_types ? "Available for: " . implode(', ', $profile->availability_types) : null,
            $profile->languages ? "Languages: " . implode(', ', array_column($profile->languages, 'name')) : null,
            $profile->hourly_rate_min && $profile->hourly_rate_max 
                ? "Rate: {$profile->currency} {$profile->hourly_rate_min}-{$profile->hourly_rate_max}/hour" 
                : null,
        ]);

        return implode('. ', $parts) ?: 'No profile information available';
    }

    /**
     * Build skills text for embedding
     */
    private function buildTalentSkillsText(TalentProfile $profile): string
    {
        // Skills are related through TalentSkill model
        $skills = $profile->skills()
            ->with('skill')
            ->get()
            ->map(function($talentSkill) {
                $skillName = $talentSkill->skill->name ?? 'Unknown';
                $level = $talentSkill->proficiency_level ?? 'unspecified';
                return "{$skillName} ({$level} level)";
            })
            ->join(', ');

        return $skills ? "Skills: {$skills}" : 'No skills specified';
    }

    /**
     * Build experience text for embedding
     */
    private function buildTalentExperienceText(TalentProfile $profile): string
    {
        // Experiences are related through User model
        $experiences = $profile->user->experiences()
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($exp) {
                $parts = [
                    $exp->title,
                    "at {$exp->company_name}",
                ];
                if ($exp->description) {
                    $parts[] = $exp->description;
                }
                return implode(' ', $parts);
            })
            ->join('. ');

        return $experiences ? "Work Experience: {$experiences}" : 'No work experience specified';
    }

    /**
     * Build project requirements text
     * Matches actual Project schema
     */
    private function buildProjectRequirementsText(Project $project): string
    {
        $parts = array_filter([
            $project->title,
            $project->description,
            $project->requirements && is_array($project->requirements) 
                ? "Requirements: " . implode('. ', $project->requirements) 
                : null,
            $project->responsibilities && is_array($project->responsibilities)
                ? "Responsibilities: " . implode('. ', $project->responsibilities)
                : null,
            $project->deliverables && is_array($project->deliverables)
                ? "Deliverables: " . implode('. ', $project->deliverables)
                : null,
            "Project Type: {$project->project_type}",
            "Work Type: {$project->work_type}",
            "Experience Level: {$project->experience_level}",
            $project->location && is_array($project->location)
                ? "Location: " . implode(', ', $project->location)
                : null,
            $project->budget_min && $project->budget_max
                ? "Budget: {$project->budget_currency} {$project->budget_min}-{$project->budget_max} ({$project->budget_type})"
                : null,
            $project->duration ? "Duration: {$project->duration}" : null,
        ]);

        return implode('. ', $parts) ?: 'No project information available';
    }

    /**
     * Build project skills text
     */
    private function buildProjectSkillsText(Project $project): string
    {
        // Only use the skills_required JSON field
        if ($project->skills_required && is_array($project->skills_required)) {
            $skillsList = collect($project->skills_required)
                ->map(function($skill) {
                    if (is_array($skill)) {
                        $name = $skill['name'] ?? 'Unknown';
                        $level = $skill['level'] ?? 'any';
                        return "{$name} ({$level} level)";
                    }
                    return is_string($skill) ? $skill : 'Unknown skill';
                })
                ->join(', ');
            
            return $skillsList ? "Required Skills: {$skillsList}" : 'No specific skills required';
        }

        return 'No specific skills required';
    }

    /**
     * Build portfolio text
     */
    private function buildPortfolioText(Portfolio $portfolio): string
    {
        $parts = array_filter([
            $portfolio->title,
            $portfolio->description,
            $portfolio->project_type ? "Type: {$portfolio->project_type}" : null,
            $portfolio->tags ? "Tags: {$portfolio->tags}" : null,
            $portfolio->role ? "Role: {$portfolio->role}" : null,
        ]);

        return implode('. ', $parts) ?: 'No portfolio information available';
    }

    /**
     * Check if embedding service is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->embeddingApiUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}