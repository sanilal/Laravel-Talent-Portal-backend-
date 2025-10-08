<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\SimilarityHelper;

class SimilarityHelperTest extends TestCase
{
    /** @test */
    public function it_calculates_cosine_similarity_for_identical_vectors()
    {
        $vector1 = [1, 0, 0];
        $vector2 = [1, 0, 0];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(1.0, $similarity);
    }

    /** @test */
    public function it_calculates_cosine_similarity_for_orthogonal_vectors()
    {
        $vector1 = [1, 0, 0];
        $vector2 = [0, 1, 0];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(0.0, $similarity);
    }

    /** @test */
    public function it_calculates_cosine_similarity_for_opposite_vectors()
    {
        $vector1 = [1, 0, 0];
        $vector2 = [-1, 0, 0];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(-1.0, $similarity);
    }

    /** @test */
    public function it_calculates_cosine_similarity_for_similar_vectors()
    {
        $vector1 = [1, 2, 3];
        $vector2 = [2, 4, 6]; // Double of vector1, should be identical direction

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(1.0, $similarity, '', 0.0001);
    }

    /** @test */
    public function it_calculates_cosine_similarity_for_partially_similar_vectors()
    {
        $vector1 = [1, 1, 0];
        $vector2 = [1, 0, 1];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        // Dot product = 1, magnitudes = sqrt(2) each
        // Similarity = 1 / (sqrt(2) * sqrt(2)) = 1/2 = 0.5
        $this->assertEqualsWithDelta(0.5, $similarity, 0.0001);
    }

    /** @test */
    public function it_throws_exception_for_different_dimension_vectors()
    {
        $this->expectException(\InvalidArgumentException::class);

        $vector1 = [1, 2, 3];
        $vector2 = [1, 2]; // Different dimension

        SimilarityHelper::cosineSimilarity($vector1, $vector2);
    }

    /** @test */
    public function it_returns_zero_for_zero_magnitude_vectors()
    {
        $vector1 = [0, 0, 0];
        $vector2 = [1, 2, 3];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(0.0, $similarity);
    }

    /** @test */
    public function it_returns_zero_for_empty_vectors()
    {
        $vector1 = [];
        $vector2 = [];

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(0.0, $similarity);
    }

    /** @test */
    public function it_calculates_weighted_similarity_correctly()
    {
        $similarities = [0.8, 0.9, 0.7];
        $weights = [0.5, 0.3, 0.2];

        $weighted = SimilarityHelper::weightedSimilarity($similarities, $weights);

        // (0.8 * 0.5 + 0.9 * 0.3 + 0.7 * 0.2) / (0.5 + 0.3 + 0.2)
        // = (0.4 + 0.27 + 0.14) / 1.0 = 0.81
        $this->assertEqualsWithDelta(0.81, $weighted, 0.0001);
    }

    /** @test */
    public function it_normalizes_weights_automatically()
    {
        $similarities = [0.8, 0.6];
        $weights = [2, 3]; // Not summing to 1

        $weighted = SimilarityHelper::weightedSimilarity($similarities, $weights);

        // (0.8 * 2 + 0.6 * 3) / (2 + 3) = (1.6 + 1.8) / 5 = 0.68
        $this->assertEqualsWithDelta(0.68, $weighted, 0.0001);
    }

    /** @test */
    public function it_throws_exception_for_mismatched_arrays_in_weighted_similarity()
    {
        $this->expectException(\InvalidArgumentException::class);

        $similarities = [0.8, 0.9, 0.7];
        $weights = [0.5, 0.3]; // Different length

        SimilarityHelper::weightedSimilarity($similarities, $weights);
    }

    /** @test */
    public function it_returns_zero_for_empty_arrays_in_weighted_similarity()
    {
        $similarities = [];
        $weights = [];

        $weighted = SimilarityHelper::weightedSimilarity($similarities, $weights);

        $this->assertEquals(0.0, $weighted);
    }

    /** @test */
    public function it_normalizes_similarity_score_correctly()
    {
        // Test various similarity scores
        $this->assertEquals(100, SimilarityHelper::normalizeScore(1.0));   // Perfect match
        $this->assertEquals(50, SimilarityHelper::normalizeScore(0.0));    // Orthogonal
        $this->assertEquals(0, SimilarityHelper::normalizeScore(-1.0));    // Opposite
        $this->assertEquals(75, SimilarityHelper::normalizeScore(0.5));    // Partial match
        $this->assertEquals(90, SimilarityHelper::normalizeScore(0.8));
    }

    /** @test */
    public function it_performs_batch_similarity_calculation()
    {
        $sourceVector = [1, 0, 0];
        $targetVectors = [
            'a' => [1, 0, 0],  // Same direction, similarity = 1.0
            'b' => [0, 1, 0],  // Orthogonal, similarity = 0.0
            'c' => [1, 1, 0],  // Partial match
        ];

        $results = SimilarityHelper::batchSimilarity($sourceVector, $targetVectors);

        $this->assertArrayHasKey('a', $results);
        $this->assertArrayHasKey('b', $results);
        $this->assertArrayHasKey('c', $results);
        $this->assertEquals(1.0, $results['a']);
        $this->assertEquals(0.0, $results['b']);
        $this->assertEqualsWithDelta(0.707, $results['c'], 0.01);
    }

    /** @test */
    public function it_finds_top_similar_vectors()
    {
        $sourceVector = [1, 0, 0];
        $targetVectors = [
            'a' => [1, 0, 0],      // Similarity = 1.0
            'b' => [0.9, 0.1, 0],  // High similarity
            'c' => [0, 1, 0],      // Similarity = 0.0
            'd' => [0.8, 0.2, 0],  // Medium similarity
            'e' => [0.5, 0.5, 0],  // Lower similarity
        ];

        $topSimilar = SimilarityHelper::topSimilar($sourceVector, $targetVectors, 3, 0.0);

        $this->assertCount(3, $topSimilar);
        $this->assertArrayHasKey('a', $topSimilar);
        $this->assertArrayHasKey('b', $topSimilar);
        $this->assertArrayHasKey('d', $topSimilar);
        
        // Check ordering (highest first)
        $keys = array_keys($topSimilar);
        $this->assertEquals('a', $keys[0]);
        $this->assertEquals('b', $keys[1]);
    }

    /** @test */
    public function it_filters_by_minimum_similarity()
    {
        $sourceVector = [1, 0, 0];
        $targetVectors = [
            'a' => [1, 0, 0],      // Similarity = 1.0
            'b' => [0.9, 0.1, 0],  // High similarity ~0.995
            'c' => [0.5, 0.5, 0],  // Lower similarity ~0.707
        ];

        $topSimilar = SimilarityHelper::topSimilar($sourceVector, $targetVectors, 10, 0.9);

        // Only 'a' and 'b' should pass the 0.9 threshold
        $this->assertCount(2, $topSimilar);
        $this->assertArrayHasKey('a', $topSimilar);
        $this->assertArrayHasKey('b', $topSimilar);
        $this->assertArrayNotHasKey('c', $topSimilar);
    }

    /** @test */
    public function it_determines_match_quality_correctly()
    {
        $this->assertEquals('excellent', SimilarityHelper::matchQuality(0.95));
        $this->assertEquals('excellent', SimilarityHelper::matchQuality(0.9));
        $this->assertEquals('very_good', SimilarityHelper::matchQuality(0.85));
        $this->assertEquals('very_good', SimilarityHelper::matchQuality(0.8));
        $this->assertEquals('good', SimilarityHelper::matchQuality(0.75));
        $this->assertEquals('good', SimilarityHelper::matchQuality(0.7));
        $this->assertEquals('fair', SimilarityHelper::matchQuality(0.65));
        $this->assertEquals('fair', SimilarityHelper::matchQuality(0.6));
        $this->assertEquals('moderate', SimilarityHelper::matchQuality(0.55));
        $this->assertEquals('moderate', SimilarityHelper::matchQuality(0.5));
        $this->assertEquals('poor', SimilarityHelper::matchQuality(0.4));
        $this->assertEquals('poor', SimilarityHelper::matchQuality(0.0));
    }

    /** @test */
    public function it_generates_insights_from_breakdown()
    {
        $breakdown = [
            'skills_match' => 0.92,
            'profile_match' => 0.78,
            'experience_match' => 0.55,
        ];

        $insights = SimilarityHelper::generateInsights($breakdown);

        $this->assertIsArray($insights);
        $this->assertNotEmpty($insights);
        
        // Should identify strong skills match
        $this->assertStringContainsString('skills', implode(' ', $insights));
    }

    /** @test */
    public function it_handles_high_dimensional_vectors()
    {
        // Test with 384-dimensional vectors (like in the actual system)
        $vector1 = array_fill(0, 384, 0.5);
        $vector2 = array_fill(0, 384, 0.5);

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEquals(1.0, $similarity);
    }

    /** @test */
    public function it_handles_realistic_embedding_vectors()
    {
        // Simulate realistic embeddings with random values
        $vector1 = array_map(fn() => (rand(-100, 100) / 100), range(1, 384));
        $vector2 = $vector1; // Identical should give 1.0

        $similarity = SimilarityHelper::cosineSimilarity($vector1, $vector2);

        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    /** @test */
    public function it_is_symmetric()
    {
        // cos_sim(A, B) should equal cos_sim(B, A)
        $vector1 = [1, 2, 3, 4, 5];
        $vector2 = [5, 4, 3, 2, 1];

        $sim1 = SimilarityHelper::cosineSimilarity($vector1, $vector2);
        $sim2 = SimilarityHelper::cosineSimilarity($vector2, $vector1);

        $this->assertEquals($sim1, $sim2);
    }
}