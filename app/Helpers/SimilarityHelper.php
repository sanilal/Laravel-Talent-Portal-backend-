<?php

namespace App\Helpers;

class SimilarityHelper
{
    /**
     * Calculate cosine similarity between two vectors
     * Returns value between -1 and 1 (1 = identical, 0 = orthogonal, -1 = opposite)
     * 
     * @param array $vector1 First embedding vector
     * @param array $vector2 Second embedding vector
     * @return float Similarity score
     * @throws \InvalidArgumentException If vectors have different dimensions
     */
    public static function cosineSimilarity(array $vector1, array $vector2): float
    {
        // Validate dimensions match
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException(
                sprintf('Vectors must have same dimensions. Got %d and %d', 
                    count($vector1), 
                    count($vector2)
                )
            );
        }

        // Handle empty vectors
        if (empty($vector1) || empty($vector2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        // Calculate dot product and magnitudes in single pass
        for ($i = 0; $i < count($vector1); $i++) {
            $dotProduct += $vector1[$i] * $vector2[$i];
            $magnitude1 += $vector1[$i] ** 2;
            $magnitude2 += $vector2[$i] ** 2;
        }
        
        // Calculate magnitudes (square root of sum of squares)
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        // Avoid division by zero
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }
        
        // Cosine similarity = dot product / (magnitude1 * magnitude2)
        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Calculate weighted similarity from multiple embedding pairs
     * 
     * @param array $similarities Array of similarity scores
     * @param array $weights Array of weights (must sum to 1.0 or will be normalized)
     * @return float Weighted average similarity
     */
    public static function weightedSimilarity(array $similarities, array $weights): float
    {
        if (count($similarities) !== count($weights)) {
            throw new \InvalidArgumentException('Similarities and weights arrays must have same length');
        }

        if (empty($similarities)) {
            return 0.0;
        }

        $totalWeight = array_sum($weights);
        
        // Avoid division by zero
        if ($totalWeight == 0) {
            return 0.0;
        }

        $weightedSum = 0;
        
        foreach ($similarities as $index => $score) {
            $weight = $weights[$index] ?? 0;
            $weightedSum += $score * $weight;
        }
        
        return $weightedSum / $totalWeight;
    }

    /**
     * Normalize similarity score to 0-100 range
     * Converts cosine similarity (-1 to 1) to percentage (0 to 100)
     * 
     * @param float $similarity Cosine similarity score
     * @return int Normalized score (0-100)
     */
    public static function normalizeScore(float $similarity): int
    {
        // Cosine similarity ranges from -1 to 1
        // Convert to 0-100 percentage: ((similarity + 1) / 2) * 100
        return (int) round((($similarity + 1) / 2) * 100);
    }

    /**
     * Calculate multiple similarities at once
     * Useful for comparing one vector against many
     * 
     * @param array $sourceVector The reference vector
     * @param array $targetVectors Array of vectors to compare against
     * @return array Array of similarity scores
     */
    public static function batchSimilarity(array $sourceVector, array $targetVectors): array
    {
        $results = [];
        
        foreach ($targetVectors as $key => $targetVector) {
            $results[$key] = self::cosineSimilarity($sourceVector, $targetVector);
        }
        
        return $results;
    }

    /**
     * Find top N most similar vectors
     * 
     * @param array $sourceVector The reference vector
     * @param array $targetVectors Associative array of vectors with keys as identifiers
     * @param int $topN Number of top results to return
     * @param float $minSimilarity Minimum similarity threshold (0.0 to 1.0)
     * @return array Sorted array of [key => similarity] pairs
     */
    public static function topSimilar(
        array $sourceVector, 
        array $targetVectors, 
        int $topN = 10,
        float $minSimilarity = 0.0
    ): array {
        $similarities = [];
        
        foreach ($targetVectors as $key => $targetVector) {
            $similarity = self::cosineSimilarity($sourceVector, $targetVector);
            
            if ($similarity >= $minSimilarity) {
                $similarities[$key] = $similarity;
            }
        }
        
        // Sort by similarity descending
        arsort($similarities);
        
        // Return top N
        return array_slice($similarities, 0, $topN, true);
    }

    /**
     * Determine match quality based on similarity score
     * 
     * @param float $similarity Similarity score (0.0 to 1.0)
     * @return string Quality rating
     */
    public static function matchQuality(float $similarity): string
    {
        if ($similarity >= 0.9) return 'excellent';
        if ($similarity >= 0.8) return 'very_good';
        if ($similarity >= 0.7) return 'good';
        if ($similarity >= 0.6) return 'fair';
        if ($similarity >= 0.5) return 'moderate';
        return 'poor';
    }

    /**
     * Generate match insights based on breakdown scores
     * 
     * @param array $breakdown Associative array of component scores
     * @param array $weights Weights for each component
     * @return array Insights about the match
     */
    public static function generateInsights(array $breakdown, array $weights = []): array
    {
        $insights = [];
        
        // Find strongest and weakest components
        arsort($breakdown);
        $strongest = array_key_first($breakdown);
        $strongestScore = $breakdown[$strongest];
        
        $weakest = array_key_last($breakdown);
        $weakestScore = $breakdown[$weakest];
        
        // Generate insights
        if ($strongestScore >= 0.8) {
            $insights[] = ucfirst(str_replace('_', ' ', $strongest)) . ' shows excellent alignment';
        }
        
        if ($weakestScore < 0.6) {
            $insights[] = ucfirst(str_replace('_', ' ', $weakest)) . ' could be improved';
        }
        
        // Overall assessment
        $average = array_sum($breakdown) / count($breakdown);
        if ($average >= 0.8) {
            $insights[] = 'Strong overall match';
        } elseif ($average >= 0.7) {
            $insights[] = 'Good overall compatibility';
        }
        
        return $insights;
    }
}