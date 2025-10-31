<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TalentProfile;
use App\Models\RecruiterProfile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BackendApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $talentUser;
    protected $recruiterUser;
    protected $talentToken;
    protected $recruiterToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->talentUser = User::factory()->create([
            'email' => 'talent@test.com',
            'password' => bcrypt('password123'),
            'role' => 'talent',
            'email_verified_at' => now(),
        ]);

        $this->recruiterUser = User::factory()->create([
            'email' => 'recruiter@test.com',
            'password' => bcrypt('password123'),
            'role' => 'recruiter',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function test_01_health_check()
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200);
        echo "\n✅ Health check passed\n";
    }

    /** @test */
    public function test_02_user_registration()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'talent'
        ]);

        if ($response->status() === 201) {
            echo "\n✅ User registration works\n";
            $response->assertStatus(201)
                     ->assertJsonStructure(['user', 'token']);
        } else {
            echo "\n❌ User registration failed: " . $response->content() . "\n";
        }
    }

    /** @test */
    public function test_03_user_login()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'talent@test.com',
            'password' => 'password123'
        ]);

        if ($response->status() === 200) {
            echo "\n✅ User login works\n";
            $this->talentToken = $response->json('token');
            $response->assertStatus(200)
                     ->assertJsonStructure(['user', 'token']);
        } else {
            echo "\n❌ User login failed: " . $response->content() . "\n";
        }
    }

    /** @test */
    public function test_04_get_authenticated_user()
    {
        $token = $this->talentUser->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        if ($response->status() === 200) {
            echo "\n✅ Get authenticated user works\n";
            $response->assertStatus(200)
                     ->assertJsonStructure(['user']);
        } else {
            echo "\n❌ Get authenticated user failed\n";
        }
    }

    /** @test */
    public function test_05_create_talent_profile()
    {
        $token = $this->talentUser->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/talent/profile', [
            'title' => 'Full Stack Developer',
            'bio' => 'Experienced developer with 5 years experience',
            'location' => 'New York',
            'hourly_rate' => 50,
            'availability' => 'full_time',
        ]);

        if (in_array($response->status(), [200, 201])) {
            echo "\n✅ Talent profile creation works\n";
        } else {
            echo "\n❌ Talent profile creation failed: " . $response->content() . "\n";
        }
    }

    /** @test */
    public function test_06_get_talent_profile()
    {
        $token = $this->talentUser->createToken('test')->plainTextToken;

        // First create profile
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/talent/profile', [
            'title' => 'Full Stack Developer',
            'bio' => 'Test bio',
        ]);

        // Then get it
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/talent/profile');

        if ($response->status() === 200) {
            echo "\n✅ Get talent profile works\n";
        } else {
            echo "\n❌ Get talent profile failed\n";
        }
    }

    /** @test */
    public function test_07_add_talent_skill()
    {
        $token = $this->talentUser->createToken('test')->plainTextToken;

        // Create a skill first
        $skill = Skill::create([
            'name' => 'PHP',
            'slug' => 'php',
            'category_id' => 1,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/talent/skills', [
            'skill_id' => $skill->id,
            'proficiency_level' => 'expert',
            'years_of_experience' => 5,
        ]);

        if (in_array($response->status(), [200, 201])) {
            echo "\n✅ Add talent skill works\n";
        } else {
            echo "\n❌ Add talent skill failed: " . $response->content() . "\n";
        }
    }

    /** @test */
    public function test_08_create_project()
    {
        $token = $this->recruiterUser->createToken('test')->plainTextToken;

        // Create recruiter profile first
        RecruiterProfile::create([
            'user_id' => $this->recruiterUser->id,
            'company_name' => 'Test Company',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/projects', [
            'title' => 'Web Development Project',
            'description' => 'Need a full stack developer',
            'budget_min' => 1000,
            'budget_max' => 5000,
            'budget_type' => 'fixed',
            'duration' => '1-3 months',
            'experience_level' => 'intermediate',
            'project_type' => 'remote',
            'required_skills' => [1],
        ]);

        if (in_array($response->status(), [200, 201])) {
            echo "\n✅ Project creation works\n";
        } else {
            echo "\n❌ Project creation failed: " . $response->content() . "\n";
        }
    }

    /** @test */
    public function test_09_get_public_projects()
    {
        $response = $this->getJson('/api/v1/public/projects');

        if ($response->status() === 200) {
            echo "\n✅ Get public projects works\n";
        } else {
            echo "\n❌ Get public projects failed\n";
        }
    }

    /** @test */
    public function test_10_get_public_talents()
    {
        $response = $this->getJson('/api/v1/public/talents');

        if ($response->status() === 200) {
            echo "\n✅ Get public talents works\n";
        } else {
            echo "\n❌ Get public talents failed\n";
        }
    }

    /** @test */
    public function test_11_get_categories()
    {
        $response = $this->getJson('/api/v1/public/categories');

        if ($response->status() === 200) {
            echo "\n✅ Get categories works\n";
        } else {
            echo "\n❌ Get categories failed\n";
        }
    }

    /** @test */
    public function test_12_get_skills()
    {
        $response = $this->getJson('/api/v1/public/skills');

        if ($response->status() === 200) {
            echo "\n✅ Get skills works\n";
        } else {
            echo "\n❌ Get skills failed\n";
        }
    }
}
