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
        
        DB::statement('SET CONSTRAINTS ALL DEFERRED');
        
        foreach ($tablesToClear as $table) {
            DB::table($table)->truncate();
            $this->command->info("  ✓ Cleared {$table}");
        }
        
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
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
                'headline' => $this->faker->catchPhrase,
                'summary' => $this->faker->paragraph(5),
                'years_of_experience' => $this->faker->numberBetween(0, 20),
                'portfolio_url' => $this->faker->boolean(50) ? $this->faker->url : null,
                'resume_url' => $this->faker->boolean(50) ? $this->faker->url : null,
                'video_intro_url' => $this->faker->boolean(20) ? $this->faker->url : null,
                'certifications' => json_encode(array_map(function() {
                    return [
                        'name' => $this->faker->words(3, true),
                        'issuer' => $this->faker->company,
                        'date' => $this->faker->date(),
                    ];
                }, range(1, $this->faker->numberBetween(0, 3)))),
                'awards' => json_encode(array_map(function() {
                    return [
                        'title' => $this->faker->words(4, true),
                        'year' => $this->faker->year,
                    ];
                }, range(1, $this->faker->numberBetween(0, 2)))),
                'physical_attributes' => json_encode([
                    'height' => $this->faker->numberBetween(150, 200),
                    'weight' => $this->faker->numberBetween(50, 100),
                    'build' => $this->faker->randomElement(['slim', 'average', 'athletic', 'muscular']),
                ]),
                'is_featured' => $this->faker->boolean(10),
                'featured_until' => $this->faker->boolean(10) ? $this->faker->dateTimeBetween('now', '+3 months') : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedRecruiterProfiles(): void
    {
        foreach ($this->recruiterIds as $userId) {
            $id = (string) Str::orderedUuid();
            
            DB::table('recruiter_profiles')->insert([
                'id' => $id,
                'user_id' => $userId,
                'company_name' => $this->faker->company,
                'company_website' => $this->faker->url,
                'company_description' => $this->faker->paragraph(3),
                'company_logo' => null,
                'industry' => $this->faker->randomElement(['Film', 'Television', 'Theater', 'Advertising', 'Technology', 'Entertainment']),
                'company_size' => $this->faker->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
                'verification_status' => $this->faker->randomElement(['pending', 'verified', 'verified', 'verified']),
                'verified_at' => $this->faker->boolean(75) ? now() : null,
                'total_projects' => $this->faker->numberBetween(0, 50),
                'total_hires' => $this->faker->numberBetween(0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedEducation(): void
    {
        foreach ($this->talentIds as $userId) {
            $count = $this->faker->numberBetween(0, 3);
            for ($i = 0; $i < $count; $i++) {
                DB::table('education')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'user_id' => $userId,
                    'institution' => $this->faker->randomElement([
                        'Harvard University',
                        'Stanford University',
                        'MIT',
                        'Yale University',
                        'University of California',
                        'New York University',
                    ]),
                    'degree' => $this->faker->randomElement(['Bachelor', 'Master', 'PhD', 'Associate']),
                    'field_of_study' => $this->faker->randomElement([
                        'Computer Science',
                        'Design',
                        'Business',
                        'Engineering',
                        'Theater Arts',
                        'Fine Arts',
                    ]),
                    'start_date' => $this->faker->date('Y-m-d', '-10 years'),
                    'end_date' => $this->faker->boolean(80) ? $this->faker->date('Y-m-d', '-1 year') : null,
                    'is_current' => $this->faker->boolean(20),
                    'description' => $this->faker->paragraph(2),
                    'grade' => $this->faker->randomFloat(2, 2.5, 4.0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedExperiences(): void
    {
        foreach ($this->talentIds as $userId) {
            $count = $this->faker->numberBetween(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $startDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
                $isCurrent = $this->faker->boolean(30);
                
                DB::table('experiences')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'user_id' => $userId,
                    'company' => $this->faker->company,
                    'position' => $this->faker->jobTitle,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $isCurrent ? null : $this->faker->dateTimeBetween($startDate, 'now')->format('Y-m-d'),
                    'is_current' => $isCurrent,
                    'description' => $this->faker->paragraph(4),
                    'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'achievements' => json_encode(array_map(function() {
                        return $this->faker->sentence();
                    }, range(1, $this->faker->numberBetween(2, 5)))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedPortfolios(): void
    {
        foreach ($this->talentIds as $userId) {
            $count = $this->faker->numberBetween(2, 8);
            for ($i = 0; $i < $count; $i++) {
                DB::table('portfolios')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'user_id' => $userId,
                    'title' => $this->faker->catchPhrase,
                    'description' => $this->faker->paragraph(3),
                    'project_url' => $this->faker->url,
                    'thumbnail_url' => null,
                    'media_urls' => json_encode(array_map(function() {
                        return $this->faker->imageUrl();
                    }, range(1, $this->faker->numberBetween(1, 4)))),
                    'technologies' => json_encode($this->faker->randomElements([
                        'React', 'Vue', 'Laravel', 'Node.js', 'Python', 'AWS', 'Docker'
                    ], $this->faker->numberBetween(2, 5))),
                    'completion_date' => $this->faker->date('Y-m-d', '-1 year'),
                    'is_featured' => $this->faker->boolean(20),
                    'sort_order' => $i,
                    'views' => $this->faker->numberBetween(0, 500),
                    'likes' => $this->faker->numberBetween(0, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function seedProjects(): void
    {
        foreach ($this->recruiterIds as $userId) {
            $count = $this->faker->numberBetween(2, 6);
            for ($i = 0; $i < $count; $i++) {
                $id = (string) Str::orderedUuid();
                $this->projectIds[] = $id;
                
                DB::table('projects')->insert([
                    'id' => $id,
                    'user_id' => $userId,
                    'title' => $this->faker->catchPhrase . ' Project',
                    'description' => $this->faker->paragraph(5),
                    'budget_min' => $this->faker->numberBetween(1000, 5000),
                    'budget_max' => $this->faker->numberBetween(10000, 50000),
                    'currency' => 'USD',
                    'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
                    'end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
                    'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'is_remote' => $this->faker->boolean(40),
                    'status' => $this->faker->randomElement(['draft', 'open', 'open', 'in_progress', 'completed', 'cancelled']),
                    'visibility' => $this->faker->randomElement(['public', 'public', 'private', 'unlisted']),
                    'urgency' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
                    'required_skills' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(3, 8))),
                    'views' => $this->faker->numberBetween(0, 200),
                    'applications_count' => $this->faker->numberBetween(0, 50),
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
                
                DB::table('casting_calls')->insert([
                    'id' => $id,
                    'user_id' => $userId,
                    'project_id' => $this->faker->randomElement($this->projectIds),
                    'title' => $this->faker->randomElement(['Lead Actor', 'Supporting Role', 'Background Talent', 'Voice Actor', 'Stunt Performer']) . ' - ' . $this->faker->words(2, true),
                    'description' => $this->faker->paragraph(5),
                    'role_type' => $this->faker->randomElement(['lead', 'supporting', 'extra', 'background']),
                    'gender_requirement' => $this->faker->randomElement(['male', 'female', 'any', null]),
                    'age_range_min' => $this->faker->numberBetween(18, 30),
                    'age_range_max' => $this->faker->numberBetween(40, 70),
                    'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'is_remote' => $this->faker->boolean(20),
                    'compensation_type' => $this->faker->randomElement(['paid', 'unpaid', 'deferred', 'credit_only', 'tbd']),
                    'rate_min' => $this->faker->numberBetween(50, 200),
                    'rate_max' => $this->faker->numberBetween(300, 1000),
                    'rate_period' => $this->faker->randomElement(['hourly', 'daily', 'weekly', 'project']),
                    'currency' => 'USD',
                    'audition_date' => $this->faker->dateTimeBetween('now', '+1 month'),
                    'audition_location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
                    'shoot_date_start' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
                    'shoot_date_end' => $this->faker->dateTimeBetween('+4 months', '+6 months'),
                    'deadline' => $this->faker->dateTimeBetween('now', '+2 weeks'),
                    'status' => $this->faker->randomElement(['draft', 'open', 'open', 'closed', 'filled']),
                    'visibility' => $this->faker->randomElement(['public', 'public', 'invited_only', 'private']),
                    'required_skills' => json_encode($this->faker->randomElements($this->skillIds, $this->faker->numberBetween(2, 5))),
                    'views' => $this->faker->numberBetween(0, 500),
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
        
        for ($i = 0; $i < $applicationCount; $i++) {
            DB::table('applications')->insert([
                'id' => (string) Str::orderedUuid(),
                'casting_call_id' => $this->faker->randomElement($this->castingCallIds),
                'user_id' => $this->faker->randomElement($this->talentIds),
                'cover_letter' => $this->faker->paragraph(4),
                'resume_url' => $this->faker->url,
                'portfolio_url' => $this->faker->boolean(50) ? $this->faker->url : null,
                'video_url' => $this->faker->boolean(30) ? $this->faker->url : null,
                'status' => $this->faker->randomElement(['pending', 'reviewing', 'shortlisted', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn']),
                'audition_status' => $this->faker->randomElement(['pending', 'under_review', 'shortlisted', 'callback', 'rejected', 'selected']),
                'applied_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'reviewed_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'interview_date' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('now', '+2 weeks') : null,
                'interview_type' => $this->faker->boolean(30) ? $this->faker->randomElement(['in_person', 'video_call', 'phone']) : null,
                'interview_location' => $this->faker->boolean(20) ? $this->faker->address : null,
                'notes' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
                'rating' => $this->faker->boolean(40) ? $this->faker->numberBetween(1, 5) : null,
                'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
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
                'receiver_id' => $this->faker->randomElement($receiverPool),
                'subject' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(3),
                'is_read' => $this->faker->boolean(60),
                'read_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'parent_id' => $this->faker->boolean(20) ? null : null, // simplified for now
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
            
            DB::table('reviews')->insert([
                'id' => (string) Str::orderedUuid(),
                'reviewer_id' => $reviewerId,
                'reviewee_id' => $this->faker->randomElement($this->talentIds),
                'project_id' => $this->faker->randomElement($this->projectIds),
                'rating' => $this->faker->numberBetween(1, 5),
                'review' => $this->faker->paragraph(4),
                'response' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
                'is_featured' => $this->faker->boolean(10),
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
        ];
        
        for ($i = 0; $i < $notificationCount; $i++) {
            DB::table('notifications')->insert([
                'id' => (string) Str::orderedUuid(),
                'type' => $this->faker->randomElement($types),
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->faker->randomElement($this->userIds),
                'data' => json_encode([
                    'title' => $this->faker->sentence(),
                    'message' => $this->faker->paragraph(),
                    'action_url' => $this->faker->url,
                ]),
                'read_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
                'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedMedia(): void
    {
        $mediaCount = 120;
        
        for ($i = 0; $i < $mediaCount; $i++) {
            DB::table('media')->insert([
                'id' => (string) Str::orderedUuid(),
                'user_id' => $this->faker->randomElement($this->talentIds),
                'mediable_type' => $this->faker->randomElement(['App\\Models\\TalentProfile', 'App\\Models\\Portfolio', 'App\\Models\\Application']),
                'mediable_id' => (string) Str::orderedUuid(),
                'file_name' => $this->faker->word . '.' . $this->faker->randomElement(['jpg', 'png', 'mp4', 'pdf']),
                'file_path' => 'media/' . $this->faker->uuid . '/',
                'file_type' => $this->faker->randomElement(['image', 'video', 'document']),
                'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'video/mp4', 'application/pdf']),
                'file_size' => $this->faker->numberBetween(100000, 10000000),
                'title' => $this->faker->words(3, true),
                'description' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
                'is_public' => $this->faker->boolean(70),
                'sort_order' => $i % 10,
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function seedTalentSkills(): void
    {
        foreach ($this->talentIds as $talentId) {
            $numberOfSkills = $this->faker->numberBetween(3, 10);
            $selectedSkills = $this->faker->randomElements($this->skillIds, $numberOfSkills);
            
            foreach ($selectedSkills as $skillId) {
                DB::table('talent_skills')->insert([
                    'id' => (string) Str::orderedUuid(),
                    'user_id' => $talentId,
                    'skill_id' => $skillId,
                    'proficiency_level' => $this->faker->numberBetween(1, 5),
                    'years_of_experience' => $this->faker->numberBetween(0, 15),
                    'is_primary' => $this->faker->boolean(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}