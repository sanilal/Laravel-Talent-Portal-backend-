<?php

namespace App\Services;

use App\Models\TalentProfile;
use App\Models\Project;
use App\Models\Portfolio;
use App\Models\Skill;
use App\Models\Application;
use App\Helpers\SimilarityHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SearchService
{
    public function __construct(
        private EmbeddingService $embeddingService
    ) {}

    /**
     * Search talents using natural language query
     * 
     * @param string $query Search query text
     * @param array $filters Additional filters
     * @param int $limit Maximum results
     * @param float $minSimilarity Minimum similarity threshold
     * @return array Search results with metadata
     */
    public function searchTalents(
        string $query, 
        array $filters = [], 
        int $limit = 20,
        float $minSimilarity = 0.5
    ): array {
        $startTime = microtime(true);
        
        // Generate query embedding
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);
        
        if (empty($queryEmbedding)) {
            return [
                'results' => [],
                'total' => 0,
                'query' => $query,
                'execution_time_ms' => 0,
                'error' => 'Failed to generate query embedding'
            ];
        }
        
        // Load all talent profiles with embeddings
        $talents = TalentProfile::whereNotNull('profile_embedding')
            ->whereNotNull('skills_embedding')
            ->whereNotNull('experience_embedding')
            ->with(['user', 'skills.skill', 'primaryCategory'])
            ->get();
        
        // Calculate similarities and score each talent
        $results = $talents->map(function ($talent) use ($queryEmbedding, $minSimilarity) {
            try {
                // Calculate individual similarity scores
                $profileSim = SimilarityHelper::cosineSimilarity(
                    $queryEmbedding, 
                    $talent->profile_embedding
                );
                $skillsSim = SimilarityHelper::cosineSimilarity(
                    $queryEmbedding, 
                    $talent->skills_embedding
                );
                $experienceSim = SimilarityHelper::cosineSimilarity(
                    $queryEmbedding, 
                    $talent->experience_embedding
                );
                
                // Weighted average: 40% profile, 40% skills, 20% experience
                $overallScore = SimilarityHelper::weightedSimilarity(
                    [$profileSim, $skillsSim, $experienceSim],
                    [0.4, 0.4, 0.2]
                );
                
                // Skip if below threshold
                if ($overallScore < $minSimilarity) {
                    return null;
                }
                
                // Add scoring data
                $talent->similarity_score = round($overallScore, 4);
                $talent->breakdown = [
                    'profile' => round($profileSim, 4),
                    'skills' => round($skillsSim, 4),
                    'experience' => round($experienceSim, 4),
                ];
                $talent->match_quality = SimilarityHelper::matchQuality($overallScore);
                
                // Generate match reasons
                $talent->match_reasons = $this->generateTalentMatchReasons($talent->breakdown);
                
                return $talent;
            } catch (\Exception $e) {
                Log::error('Error calculating talent similarity', [
                    'talent_id' => $talent->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Apply additional filters
        $results = $this->applyTalentFilters($results, $filters);
        
        // Sort by similarity and limit
        $sorted = $results->sortByDesc('similarity_score')->values();
        $limited = $sorted->take($limit);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'results' => $limited->map(fn($t) => $this->formatTalentResult($t)),
            'total' => $sorted->count(),
            'query' => $query,
            'execution_time_ms' => round($executionTime, 2),
            'filters_applied' => !empty($filters),
        ];
    }

    /**
     * Find best matching talents for a project
     * 
     * @param Project $project The project to match
     * @param array $filters Additional filters
     * @param int $limit Maximum results
     * @param float $minSimilarity Minimum similarity threshold
     * @return array Match results
     */
    public function matchTalentsToProject(
        Project $project,
        array $filters = [],
        int $limit = 20,
        float $minSimilarity = 0.6
    ): array {
        $startTime = microtime(true);
        
        // Validate project has embeddings
        if (empty($project->requirements_embedding) || empty($project->required_skills_embedding)) {
            return [
                'project' => $this->formatProjectInfo($project),
                'matches' => [],
                'total_analyzed' => 0,
                'total_matched' => 0,
                'error' => 'Project embeddings not generated yet'
            ];
        }
        
        // Load all available talents
        $talents = TalentProfile::whereNotNull('profile_embedding')
            ->whereNotNull('skills_embedding')
            ->whereNotNull('experience_embedding')
            ->with(['user', 'skills.skill'])
            ->get();
        
        $totalAnalyzed = $talents->count();
        
        // Score each talent against project
        $matches = $talents->map(function ($talent) use ($project, $minSimilarity) {
            try {
                // Calculate component similarities
                $requirementsMatch = SimilarityHelper::cosineSimilarity(
                    $project->requirements_embedding,
                    $talent->profile_embedding
                );
                
                $skillsMatch = SimilarityHelper::cosineSimilarity(
                    $project->required_skills_embedding,
                    $talent->skills_embedding
                );
                
                $experienceMatch = SimilarityHelper::cosineSimilarity(
                    $project->requirements_embedding,
                    $talent->experience_embedding
                );
                
                // Weighted scoring: 50% requirements, 30% skills, 20% experience
                $overallScore = SimilarityHelper::weightedSimilarity(
                    [$requirementsMatch, $skillsMatch, $experienceMatch],
                    [0.5, 0.3, 0.2]
                );
                
                // Skip if below threshold
                if ($overallScore < $minSimilarity) {
                    return null;
                }
                
                $talent->match_score = round($overallScore, 4);
                $talent->breakdown = [
                    'requirements_match' => round($requirementsMatch, 4),
                    'skills_match' => round($skillsMatch, 4),
                    'experience_match' => round($experienceMatch, 4),
                ];
                
                // Generate strengths and gaps
                $talent->strengths = $this->identifyStrengths($talent, $project);
                $talent->gaps = $this->identifyGaps($talent, $project);
                
                return $talent;
            } catch (\Exception $e) {
                Log::error('Error matching talent to project', [
                    'talent_id' => $talent->id,
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Apply filters
        $matches = $this->applyTalentFilters($matches, $filters);
        
        // Sort and limit
        $sorted = $matches->sortByDesc('match_score')->values();
        $limited = $sorted->take($limit);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'project' => $this->formatProjectInfo($project),
            'matches' => $limited->map(fn($t) => $this->formatProjectMatch($t)),
            'total_analyzed' => $totalAnalyzed,
            'total_matched' => $sorted->count(),
            'execution_time_ms' => round($executionTime, 2),
        ];
    }

    /**
     * Find suitable projects for a talent
     * 
     * @param TalentProfile $talent The talent profile
     * @param array $filters Additional filters
     * @param int $limit Maximum results
     * @param float $minSimilarity Minimum similarity threshold
     * @return array Recommended projects
     */
    public function matchProjectsToTalent(
        TalentProfile $talent,
        array $filters = [],
        int $limit = 20,
        float $minSimilarity = 0.6
    ): array {
        $startTime = microtime(true);
        
        // Validate talent has embeddings
        if (empty($talent->profile_embedding) || empty($talent->skills_embedding)) {
            return [
                'talent' => $this->formatTalentInfo($talent),
                'recommended_projects' => [],
                'total' => 0,
                'error' => 'Talent embeddings not generated yet'
            ];
        }
        
        // Load active projects with embeddings
        $projects = Project::where('status', 'open')
            ->whereNotNull('requirements_embedding')
            ->whereNotNull('required_skills_embedding')
            ->with(['recruiter.user'])
            ->get();
        
        // Score each project
        $matches = $projects->map(function ($project) use ($talent, $minSimilarity) {
            try {
                $profileMatch = SimilarityHelper::cosineSimilarity(
                    $talent->profile_embedding,
                    $project->requirements_embedding
                );
                
                $skillsMatch = SimilarityHelper::cosineSimilarity(
                    $talent->skills_embedding,
                    $project->required_skills_embedding
                );
                
                $experienceMatch = SimilarityHelper::cosineSimilarity(
                    $talent->experience_embedding,
                    $project->requirements_embedding
                );
                
                // Weighted: 40% profile, 40% skills, 20% experience
                $matchScore = SimilarityHelper::weightedSimilarity(
                    [$profileMatch, $skillsMatch, $experienceMatch],
                    [0.4, 0.4, 0.2]
                );
                
                if ($matchScore < $minSimilarity) {
                    return null;
                }
                
                $project->match_score = round($matchScore, 4);
                $project->compatibility = [
                    'profile_match' => round($profileMatch, 4),
                    'skills_match' => round($skillsMatch, 4),
                    'experience_match' => round($experienceMatch, 4),
                ];
                
                $project->why_good_fit = $this->generateFitReasons($project, $talent);
                
                return $project;
            } catch (\Exception $e) {
                Log::error('Error matching project to talent', [
                    'project_id' => $project->id,
                    'talent_id' => $talent->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Apply project filters
        $matches = $this->applyProjectFilters($matches, $filters);
        
        // Sort and limit
        $sorted = $matches->sortByDesc('match_score')->values();
        $limited = $sorted->take($limit);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'talent' => $this->formatTalentInfo($talent),
            'recommended_projects' => $limited->map(fn($p) => $this->formatProjectRecommendation($p)),
            'total' => $sorted->count(),
            'execution_time_ms' => round($executionTime, 2),
        ];
    }

    /**
     * Find portfolios similar to a given portfolio
     * 
     * @param Portfolio $sourcePortfolio The reference portfolio
     * @param int $limit Maximum results
     * @param float $minSimilarity Minimum similarity threshold
     * @return array Similar portfolios
     */
    public function findSimilarPortfolios(
        Portfolio $sourcePortfolio,
        int $limit = 10,
        float $minSimilarity = 0.6
    ): array {
        $startTime = microtime(true);
        
        if (empty($sourcePortfolio->description_embedding)) {
            return [
                'source_portfolio' => $this->formatPortfolioInfo($sourcePortfolio),
                'similar_portfolios' => [],
                'total' => 0,
                'error' => 'Source portfolio embedding not generated yet'
            ];
        }
        
        // Load other portfolios (exclude source)
        $portfolios = Portfolio::where('id', '!=', $sourcePortfolio->id)
            ->whereNotNull('description_embedding')
            ->with(['user'])
            ->get();
        
        // Calculate similarities
        $similar = $portfolios->map(function ($portfolio) use ($sourcePortfolio, $minSimilarity) {
            try {
                $similarity = SimilarityHelper::cosineSimilarity(
                    $sourcePortfolio->description_embedding,
                    $portfolio->description_embedding
                );
                
                if ($similarity < $minSimilarity) {
                    return null;
                }
                
                $portfolio->similarity_score = round($similarity, 4);
                $portfolio->common_elements = $this->findCommonElements($sourcePortfolio, $portfolio);
                
                return $portfolio;
            } catch (\Exception $e) {
                Log::error('Error finding similar portfolios', [
                    'portfolio_id' => $portfolio->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Sort and limit
        $sorted = $similar->sortByDesc('similarity_score')->values();
        $limited = $sorted->take($limit);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'source_portfolio' => $this->formatPortfolioInfo($sourcePortfolio),
            'similar_portfolios' => $limited->map(fn($p) => $this->formatSimilarPortfolio($p)),
            'total' => $sorted->count(),
            'execution_time_ms' => round($executionTime, 2),
        ];
    }

    /**
     * Find skills related to a given skill
     * 
     * @param Skill $sourceSkill The reference skill
     * @param int $limit Maximum results
     * @param float $minSimilarity Minimum similarity threshold
     * @return array Related skills with clusters
     */
    public function findRelatedSkills(
        Skill $sourceSkill,
        int $limit = 15,
        float $minSimilarity = 0.7
    ): array {
        $startTime = microtime(true);
        
        if (empty($sourceSkill->skill_embedding)) {
            return [
                'skill' => $this->formatSkillInfo($sourceSkill),
                'related_skills' => [],
                'clusters' => [],
                'total' => 0,
                'error' => 'Source skill embedding not generated yet'
            ];
        }
        
        // Load other skills (exclude source)
        $skills = Skill::where('id', '!=', $sourceSkill->id)
            ->whereNotNull('skill_embedding')
            ->with(['category'])
            ->get();
        
        // Calculate similarities
        $related = $skills->map(function ($skill) use ($sourceSkill, $minSimilarity) {
            try {
                $similarity = SimilarityHelper::cosineSimilarity(
                    $sourceSkill->skill_embedding,
                    $skill->skill_embedding
                );
                
                if ($similarity < $minSimilarity) {
                    return null;
                }
                
                $skill->similarity_score = round($similarity, 4);
                $skill->relationship = $this->determineSkillRelationship($sourceSkill, $skill, $similarity);
                
                return $skill;
            } catch (\Exception $e) {
                Log::error('Error finding related skills', [
                    'skill_id' => $skill->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Sort and limit
        $sorted = $related->sortByDesc('similarity_score')->values();
        $limited = $sorted->take($limit);
        
        // Create clusters
        $clusters = $this->clusterSkills($limited);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'skill' => $this->formatSkillInfo($sourceSkill),
            'related_skills' => $limited->map(fn($s) => $this->formatRelatedSkill($s)),
            'clusters' => $clusters,
            'total' => $sorted->count(),
            'execution_time_ms' => round($executionTime, 2),
        ];
    }

    /**
     * Generate personalized project recommendations for a talent
     * 
     * @param TalentProfile $talent The talent profile
     * @param int $limit Maximum results
     * @return array Personalized recommendations
     */
    public function generateRecommendations(
        TalentProfile $talent,
        int $limit = 10
    ): array {
        $startTime = microtime(true);
        
        // Get talent's application history
        $appliedProjectIds = Application::where('talent_id', $talent->user_id)
            ->pluck('project_id')
            ->toArray();
        
        // Load open projects (exclude already applied)
        $projects = Project::where('status', 'open')
            ->whereNotIn('id', $appliedProjectIds)
            ->whereNotNull('requirements_embedding')
            ->whereNotNull('required_skills_embedding')
            ->with(['recruiter.user'])
            ->get();
        
        // Score projects
        $recommendations = $projects->map(function ($project) use ($talent) {
            try {
                // Multi-factor scoring
                $profileScore = SimilarityHelper::cosineSimilarity(
                    $talent->profile_embedding,
                    $project->requirements_embedding
                );
                
                $skillsScore = SimilarityHelper::cosineSimilarity(
                    $talent->skills_embedding,
                    $project->required_skills_embedding
                );
                
                $experienceScore = SimilarityHelper::cosineSimilarity(
                    $talent->experience_embedding,
                    $project->requirements_embedding
                );
                
                // Check preferences alignment
                $preferencesScore = $this->calculatePreferencesScore($talent, $project);
                
                // Weighted recommendation score
                $recommendationScore = SimilarityHelper::weightedSimilarity(
                    [$profileScore, $skillsScore, $experienceScore, $preferencesScore],
                    [0.3, 0.3, 0.2, 0.2]
                );
                
                $project->recommendation_score = round($recommendationScore, 4);
                $project->reasons = $this->generateRecommendationReasons($project, $talent, [
                    'profile' => $profileScore,
                    'skills' => $skillsScore,
                    'experience' => $experienceScore,
                    'preferences' => $preferencesScore,
                ]);
                
                $project->confidence = $recommendationScore >= 0.8 ? 'high' : 
                                      ($recommendationScore >= 0.7 ? 'medium' : 'moderate');
                
                return $project;
            } catch (\Exception $e) {
                Log::error('Error generating recommendation', [
                    'project_id' => $project->id,
                    'talent_id' => $talent->id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        })->filter();
        
        // Sort by score and diversify
        $sorted = $recommendations->sortByDesc('recommendation_score')->values();
        $limited = $sorted->take($limit);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'recommendations' => $limited->map(fn($p) => $this->formatRecommendation($p)),
            'personalization_factors' => [
                'based_on_profile' => 0.3,
                'based_on_skills' => 0.3,
                'based_on_experience' => 0.2,
                'based_on_preferences' => 0.2,
            ],
            'total' => $sorted->count(),
            'execution_time_ms' => round($executionTime, 2),
        ];
    }

    // ==================== PRIVATE HELPER METHODS ====================

    private function applyTalentFilters(Collection $talents, array $filters): Collection
    {
        if (empty($filters)) {
            return $talents;
        }

        return $talents->filter(function ($talent) use ($filters) {
            // Experience level filter
            if (isset($filters['experience_level']) && 
                $talent->experience_level !== $filters['experience_level']) {
                return false;
            }

            // Hourly rate filter
            if (isset($filters['hourly_rate_max']) && 
                $talent->hourly_rate_min > $filters['hourly_rate_max']) {
                return false;
            }

            // Availability filter
            if (isset($filters['availability']) && $filters['availability']) {
                if (!$talent->is_available) {
                    return false;
                }
            }

            // Category filter
            if (isset($filters['category_id']) && 
                $talent->primary_category_id !== $filters['category_id']) {
                return false;
            }

            return true;
        });
    }

    private function applyProjectFilters(Collection $projects, array $filters): Collection
    {
        if (empty($filters)) {
            return $projects;
        }

        return $projects->filter(function ($project) use ($filters) {
            if (isset($filters['project_type']) && 
                $project->project_type !== $filters['project_type']) {
                return false;
            }

            if (isset($filters['work_type']) && 
                $project->work_type !== $filters['work_type']) {
                return false;
            }

            if (isset($filters['budget_max']) && 
                $project->budget_min > $filters['budget_max']) {
                return false;
            }

            return true;
        });
    }

    private function generateTalentMatchReasons(array $breakdown): array
    {
        $reasons = [];
        
        if ($breakdown['skills'] >= 0.85) {
            $reasons[] = sprintf('Strong skills match (%.2f)', $breakdown['skills']);
        }
        
        if ($breakdown['experience'] >= 0.80) {
            $reasons[] = sprintf('Relevant experience (%.2f)', $breakdown['experience']);
        }
        
        if ($breakdown['profile'] >= 0.85) {
            $reasons[] = sprintf('Profile alignment (%.2f)', $breakdown['profile']);
        }

        return $reasons ?: ['General match'];
    }

    private function identifyStrengths(TalentProfile $talent, Project $project): array
    {
        $strengths = [];
        
        // Check skills overlap
        $talentSkills = $talent->skills->pluck('skill.name')->toArray();
        $projectSkills = $project->skills_required ?? [];
        $overlap = array_intersect($talentSkills, array_column($projectSkills, 'name'));
        
        if (count($overlap) > 0) {
            $strengths[] = 'Exact skill match: ' . implode(', ', array_slice($overlap, 0, 3));
        }
        
        // Check experience level
        if ($talent->experience_level === $project->experience_level) {
            $strengths[] = 'Experience level matches requirement';
        }
        
        // Check rate compatibility
        if ($project->budget_type === 'hourly' && 
            $talent->hourly_rate_min <= ($project->budget_max ?? PHP_INT_MAX)) {
            $strengths[] = 'Rate within budget';
        }
        
        return $strengths ?: ['Compatible profile'];
    }

    private function identifyGaps(TalentProfile $talent, Project $project): array
    {
        $gaps = [];
        
        // Check for missing required skills
        $talentSkills = $talent->skills->pluck('skill.name')->toArray();
        $projectSkills = $project->skills_required ?? [];
        $required = array_column($projectSkills, 'name');
        $missing = array_diff($required, $talentSkills);
        
        if (count($missing) > 0) {
            $gaps[] = 'Missing: ' . implode(', ', array_slice($missing, 0, 2));
        }
        
        return $gaps;
    }

    private function generateFitReasons(Project $project, TalentProfile $talent): array
    {
        $reasons = [];
        
        $compatibility = $project->compatibility;
        
        if ($compatibility['skills_match'] >= 0.85) {
            $reasons[] = 'Your skills align perfectly with requirements';
        }
        
        if ($compatibility['experience_match'] >= 0.80) {
            $reasons[] = 'Your experience matches project needs';
        }
        
        if ($project->work_type === 'remote' && 
            in_array('remote', $talent->work_preferences ?? [])) {
            $reasons[] = 'Remote work matches your preferences';
        }
        
        if ($project->budget_type === 'hourly') {
            $midBudget = ($project->budget_min + $project->budget_max) / 2;
            $midRate = ($talent->hourly_rate_min + $talent->hourly_rate_max) / 2;
            if (abs($midBudget - $midRate) <= 20) {
                $reasons[] = 'Budget aligns with your rate expectations';
            }
        }
        
        return $reasons ?: ['Good overall match'];
    }

    private function findCommonElements(Portfolio $source, Portfolio $target): array
    {
        $elements = [];
        
        // Compare project types
        if ($source->project_type === $target->project_type) {
            $elements[] = ucfirst($source->project_type) . ' project';
        }
        
        // Compare tags
        $sourceTags = array_map('trim', explode(',', $source->tags ?? ''));
        $targetTags = array_map('trim', explode(',', $target->tags ?? ''));
        $commonTags = array_intersect($sourceTags, $targetTags);
        
        if (count($commonTags) > 0) {
            $elements[] = 'Common tags: ' . implode(', ', array_slice($commonTags, 0, 3));
        }
        
        // Compare roles
        if ($source->role === $target->role) {
            $elements[] = 'Similar role: ' . $source->role;
        }
        
        return $elements ?: ['Similar style'];
    }

    private function determineSkillRelationship(Skill $source, Skill $target, float $similarity): string
    {
        // Same category = related
        if ($source->category_id === $target->category_id) {
            if ($similarity >= 0.9) {
                return 'alternative';
            }
            return 'related_in_category';
        }
        
        // High similarity, different category = complementary
        if ($similarity >= 0.85) {
            return 'commonly_used_together';
        }
        
        return 'related';
    }

    private function clusterSkills(Collection $skills): array
    {
        $clusters = [];
        
        // Group by category
        $byCategory = $skills->groupBy('category.name');
        
        foreach ($byCategory as $categoryName => $categorySkills) {
            if ($categorySkills->count() >= 2) {
                $clusters[] = [
                    'name' => $categoryName,
                    'skills' => $categorySkills->pluck('name')->toArray(),
                    'avg_similarity' => round($categorySkills->avg('similarity_score'), 4),
                ];
            }
        }
        
        return $clusters;
    }

    private function calculatePreferencesScore(TalentProfile $talent, Project $project): float
    {
        $score = 0.5; // Base score
        $matches = 0;
        $checks = 0;
        
        // Work type preference
        $checks++;
        if (in_array($project->work_type, $talent->work_preferences ?? [])) {
            $matches++;
        }
        
        // Location preference
        if (!empty($talent->preferred_locations) && !empty($project->location)) {
            $checks++;
            $projectLocations = is_array($project->location) ? $project->location : [$project->location];
            if (array_intersect($talent->preferred_locations, $projectLocations)) {
                $matches++;
            }
        }
        
        return $checks > 0 ? ($matches / $checks) : 0.5;
    }

    private function generateRecommendationReasons(Project $project, TalentProfile $talent, array $scores): array
    {
        $reasons = [];
        
        if ($scores['skills'] >= 0.85) {
            $reasons[] = 'Perfect skill match based on your profile';
        }
        
        if ($scores['experience'] >= 0.80) {
            $reasons[] = 'Your experience aligns with project needs';
        }
        
        if ($scores['preferences'] >= 0.7) {
            $reasons[] = 'Matches your work preferences';
        }
        
        if ($project->budget_type === 'hourly' && 
            $talent->hourly_rate_min <= ($project->budget_max ?? PHP_INT_MAX)) {
            $reasons[] = 'Budget aligns with your rates';
        }
        
        return $reasons ?: ['Good match based on your profile'];
    }

    // Formatting methods
    private function formatTalentResult($talent): array
    {
        return [
            'id' => $talent->id,
            'user_id' => $talent->user_id,
            'professional_title' => $talent->professional_title,
            'summary' => $talent->summary,
            'similarity_score' => $talent->similarity_score,
            'match_quality' => $talent->match_quality,
            'hourly_rate_min' => $talent->hourly_rate_min,
            'hourly_rate_max' => $talent->hourly_rate_max,
            'currency' => $talent->currency,
            'experience_level' => $talent->experience_level,
            'is_available' => $talent->is_available,
            'match_reasons' => $talent->match_reasons,
            'breakdown' => $talent->breakdown,
        ];
    }

    private function formatProjectMatch($talent): array
    {
        return [
            'talent_id' => $talent->id,
            'professional_title' => $talent->professional_title,
            'overall_score' => $talent->match_score,
            'breakdown' => $talent->breakdown,
            'strengths' => $talent->strengths,
            'gaps' => $talent->gaps,
            'hourly_rate_range' => sprintf('$%d-$%d %s', 
                $talent->hourly_rate_min, 
                $talent->hourly_rate_max, 
                $talent->currency
            ),
        ];
    }

    private function formatProjectRecommendation($project): array
    {
        return [
            'project_id' => $project->id,
            'title' => $project->title,
            'match_score' => $project->match_score,
            'compatibility' => $project->compatibility,
            'why_good_fit' => $project->why_good_fit,
            'budget' => $this->formatBudget($project),
            'work_type' => $project->work_type,
            'duration' => $project->duration,
        ];
    }

    private function formatSimilarPortfolio($portfolio): array
    {
        return [
            'id' => $portfolio->id,
            'title' => $portfolio->title,
            'similarity_score' => $portfolio->similarity_score,
            'user' => [
                'id' => $portfolio->user_id,
                'name' => $portfolio->user->name ?? 'Unknown',
            ],
            'common_elements' => $portfolio->common_elements,
            'project_type' => $portfolio->project_type,
        ];
    }

    private function formatRelatedSkill($skill): array
    {
        return [
            'id' => $skill->id,
            'name' => $skill->name,
            'similarity_score' => $skill->similarity_score,
            'category' => $skill->category->name ?? 'Uncategorized',
            'relationship' => $skill->relationship,
        ];
    }

    private function formatRecommendation($project): array
    {
        return [
            'project_id' => $project->id,
            'title' => $project->title,
            'recommendation_score' => $project->recommendation_score,
            'reasons' => $project->reasons,
            'confidence' => $project->confidence,
            'budget' => $this->formatBudget($project),
            'work_type' => $project->work_type,
            'posted_days_ago' => $project->created_at->diffInDays(now()),
        ];
    }

    private function formatProjectInfo(Project $project): array
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'budget' => $this->formatBudget($project),
            'work_type' => $project->work_type,
        ];
    }

    private function formatTalentInfo(TalentProfile $talent): array
    {
        return [
            'id' => $talent->id,
            'professional_title' => $talent->professional_title,
            'experience_level' => $talent->experience_level,
        ];
    }

    private function formatPortfolioInfo(Portfolio $portfolio): array
    {
        return [
            'id' => $portfolio->id,
            'title' => $portfolio->title,
            'user_id' => $portfolio->user_id,
        ];
    }

    private function formatSkillInfo(Skill $skill): array
    {
        return [
            'id' => $skill->id,
            'name' => $skill->name,
            'category' => $skill->category->name ?? 'Uncategorized',
        ];
    }

    private function formatBudget(Project $project): string
    {
        if ($project->budget_type === 'hourly') {
            return sprintf('$%d-$%d/hr %s', 
                $project->budget_min, 
                $project->budget_max, 
                $project->budget_currency
            );
        }
        return sprintf('$%d-%d %s', 
            $project->budget_min, 
            $project->budget_max, 
            $project->budget_currency
        );
    }
}