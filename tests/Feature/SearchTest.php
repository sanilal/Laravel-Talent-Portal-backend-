<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TalentProfile;
use App\Models\Project;
use App\Models\Portfolio;
use App\Models\Skill;
use App\Models\RecruiterProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $talent;
    protected $recruiter;
    protected $talentProfile;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test talent user
        $this->talent = User::factory()->create([
            'role' => 'talent',
            'email_verified_at' => now(),
        ]);

        // Create talent profile with embeddings
        $this->talentProfile = TalentProfile::factory()->create([
            'user_id' => $this->talent->id,
            'professional_title' => 'Senior React Developer',
            'summary' => 'Expert in React, TypeScript, and modern web development',
            'experience_level' => 'senior',
            'profile_embedding' => array_fill(0, 384, 0.5), // Mock embedding
            'skills_embedding' => array_fill(0, 384, 0.5),
            'experience_embedding' => array_fill(0, 384, 0.5),
            'embeddings_generated_at' => now(),
        ]);

        // Create recruiter user
        $this->recruiter = User::factory()->create([
            'role' => 'recruiter',
            'email_verified_at' => now(),
        ]);

        RecruiterProfile::factory()->create([
            'user_id' => $this->recruiter->id,
        ]);

        // Create project with embeddings
        $this->project = Project::factory()->create([
            'recruiter_id' => $this->recruiter->id,
            'title' => 'Senior React Developer Needed',
            'description' => 'Looking for experienced React developer',
            'status' => 'open',
            'requirements_embedding' => array_fill(0, 384, 0.5),
            'required_skills_embedding' => array_fill(0, 384, 0.5),
            'embeddings_generated_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_search_talents_with_text_query()
    {
        $response = $this->actingAs($this->recruiter, 'sanctum')
            ->postJson('/api/v1/search/talents', [
                'query' => 'React developer',
                'limit' => 10,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'results' => [
                        '*' => [
                            'id',
                            'professional_title',
                            'similarity_score',
                            'match_quality',
                            'breakdown',
                            'match_reasons',
                        ]
                    ],
                    'total',
                    'query',
                    'execution_time_ms',
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('React developer', $response->json('data.query'));
    }

    /** @test */
    public function it_validates_search_query_is_required()
    {
        $response = $this->actingAs($this->recruiter, 'sanctum')
            ->postJson('/api/v1/search/talents', [
                'limit' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    /** @test */
    public function it_can_apply_filters_to_talent_search()
    {
        $response = $this->actingAs($this->recruiter, 'sanctum')
            ->postJson('/api/v1/search/talents', [
                'query' => 'developer',
                'filters' => [
                    'experience_level' => 'senior',
                    'hourly_rate_max' => 150,
                    'availability' => true,
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'filters_applied' => true,
                ]
            ]);
    }

    /** @test */
    public function it_can_match_talents_to_project()
    {
        $response = $this->actingAs($this->recruiter, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/match-talents", [
                'limit' => 20,
                'min_similarity' => 0.5,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'project',
                    'matches' => [
                        '*' => [
                            'talent_id',
                            'professional_title',
                            'overall_score',
                            'breakdown',
                            'strengths',
                            'gaps',
                        ]
                    ],
                    'total_analyzed',
                    'total_matched',
                ]
            ]);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_project_matching()
    {
        $otherRecruiter = User::factory()->create(['role' => 'recruiter']);

        $response = $this->actingAs($otherRecruiter, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/match-talents");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access to this project'
            ]);
    }

    /** @test */
    public function it_can_match_projects_to_talent()
    {
        $response = $this->actingAs($this->talent, 'sanctum')
            ->postJson("/api/v1/talents/{$this->talentProfile->id}/match-projects", [
                'limit' => 20,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'talent',
                    'recommended_projects' => [
                        '*' => [
                            'project_id',
                            'title',
                            'match_score',
                            'compatibility',
                            'why_good_fit',
                        ]
                    ],
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_talent_matching()
    {
        $otherTalent = User::factory()->create(['role' => 'talent']);

        $response = $this->actingAs($otherTalent, 'sanctum')
            ->postJson("/api/v1/talents/{$this->talentProfile->id}/match-projects");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized access to this talent profile'
            ]);
    }

    /** @test */
    public function it_can_find_similar_portfolios()
    {
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->talent->id,
            'title' => 'E-commerce Platform',
            'description' => 'Built a full-stack e-commerce platform',
            'description_embedding' => array_fill(0, 384, 0.5),
            'embeddings_generated_at' => now(),
        ]);

        // Create another portfolio to find
        Portfolio::factory()->create([
            'user_id' => $this->talent->id,
            'title' => 'Online Store',
            'description' => 'Developed an online retail store',
            'description_embedding' => array_fill(0, 384, 0.5),
            'embeddings_generated_at' => now(),
        ]);

        $response = $this->actingAs($this->talent, 'sanctum')
            ->getJson("/api/v1/portfolios/{$portfolio->id}/similar?limit=10");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'source_portfolio',
                    'similar_portfolios' => [
                        '*' => [
                            'id',
                            'title',
                            'similarity_score',
                            'common_elements',
                        ]
                    ],
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_portfolio()
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->talent, 'sanctum')
            ->getJson("/api/v1/portfolios/{$fakeId}/similar");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Portfolio not found'
            ]);
    }

    /** @test */
    public function it_can_find_related_skills()
    {
        $skill = Skill::factory()->create([
            'name' => 'React',
            'skill_embedding' => array_fill(0, 384, 0.5),
            'embeddings_generated_at' => now(),
        ]);

        // Create related skills
        Skill::factory()->create([
            'name' => 'Vue.js',
            'skill_embedding' => array_fill(0, 384, 0.48),
            'embeddings_generated_at' => now(),
        ]);

        Skill::factory()->create([
            'name' => 'Angular',
            'skill_embedding' => array_fill(0, 384, 0.47),
            'embeddings_generated_at' => now(),
        ]);

        $response = $this->actingAs($this->talent, 'sanctum')
            ->getJson("/api/v1/skills/{$skill->id}/related?limit=15");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'skill',
                    'related_skills' => [
                        '*' => [
                            'id',
                            'name',
                            'similarity_score',
                            'category',
                            'relationship',
                        ]
                    ],
                    'clusters',
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_can_generate_recommendations()
    {
        $response = $this->actingAs($this->talent, 'sanctum')
            ->getJson("/api/v1/talents/{$this->talentProfile->id}/recommendations?limit=10");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recommendations' => [
                        '*' => [
                            'project_id',
                            'title',
                            'recommendation_score',
                            'reasons',
                            'confidence',
                            'budget',
                        ]
                    ],
                    'personalization_factors',
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        // Test search talents
        $response = $this->postJson('/api/v1/search/talents', [
            'query' => 'developer'
        ]);
        $response->assertStatus(401);

        // Test match talents to project
        $response = $this->postJson("/api/v1/projects/{$this->project->id}/match-talents");
        $response->assertStatus(401);

        // Test match projects to talent
        $response = $this->postJson("/api/v1/talents/{$this->talentProfile->id}/match-projects");
        $response->assertStatus(401);

        // Test similar portfolios
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->talent->id,
            'description_embedding' => array_fill(0, 384, 0.5),
        ]);
        $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}/similar");
        $response->assertStatus(401);

        // Test related skills
        $skill = Skill::factory()->create([
            'skill_embedding' => array_fill(0, 384, 0.5),
        ]);
        $response = $this->getJson("/api/v1/skills/{$skill->id}/related");
        $response->assertStatus(401);

        // Test recommendations
        $response = $this->getJson("/api/v1/talents/{$this->talentProfile->id}/recommendations");
        $response->assertStatus(401);
    }
}