<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    private $faker;
    private $userIds = [];
    private $talentIds = [];
    private $recruiterIds = [];
    private $recruiterProfileIds = [];
    private $categoryIds = [];
    private $skillIds = [];
    private $projectIds = [];
    private $castingCallIds = [];
    private $talentProfileIds = [];
    
    public function run(): void
    {
        $this->faker = Faker::create();
        
        $this->command->info('🗑️  Clearing existing data...');
        $this->clearExistingData();
        
        $this->command->info('📊 Seeding categories...');
        $this->seedCategories();
        
        $this->command->info('🎯 Seeding skills...');
        $this->seedSkills();
        
        $this->command->info('👥 Seeding users...');
        $this->seedUsers();
        
        $this->command->info('🎭 Seeding talent profiles...');
        $this->seedTalentProfiles();
        
        $this->command->info('💼 Seeding recruiter profiles...');
        $this->seedRecruiterProfiles();
        
        $this->command->info('🎓 Seeding education...');
        $this->seedEducation();
        
        $this->command->info('💡 Seeding experiences...');
        $this->seedExperiences();
        
        $this->command->info('📁 Seeding portfolios...');
        $this->seedPortfolios();
        
        $this->command->info('🏢 Seeding projects...');
        $this->seedProjects();
        
        $this->command->info('🎬 Seeding casting calls...');
        $this->seedCastingCalls();
        
        $this->command->info('📝 Seeding applications...');
        $this->seedApplications();
        
        $this->command->info('💬 Seeding messages...');
        $this->seedMessages();
        
        $this->command->info('⭐ Seeding reviews...');
        $this->seedReviews();
        
        $this->command->info('🔔 Seeding notifications...');
        $this->seedNotifications();
        
        $this->command->info('🖼️  Seeding media...');
        $this->seedMedia();
        
        $this->command->info('🔗 Seeding talent skills...');
        $this->seedTalentSkills();
        
        $this->command->info('✅ Database seeding completed successfully!');
    }
    
    private function clearExistingData(): void
    {
        // Don't delete migrations, cache, sessions, telescope, job queues
        $tablesToClear = [
            'talent_skills',
            'media',
            'notifications',
            'reviews',
            'messages',
            'applications',
            'casting_calls',
            'projects',
            'portfolios',
            'experiences',
            'education',
            'talent_profiles',
            'recruiter_profiles',
            'personal_access_tokens',
            'password_reset_tokens',
            'login_attempts',
            'email_verification_attempts',
            'users',
            'skills',
            'categories',
        ];
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tablesToClear as $table) {
            DB::table($table)->truncate();
            $this->command->info("  ✓ Cleared {$table}");
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
    
    private function generateLanguages(): array
    {
        $languages = ['English', 'Spanish', 'French', 'German', 'Mandarin', 'Japanese', 'Arabic', 'Portuguese'];
        $proficiencies = ['Native', 'Fluent', 'Intermediate', 'Beginner'];
        
        $count = $this->faker->numberBetween(1, 3);
        $selectedLanguages = $this->faker->randomElements($languages, $count);
        
        $result = [];
        foreach ($selectedLanguages as $language) {
            $result[$language] = $this->faker->randomElement($proficiencies);
        }
        
        return $result;
    }
    
    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Web development and programming',
                'icon' => 'code',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Mobile Development',
                'slug' => 'mobile-development',
                'description' => 'Mobile app development',
                'icon' => 'mobile',
                'color' => '#8B5CF6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'description' => 'Design and creative work',
                'icon' => 'palette',
                'color' => '#EC4899',
                'sort_order' => 3,
            ],
            [
                'name' => 'Data Science',
                'slug' => 'data-science',
                'description' => 'Data analysis and machine learning',
                'icon' => 'chart',
                'color' => '#10B981',
                'sort_order' => 4,
            ],
            [
                'name' => 'DevOps',
                'slug' => 'devops',
                'description' => 'DevOps and infrastructure',
                'icon' => 'server',
                'color' => '#F59E0B',
                'sort_order' => 5,
            ],
            [
                'name' => 'Acting',
                'slug' => 'acting',
                'description' => 'Acting and performance',
                'icon' => 'drama',
                'color' => '#EF4444',
                'sort_order' => 6,
            ],
            [
                'name' => 'Music',
                'slug' => 'music',
                'description' => 'Music and audio production',
                'icon' => 'music',
                'color' => '#8B5CF6',
                'sort_order' => 7,
            ],
        ];
        
        foreach ($categories as $category) {
            $id = (string) Str::orderedUuid();
            $this->categoryIds[] = $id;
            
            DB::table('categories')->insert(array_merge($category, [
                'id' => $id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
    
    private function seedSkills(): void
    {
        $skillsByCategory = [
            0 => [ // Web Development
                'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React', 'Vue.js', 
                'Node.js', 'HTML', 'CSS', 'Tailwind CSS', 'PostgreSQL', 'MySQL'
            ],
            1 => [ // Mobile Development
                'React Native', 'Flutter', 'Swift', 'Kotlin', 'iOS', 'Android'
            ],
            2 => [ // Design
                'UI/UX Design', 'Figma', 'Adobe Photoshop', 'Adobe Illustrator', 
                'Sketch', 'Graphic Design', 'Web Design'
            ],
            3 => [ // Data Science
                'Python', 'Machine Learning', 'TensorFlow', 'PyTorch', 
                'Data Analysis', 'SQL', 'Pandas', 'NumPy'
            ],
            4 => [ // DevOps
                'Docker', 'Kubernetes', 'AWS', 'CI/CD', 'Jenkins', 'Terraform'
            ],
            5 => [ // Acting
                'Method Acting', 'Voice Acting', 'Stage Acting', 'Improvisation',
                'Character Development', 'Script Analysis'
            ],
            6 => [ // Music
                'Vocal Performance', 'Piano', 'Guitar', 'Music Production',
                'Sound Engineering', 'Composition'
            ],
        ];
        
        foreach ($skillsByCategory as $categoryIndex => $skills) {
            foreach ($skills as $skillName) {
                $id = (string) Str::orderedUuid();
                $this->skillIds[] = $id;
                
                DB::table('skills')->insert([
                    'id' => $id,
                    'name' => $skillName,
                    'slug' => Str::slug($skillName),
                    'description' => "Professional {$skillName} skills",
                    'category_id' => $this->categoryIds[$categoryIndex],
                    'is_featured' => $this->faker->boolean(20),
                    'is_active' => true,
                    'usage_count' => $this->faker->numberBetween(0, 100),
                    'embedding_model' => 'all-MiniLM-L6-v2',
                    'talents_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedUsers(): void
    {
        // Create admin user
        $adminId = (string) Str::orderedUuid();
        $this->userIds[] = $adminId;
        
        DB::table('users')->insert([
            'id' => $adminId,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@talentsyouneed.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'user_type' => 'admin',
            'account_status' => 'active',
            'is_verified' => true,
            'is_email_verified' => true,
            'timezone' => 'America/New_York',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create 30 talent users
        for ($i = 1; $i <= 30; $i++) {
            $id = (string) Str::orderedUuid();
            $this->userIds[] = $id;
            $this->talentIds[] = $id;
            
            $gender = $this->faker->randomElement(['male', 'female', 'other', 'prefer_not_to_say']);
            
            DB::table('users')->insert([
                'id' => $id,
                'first_name' => $this->faker->firstName($gender === 'male' ? 'male' : ($gender === 'female' ? 'female' : null)),
                'last_name' => $this->faker->lastName,
                'email' => "talent{$i}@example.com",
                'email_verified_at' => $this->faker->boolean(80) ? now() : null,
                'password' => Hash::make('password'),
                'phone' => $this->faker->phoneNumber,
                'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
                'gender' => $gender,
                'bio' => $this->faker->paragraph(3),
                'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                'website' => $this->faker->boolean(30) ? $this->faker->url : null,
                'user_type' => 'talent',
                'account_status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive', 'suspended']),
                'is_verified' => $this->faker->boolean(70),
                'is_email_verified' => $this->faker->boolean(80),
                'last_login_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'last_login_ip' => $this->faker->ipv4,
                'timezone' => $this->faker->timezone,
                'professional_title' => $this->faker->jobTitle,
                'city' => $this->faker->city,
                'state' => $this->faker->stateAbbr,
                'country' => $this->faker->country,
                'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
                'currency' => 'USD',
                'experience_level' => $this->faker->randomElement(['entry', 'intermediate', 'senior', 'expert']),
                'availability_status' => $this->faker->randomElement(['available', 'busy', 'not_available']),
                'profile_views' => $this->faker->numberBetween(0, 500),
                'profile_completion' => $this->faker->numberBetween(50, 100),
                'languages' => json_encode($this->faker->randomElements(['English', 'Spanish', 'French', 'German', 'Mandarin', 'Japanese'], $this->faker->numberBetween(1, 3))),
                'linkedin_url' => $this->faker->boolean(50) ? 'https://linkedin.com/in/' . $this->faker->userName : null,
                'twitter_url' => $this->faker->boolean(30) ? 'https://twitter.com/' . $this->faker->userName : null,
                'instagram_url' => $this->faker->boolean(40) ? 'https://instagram.com/' . $this->faker->userName : null,
                'height' => $this->faker->numberBetween(150, 200),
                'weight' => $this->faker->numberBetween(50, 100),
                'hair_color' => $this->faker->randomElement(['Black', 'Brown', 'Blonde', 'Red', 'Gray']),
                'eye_color' => $this->faker->randomElement(['Brown', 'Blue', 'Green', 'Hazel', 'Gray']),
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
                'last_activity_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ]);
        }
        
        // Create 15 recruiter users
        for ($i = 1; $i <= 15; $i++) {
            $id = (string) Str::orderedUuid();
            $this->userIds[] = $id;
            $this->recruiterIds[] = $id;
            
            DB::table('users')->insert([
                'id' => $id,
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => "recruiter{$i}@example.com",
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => $this->faker->phoneNumber,
                'bio' => $this->faker->paragraph(2),
                'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                'user_type' => 'recruiter',
                'account_status' => 'active',
                'is_verified' => true,
                'is_email_verified' => true,
                'timezone' => $this->faker->timezone,
                'professional_title' => $this->faker->randomElement(['Talent Scout', 'Casting Director', 'HR Manager', 'Recruitment Manager']),
                'city' => $this->faker->city,
                'state' => $this->faker->stateAbbr,
                'country' => $this->faker->country,
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
                'last_activity_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ]);
        }
    }
    
    private function seedTalentProfiles(): void
    {
        foreach ($this->talentIds as $userId) {
            $id = (string) Str::orderedUuid();
            $this->talentProfileIds[] = $id;
            
            DB::table('talent_profiles')->insert([
                'id' => $id,
                'user_id' => $userId,
                'primary_category_id' => $this->faker->randomElement($this->categoryIds),
                'professional_title' => $this->faker->jobTitle,
                'summary' => $this->faker->paragraph(5),
                'experience_level' => $this->faker->randomElement(['entry', 'intermediate', 'senior', 'expert']),
                'hourly_rate_min' => $this->faker->randomFloat(2, 25, 75),
                'hourly_rate_max' => $this->faker->randomFloat(2, 100, 200),
                'currency' => 'USD',
                'availability_types' => json_encode($this->faker->randomElements(['full_time', 'part_time', 'contract', 'freelance'], $this->faker->numberBetween(1, 3))),
                'is_available' => $this->faker->boolean(75),
                'work_preferences' => json_encode([
                    'remote' => $this->faker->boolean(60),
                    'on_site' => $this->faker->boolean(40),
                    'hybrid' => $this->faker->boolean(50),
                ]),
                'preferred_locations' => json_encode($this->faker->randomElements(['United States', 'Europe', 'Asia', 'Remote'], $this->faker->numberBetween(1, 3))),
                'notice_period' => $this->faker->randomElement(['immediate', '1 week', '2 weeks', '1 month', '3 months']),
                'languages' => json_encode($this->generateLanguages()),
                'profile_completion_percentage' => $this->faker->numberBetween(60, 100),
                'is_featured' => $this->faker->boolean(10),
                'is_public' => $this->faker->boolean(85),
                'profile_views' => $this->faker->numberBetween(0, 500),
                'average_rating' => $this->faker->randomFloat(2, 3.0, 5.0),
                'total_ratings' => $this->faker->numberBetween(0, 50),
                'portfolio_highlights' => $this->faker->boolean(30) ? json_encode($this->faker->words(5)) : null,
                'availability_updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'embedding_model' => 'all-MiniLM-L6-v2',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedRecruiterProfiles(): void
    {
        foreach ($this->recruiterIds as $userId) {
            $id = (string) Str::orderedUuid();
            $this->recruiterProfileIds[] = $id;
            $companyName = $this->faker->company;
            
            DB::table('recruiter_profiles')->insert([
                'id' => $id,
                'user_id' => $userId,
                'company_name' => $companyName,
                'company_slug' => Str::slug($companyName) . '-' . $this->faker->randomNumber(4),
                'company_description' => $this->faker->paragraph(3),
                'industry' => $this->faker->randomElement(['Film', 'Television', 'Theater', 'Advertising', 'Technology', 'Entertainment', 'Media', 'Production']),
                'company_size' => $this->faker->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
                'company_website' => $this->faker->url,
                'company_email' => $this->faker->companyEmail,
                'company_phone' => $this->faker->phoneNumber,
                'company_address' => json_encode([
                    'street' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->stateAbbr,
                    'country' => $this->faker->country,
                    'postal_code' => $this->faker->postcode,
                ]),
                'company_logo_url' => null,
                'social_links' => json_encode([
                    'linkedin' => $this->faker->boolean(60) ? 'https://linkedin.com/company/' . Str::slug($companyName) : null,
                    'twitter' => $this->faker->boolean(40) ? 'https://twitter.com/' . Str::slug($companyName) : null,
                    'facebook' => $this->faker->boolean(30) ? 'https://facebook.com/' . Str::slug($companyName) : null,
                ]),
                'founded_year' => $this->faker->numberBetween(1980, 2023),
                'company_type' => $this->faker->randomElement(['Private', 'Public', 'Non-profit', 'Startup', 'Agency']),
                'employee_count' => $this->faker->numberBetween(5, 1000),
                'annual_revenue' => $this->faker->randomFloat(2, 100000, 50000000),
                'company_benefits' => json_encode($this->faker->randomElements([
                    'Health Insurance',
                    'Dental Insurance',
                    '401k Matching',
                    'Remote Work',
                    'Flexible Hours',
                    'Paid Time Off',
                    'Professional Development',
                    'Gym Membership',
                ], $this->faker->numberBetween(3, 6))),
                'culture_values' => json_encode($this->faker->randomElements([
                    'Innovation',
                    'Collaboration',
                    'Diversity',
                    'Work-Life Balance',
                    'Professional Growth',
                    'Transparency',
                    'Excellence',
                ], $this->faker->numberBetween(3, 5))),
                'hiring_preferences' => json_encode([
                    'remote_friendly' => $this->faker->boolean(60),
                    'offers_internships' => $this->faker->boolean(40),
                    'hiring_urgency' => $this->faker->randomElement(['low', 'medium', 'high']),
                ]),
                'is_verified' => $this->faker->boolean(75),
                'is_featured' => $this->faker->boolean(15),
                'verification_status' => $this->faker->randomElement(['pending', 'verified', 'verified', 'verified']),
                'average_rating' => $this->faker->randomFloat(2, 3.0, 5.0),
                'total_ratings' => $this->faker->numberBetween(0, 100),
                'active_projects_count' => $this->faker->numberBetween(0, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedEducation(): void
    {
        foreach ($this->talentProfileIds as $index => $talentProfileId) {
            $count = $this->faker->numberBetween(0, 3);
            for ($i = 0; $i < $count; $i++) {
                DB::table('education')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'talent_profile_id' => $talentProfileId,
                    'user_id' => $this->talentIds[$index],
                    'institution_name' => $this->faker->randomElement([
                        'Harvard University',
                        'Stanford University',
                        'MIT',
                        'Yale University',
                        'University of California',
                        'New York University',
                        'Columbia University',
                        'UCLA',
                    ]),
                    'degree' => $this->faker->randomElement(['Bachelor', 'Master', 'PhD', 'Associate', 'Certificate']),
                    'field_of_study' => $this->faker->randomElement([
                        'Computer Science',
                        'Design',
                        'Business',
                        'Engineering',
                        'Theater Arts',
                        'Fine Arts',
                        'Film Production',
                        'Communications',
                    ]),
                    'description' => $this->faker->paragraph(2),
                    'start_date' => $this->faker->date('Y-m-d', '-10 years'),
                    'end_date' => $this->faker->boolean(80) ? $this->faker->date('Y-m-d', '-1 year') : null,
                    'is_current' => $this->faker->boolean(20),
                    'grade' => $this->faker->randomElement(['3.5', '3.7', '3.9', '4.0', 'Pass', 'Distinction', null]),
                    'activities' => $this->faker->boolean(50) ? json_encode(array_map(fn() => $this->faker->words(3, true), range(1, $this->faker->numberBetween(1, 3)))) : null,
                    'certifications' => $this->faker->boolean(30) ? json_encode(array_map(fn() => [
                        'name' => $this->faker->words(3, true),
                        'year' => $this->faker->year,
                    ], range(1, $this->faker->numberBetween(1, 2)))) : null,
                    'institution_website' => $this->faker->boolean(60) ? $this->faker->url : null,
                    'attachments' => $this->faker->boolean(20) ? json_encode([$this->faker->url]) : null,
                    'order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedExperiences(): void
    {
        foreach ($this->talentProfileIds as $index => $talentProfileId) {
            $count = $this->faker->numberBetween(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $startDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
                $isCurrent = $this->faker->boolean(30);
                
                DB::table('experiences')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'talent_profile_id' => $talentProfileId,
                    'user_id' => $this->talentIds[$index],
                    'category_id' => $this->faker->randomElement($this->categoryIds),
                    'title' => $this->faker->jobTitle,
                    'company_name' => $this->faker->boolean(80) ? $this->faker->company : null,
                    'project_name' => $this->faker->boolean(50) ? $this->faker->catchPhrase : null,
                    'description' => $this->faker->paragraph(4),
                    'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $isCurrent ? null : $this->faker->dateTimeBetween($startDate, 'now')->format('Y-m-d'),
                    'is_current' => $isCurrent,
                    'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'freelance', 'internship']),
                    'skills_used' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(2, 6))),
                    'achievements' => json_encode(array_map(fn() => $this->faker->sentence(), range(1, $this->faker->numberBetween(2, 5)))),
                    'company_website' => $this->faker->boolean(50) ? $this->faker->url : null,
                    'compensation' => $this->faker->boolean(40) ? $this->faker->randomFloat(2, 50000, 200000) : null,
                    'compensation_type' => $this->faker->boolean(40) ? $this->faker->randomElement(['annual', 'hourly', 'project']) : null,
                    'media_attachments' => $this->faker->boolean(30) ? json_encode([$this->faker->url, $this->faker->url]) : null,
                    'is_featured' => $this->faker->boolean(20),
                    'order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedPortfolios(): void
    {
        foreach ($this->talentProfileIds as $index => $talentProfileId) {
            $count = $this->faker->numberBetween(2, 6);
            for ($i = 0; $i < $count; $i++) {
                $title = $this->faker->catchPhrase;
                
                DB::table('portfolios')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'talent_profile_id' => $talentProfileId,
                    'category_id' => $this->faker->randomElement($this->categoryIds),
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . $this->faker->randomNumber(4),
                    'description' => $this->faker->paragraph(3),
                    'project_type' => $this->faker->randomElement(['Commercial', 'Film', 'TV Show', 'Web Series', 'Music Video', 'Theater', 'Voice Over']),
                    'skills_demonstrated' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(2, 5))),
                    'project_url' => $this->faker->boolean(50) ? $this->faker->url : null,
                    'external_url' => $this->faker->boolean(40) ? $this->faker->url : null,
                    'completion_date' => $this->faker->date('Y-m-d', '-1 year'),
                    'client_name' => $this->faker->boolean(60) ? $this->faker->company : null,
                    'director_name' => $this->faker->boolean(50) ? $this->faker->name : null,
                    'role_description' => $this->faker->paragraph(2),
                    'challenges_faced' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
                    'collaborators' => $this->faker->boolean(60) ? json_encode(array_map(fn() => $this->faker->name, range(1, $this->faker->numberBetween(1, 4)))) : null,
                    'awards' => $this->faker->boolean(20) ? json_encode(array_map(fn() => [
                        'name' => $this->faker->words(3, true),
                        'year' => $this->faker->year,
                    ], range(1, $this->faker->numberBetween(1, 2)))) : null,
                    'is_featured' => $this->faker->boolean(20),
                    'is_public' => $this->faker->boolean(85),
                    'is_demo_reel' => $this->faker->boolean(15),
                    'views_count' => $this->faker->numberBetween(0, 500),
                    'likes_count' => $this->faker->numberBetween(0, 100),
                    'average_rating' => $this->faker->randomFloat(2, 3.0, 5.0),
                    'total_ratings' => $this->faker->numberBetween(0, 50),
                    'order' => $i,
                    'metadata' => $this->faker->boolean(20) ? json_encode(['duration' => $this->faker->numberBetween(30, 300)]) : null,
                    'embedding_model' => 'all-MiniLM-L6-v2',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedProjects(): void
    {
        foreach ($this->recruiterProfileIds as $index => $recruiterProfileId) {
            $count = $this->faker->numberBetween(2, 5);
            for ($i = 0; $i < $count; $i++) {
                $id = (string) Str::orderedUuid();
                $this->projectIds[] = $id;
                $title = $this->faker->catchPhrase . ' Project';
                
                DB::table('projects')->insert([
                    'id' => $id,
                    'recruiter_profile_id' => $recruiterProfileId,
                    'posted_by' => $this->recruiterIds[$index],
                    'primary_category_id' => $this->faker->randomElement($this->categoryIds),
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . $this->faker->randomNumber(4),
                    'description' => $this->faker->paragraph(5),
                    'requirements' => json_encode(array_map(fn() => $this->faker->sentence(), range(1, $this->faker->numberBetween(3, 6)))),
                    'responsibilities' => json_encode(array_map(fn() => $this->faker->sentence(), range(1, $this->faker->numberBetween(3, 5)))),
                    'deliverables' => json_encode(array_map(fn() => $this->faker->words(4, true), range(1, $this->faker->numberBetween(2, 4)))),
                    'project_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'freelance', 'temporary']),
                    'work_type' => $this->faker->randomElement(['remote', 'on_site', 'hybrid']),
                    'experience_level' => $this->faker->randomElement(['entry', 'intermediate', 'senior', 'expert']),
                    'skills_required' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(3, 8))),
                    'location' => json_encode([
                        'city' => $this->faker->city,
                        'state' => $this->faker->stateAbbr,
                        'country' => $this->faker->country,
                    ]),
                    'budget_min' => $this->faker->numberBetween(1000, 5000),
                    'budget_max' => $this->faker->numberBetween(10000, 50000),
                    'budget_currency' => 'USD',
                    'budget_type' => $this->faker->randomElement(['hourly', 'fixed', 'monthly']),
                    'budget_negotiable' => $this->faker->boolean(40),
                    'positions_available' => $this->faker->numberBetween(1, 5),
                    'application_deadline' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
                    'project_start_date' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
                    'project_end_date' => $this->faker->dateTimeBetween('+3 months', '+6 months')->format('Y-m-d'),
                    'duration' => $this->faker->randomElement(['1-3 months', '3-6 months', '6-12 months', '12+ months']),
                    'status' => $this->faker->randomElement(['draft', 'open', 'open', 'in_progress', 'completed', 'cancelled']),
                    'urgency' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
                    'visibility' => $this->faker->randomElement(['public', 'public', 'private', 'unlisted']),
                    'is_featured' => $this->faker->boolean(15),
                    'views_count' => $this->faker->numberBetween(0, 200),
                    'applications_count' => $this->faker->numberBetween(0, 50),
                    'application_questions' => $this->faker->boolean(50) ? json_encode(array_map(fn() => $this->faker->sentence() . '?', range(1, $this->faker->numberBetween(1, 3)))) : null,
                    'requires_portfolio' => $this->faker->boolean(60),
                    'requires_demo_reel' => $this->faker->boolean(30),
                    'attachments' => $this->faker->boolean(20) ? json_encode([$this->faker->url, $this->faker->url]) : null,
                    'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-2 months', 'now') : null,
                    'embedding_model' => 'all-MiniLM-L6-v2',
                    'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedCastingCalls(): void
    {
        foreach ($this->recruiterIds as $userId) {
            $count = $this->faker->numberBetween(1, 4);
            for ($i = 0; $i < $count; $i++) {
                $id = (string) Str::orderedUuid();
                $this->castingCallIds[] = $id;
                $roleName = $this->faker->randomElement(['Lead Actor', 'Supporting Role', 'Background Talent', 'Voice Actor', 'Stunt Performer', 'Narrator', 'Dancer']);
                
                DB::table('casting_calls')->insert([
                    'id' => $id,
                    'recruiter_id' => $userId,
                    'project_id' => $this->faker->randomElement($this->projectIds),
                    'title' => $roleName . ' - ' . $this->faker->words(2, true),
                    'description' => $this->faker->paragraph(5),
                    'role_name' => $roleName,
                    'role_type' => $this->faker->randomElement(['lead', 'supporting', 'extra', 'background']),
                    'gender_required' => $this->faker->randomElement(['male', 'female', 'any', null]),
                    'age_min' => $this->faker->numberBetween(18, 35),
                    'age_max' => $this->faker->numberBetween(40, 70),
                    'ethnicity_preferences' => $this->faker->boolean(40) ? json_encode($this->faker->randomElements(['Any', 'African', 'Asian', 'Caucasian', 'Hispanic', 'Middle Eastern'], $this->faker->numberBetween(1, 3))) : null,
                    'required_skills' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(2, 5))),
                    'audition_script' => $this->faker->boolean(50) ? $this->faker->paragraph(3) : null,
                    'audition_duration_seconds' => $this->faker->randomElement([30, 60, 90, 120, 180]),
                    'submission_requirements' => json_encode($this->faker->randomElements(['Headshot', 'Resume', 'Demo Reel', 'Voice Sample', 'Full Body Photo'], $this->faker->numberBetween(2, 4))),
                    'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'deadline' => $this->faker->dateTimeBetween('now', '+2 weeks'),
                    'audition_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
                    'audition_location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'is_remote_audition' => $this->faker->boolean(30),
                    'compensation_type' => $this->faker->randomElement(['paid', 'unpaid', 'deferred', 'credit_only', 'tbd']),
                    'rate_amount' => $this->faker->randomFloat(2, 50, 500),
                    'rate_currency' => 'AED',
                    'rate_period' => $this->faker->randomElement(['hourly', 'daily', 'weekly', 'project']),
                    'status' => $this->faker->randomElement(['draft', 'open', 'open', 'closed', 'filled']),
                    'visibility' => $this->faker->randomElement(['public', 'public', 'invited_only', 'private']),
                    'is_featured' => $this->faker->boolean(15),
                    'is_urgent' => $this->faker->boolean(20),
                    'views_count' => $this->faker->numberBetween(0, 500),
                    'applications_count' => $this->faker->numberBetween(0, 100),
                    'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedApplications(): void
    {
        $applicationCount = 100;
        $existingProjectTalent = [];
        $existingCastingTalent = [];
        $attempts = 0;
        $maxAttempts = $applicationCount * 5;
        
        for ($i = 0; $i < $applicationCount && $attempts < $maxAttempts; $attempts++) {
            $projectId = $this->faker->randomElement($this->projectIds);
            $talentId = $this->faker->randomElement($this->talentIds);
            $hasCastingCall = $this->faker->boolean(50);
            $castingCallId = $hasCastingCall ? $this->faker->randomElement($this->castingCallIds) : null;
            
            $projectTalentKey = "{$projectId}-{$talentId}";
            $castingTalentKey = $castingCallId ? "{$talentId}-{$castingCallId}" : null;
            
            // Skip if project-talent combination already exists
            if (isset($existingProjectTalent[$projectTalentKey])) {
                continue;
            }
            
            // Skip if casting-talent combination already exists
            if ($castingTalentKey && isset($existingCastingTalent[$castingTalentKey])) {
                continue;
            }
            
            // Mark combinations as used
            $existingProjectTalent[$projectTalentKey] = true;
            if ($castingTalentKey) {
                $existingCastingTalent[$castingTalentKey] = true;
            }
            
            DB::table('applications')->insert([
                'id' => (string) Str::orderedUuid(),
                'project_id' => $projectId,
                'talent_id' => $talentId,
                'casting_call_id' => $castingCallId,
                'cover_letter' => $this->faker->paragraph(4),
                'message' => $this->faker->paragraph(2),
                'pitch' => $this->faker->paragraph(3),
                'status' => $this->faker->randomElement(['pending', 'reviewing', 'shortlisted', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn']),
                'audition_status' => $this->faker->randomElement(['pending', 'under_review', 'shortlisted', 'callback', 'rejected', 'selected']),
                'proposed_rate' => $this->faker->randomFloat(2, 50, 200),
                'rate_type' => $this->faker->randomElement(['hourly', 'daily', 'project']),
                'currency' => 'AED',
                'available_from' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
                'available_until' => $this->faker->dateTimeBetween('+2 months', '+6 months')->format('Y-m-d'),
                'resume_url' => $this->faker->boolean(70) ? $this->faker->url : null,
                'audition_video_url' => $this->faker->boolean(30) ? $this->faker->url : null,
                'attachments' => $this->faker->boolean(40) ? json_encode([$this->faker->url, $this->faker->url]) : null,
                'portfolio_links' => $this->faker->boolean(50) ? json_encode([$this->faker->url]) : null,
                'recruiter_id' => $this->faker->randomElement($this->recruiterIds),
                'recruiter_notes' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
                'feedback_to_talent' => $this->faker->boolean(20) ? $this->faker->paragraph() : null,
                'rating' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 5) : null,
                'viewed_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'responded_at' => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('-2 weeks', 'now') : null,
                'reviewed_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'shortlisted_at' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-2 weeks', 'now') : null,
                'interview_scheduled_at' => $this->faker->boolean(20) ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
                'interview_date' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('now', '+2 weeks') : null,
                'interview_type' => $this->faker->boolean(30) ? $this->faker->randomElement(['in_person', 'video_call', 'phone']) : null,
                'interview_location' => $this->faker->boolean(20) ? $this->faker->address : null,
                'interview_notes' => $this->faker->boolean(15) ? $this->faker->paragraph() : null,
                'accepted_at' => $this->faker->boolean(10) ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
                'rejected_at' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
                'withdrawn_at' => $this->faker->boolean(5) ? $this->faker->dateTimeBetween('-2 weeks', 'now') : null,
                'withdrawn_by' => $this->faker->boolean(5) ? $this->faker->randomElement($this->talentIds) : null,
                'is_read' => $this->faker->boolean(60),
                'read_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'source' => $this->faker->randomElement(['web', 'mobile', 'api', 'referral']),
                'referral_code' => $this->faker->boolean(10) ? $this->faker->bothify('REF-####') : null,
                'metadata' => $this->faker->boolean(20) ? json_encode(['notes' => $this->faker->sentence()]) : null,
                'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
            
            $i++;
        }
        
        if ($i < $applicationCount) {
            $this->command->warn("  ⚠ Created {$i} applications (target was {$applicationCount} - some duplicates skipped)");
        }
    }
    
    private function seedMessages(): void
    {
        $messageCount = 200;
        
        for ($i = 0; $i < $messageCount; $i++) {
            $senderId = $this->faker->randomElement($this->userIds);
            $receiverPool = array_filter($this->userIds, fn($id) => $id !== $senderId);
            
            DB::table('messages')->insert([
                'id' => (string) Str::orderedUuid(),
                'sender_id' => $senderId,
                'recipient_id' => $this->faker->randomElement($receiverPool),
                'subject' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(3),
                'message_type' => $this->faker->randomElement(['direct', 'direct', 'application', 'project']),
                'project_id' => $this->faker->boolean(20) ? $this->faker->randomElement($this->projectIds) : null,
                'application_id' => null, // We'll leave this null for now since applications are seeded separately
                'parent_id' => $this->faker->boolean(15) ? null : null, // Keep null for simplicity
                'attachments' => $this->faker->boolean(20) ? json_encode([$this->faker->url]) : null,
                'is_read' => $this->faker->boolean(60),
                'read_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'is_important' => $this->faker->boolean(15),
                'is_archived' => $this->faker->boolean(25),
                'metadata' => $this->faker->boolean(10) ? json_encode(['context' => $this->faker->word]) : null,
                'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedReviews(): void
    {
        $reviewCount = 80;
        
        for ($i = 0; $i < $reviewCount; $i++) {
            $reviewerId = $this->faker->randomElement($this->recruiterIds);
            $revieweeId = $this->faker->randomElement($this->talentIds);
            
            $rating = $this->faker->randomFloat(1, 1.0, 5.0);
            
            DB::table('reviews')->insert([
                'id' => (string) Str::orderedUuid(),
                'reviewer_id' => $reviewerId,
                'reviewee_id' => $revieweeId,
                'project_id' => $this->faker->randomElement($this->projectIds),
                'application_id' => null, // Leave null for simplicity
                'rating' => $rating,
                'title' => $this->faker->sentence(6),
                'comment' => $this->faker->paragraph(4),
                'pros' => json_encode(array_map(fn() => $this->faker->sentence(), range(1, $this->faker->numberBetween(2, 4)))),
                'cons' => json_encode(array_map(fn() => $this->faker->sentence(), range(1, $this->faker->numberBetween(0, 2)))),
                'would_recommend' => $rating >= 3.5,
                'work_quality' => $this->faker->numberBetween(1, 5),
                'communication' => $this->faker->numberBetween(1, 5),
                'deadline_adherence' => $this->faker->numberBetween(1, 5),
                'professionalism' => $this->faker->numberBetween(1, 5),
                'is_public' => $this->faker->boolean(85),
                'is_featured' => $this->faker->boolean(10),
                'status' => $this->faker->randomElement(['published', 'published', 'published', 'pending', 'flagged']),
                'metadata' => $this->faker->boolean(15) ? json_encode(['verified' => true]) : null,
                'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedNotifications(): void
    {
        $notificationCount = 150;
        
        $types = [
            'application_received',
            'application_status_updated',
            'new_message',
            'profile_viewed',
            'casting_call_expiring',
            'new_review',
            'project_update',
            'interview_scheduled',
            'payment_received',
        ];
        
        for ($i = 0; $i < $notificationCount; $i++) {
            $type = $this->faker->randomElement($types);
            
            DB::table('notifications')->insert([
                'id' => (string) Str::orderedUuid(),
                'user_id' => $this->faker->randomElement($this->userIds),
                'type' => $type,
                'title' => $this->faker->sentence(6),
                'message' => $this->faker->paragraph(),
                'data' => json_encode([
                    'action' => $this->faker->word,
                    'reference_id' => (string) Str::uuid(),
                ]),
                'action_url' => $this->faker->boolean(70) ? $this->faker->url : null,
                'read_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'is_important' => $this->faker->boolean(20),
                'expires_at' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+1 week', '+1 month') : null,
                'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedMedia(): void
    {
        $mediaCount = 120;
        
        for ($i = 0; $i < $mediaCount; $i++) {
            $fileName = $this->faker->word . '.' . $this->faker->randomElement(['jpg', 'png', 'mp4', 'pdf']);
            
            DB::table('media')->insert([
                'id' => (string) Str::orderedUuid(),
                'model_type' => $this->faker->randomElement(['App\\Models\\TalentProfile', 'App\\Models\\Portfolio', 'App\\Models\\Experience']),
                'model_id' => $this->faker->randomElement($this->talentProfileIds),
                'uuid' => (string) Str::uuid(),
                'collection_name' => $this->faker->randomElement(['profile_images', 'portfolio', 'attachments', 'documents']),
                'name' => $this->faker->words(3, true),
                'file_name' => $fileName,
                'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'video/mp4', 'application/pdf']),
                'disk' => 'public',
                'conversions_disk' => 'public',
                'size' => $this->faker->numberBetween(100000, 10000000),
                'manipulations' => json_encode([]),
                'custom_properties' => json_encode([]),
                'generated_conversions' => json_encode([]),
                'responsive_images' => json_encode([]),
                'order_column' => $i % 10,
                'alt_text' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
                'caption' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
                'metadata' => $this->faker->boolean(20) ? json_encode(['uploaded_by' => $this->faker->name]) : null,
                'is_public' => $this->faker->boolean(70),
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedTalentSkills(): void
    {
        foreach ($this->talentProfileIds as $index => $talentProfileId) {
            $numberOfSkills = $this->faker->numberBetween(3, 10);
            $selectedSkills = $this->faker->randomElements($this->skillIds, $numberOfSkills);
            
            foreach ($selectedSkills as $i => $skillId) {
                DB::table('talent_skills')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'talent_profile_id' => $talentProfileId,
                    'skill_id' => $skillId,
                    'proficiency_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
                    'years_of_experience' => $this->faker->numberBetween(0, 15),
                    'is_primary' => $this->faker->boolean(30),
                    'description' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
                    'certifications' => $this->faker->boolean(30) ? json_encode(array_map(fn() => [
                        'name' => $this->faker->words(3, true),
                        'issuer' => $this->faker->company,
                        'date' => $this->faker->date(),
                    ], range(1, $this->faker->numberBetween(1, 2)))) : null,
                    'is_verified' => $this->faker->boolean(40),
                    'image_path' => null,
                    'video_url' => $this->faker->boolean(20) ? $this->faker->url : null,
                    'display_order' => $i,
                    'show_on_profile' => $this->faker->boolean(85),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}