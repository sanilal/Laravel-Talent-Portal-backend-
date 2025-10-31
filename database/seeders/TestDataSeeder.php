<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TalentProfile;
use App\Models\RecruiterProfile;
use App\Models\Category;
use App\Models\Skill;
use App\Models\Project;
use App\Models\TalentSkill;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🌱 Seeding test data...\n\n";

        // Create Categories
        echo "Creating categories...\n";
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Web development skills'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'Mobile app development'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'Design and creative skills'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis and ML'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'DevOps and infrastructure'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Create Skills
        echo "Creating skills...\n";
        $webDevCategory = Category::where('slug', 'web-development')->first();
        $mobileDevCategory = Category::where('slug', 'mobile-development')->first();
        $designCategory = Category::where('slug', 'design')->first();
        $dataCategory = Category::where('slug', 'data-science')->first();
        $devopsCategory = Category::where('slug', 'devops')->first();

        $skills = [
            // Web Development
            ['name' => 'PHP', 'slug' => 'php', 'category_id' => $webDevCategory->id, 'description' => 'PHP programming'],
            ['name' => 'Laravel', 'slug' => 'laravel', 'category_id' => $webDevCategory->id, 'description' => 'Laravel framework'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category_id' => $webDevCategory->id, 'description' => 'JavaScript programming'],
            ['name' => 'React', 'slug' => 'react', 'category_id' => $webDevCategory->id, 'description' => 'React library'],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'category_id' => $webDevCategory->id, 'description' => 'Vue.js framework'],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'category_id' => $webDevCategory->id, 'description' => 'Node.js runtime'],
            ['name' => 'Next.js', 'slug' => 'nextjs', 'category_id' => $webDevCategory->id, 'description' => 'Next.js framework'],
            
            // Mobile Development
            ['name' => 'React Native', 'slug' => 'react-native', 'category_id' => $mobileDevCategory->id, 'description' => 'React Native'],
            ['name' => 'Flutter', 'slug' => 'flutter', 'category_id' => $mobileDevCategory->id, 'description' => 'Flutter framework'],
            ['name' => 'Swift', 'slug' => 'swift', 'category_id' => $mobileDevCategory->id, 'description' => 'Swift for iOS'],
            ['name' => 'Kotlin', 'slug' => 'kotlin', 'category_id' => $mobileDevCategory->id, 'description' => 'Kotlin for Android'],
            
            // Design
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'category_id' => $designCategory->id, 'description' => 'UI/UX design'],
            ['name' => 'Figma', 'slug' => 'figma', 'category_id' => $designCategory->id, 'description' => 'Figma tool'],
            ['name' => 'Adobe XD', 'slug' => 'adobe-xd', 'category_id' => $designCategory->id, 'description' => 'Adobe XD'],
            
            // Data Science
            ['name' => 'Python', 'slug' => 'python', 'category_id' => $dataCategory->id, 'description' => 'Python programming'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'category_id' => $dataCategory->id, 'description' => 'ML'],
            ['name' => 'TensorFlow', 'slug' => 'tensorflow', 'category_id' => $dataCategory->id, 'description' => 'TensorFlow'],
            
            // DevOps
            ['name' => 'Docker', 'slug' => 'docker', 'category_id' => $devopsCategory->id, 'description' => 'Docker'],
            ['name' => 'Kubernetes', 'slug' => 'kubernetes', 'category_id' => $devopsCategory->id, 'description' => 'Kubernetes'],
            ['name' => 'AWS', 'slug' => 'aws', 'category_id' => $devopsCategory->id, 'description' => 'Amazon Web Services'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['slug' => $skill['slug']], $skill);
        }

        // Create Test Users
        echo "Creating test users...\n";

        // Talent User 1
        $talent1 = User::firstOrCreate(
            ['email' => 'talent1@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
            ]
        );

        TalentProfile::firstOrCreate(
            ['user_id' => $talent1->id],
            [
                'title' => 'Full Stack Developer',
                'bio' => 'Experienced full stack developer with 5+ years of experience in web development.',
                'location' => 'New York, USA',
                'hourly_rate' => 75.00,
                'availability' => 'full_time',
                'profile_visibility' => 'public',
            ]
        );

        // Add skills to talent1
        $phpSkill = Skill::where('slug', 'php')->first();
        $laravelSkill = Skill::where('slug', 'laravel')->first();
        $reactSkill = Skill::where('slug', 'react')->first();

        TalentSkill::firstOrCreate(
            ['user_id' => $talent1->id, 'skill_id' => $phpSkill->id],
            ['proficiency_level' => 'expert', 'years_of_experience' => 5, 'is_primary' => true]
        );

        TalentSkill::firstOrCreate(
            ['user_id' => $talent1->id, 'skill_id' => $laravelSkill->id],
            ['proficiency_level' => 'expert', 'years_of_experience' => 4, 'is_primary' => false]
        );

        TalentSkill::firstOrCreate(
            ['user_id' => $talent1->id, 'skill_id' => $reactSkill->id],
            ['proficiency_level' => 'advanced', 'years_of_experience' => 3, 'is_primary' => false]
        );

        // Talent User 2
        $talent2 = User::firstOrCreate(
            ['email' => 'talent2@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
            ]
        );

        TalentProfile::firstOrCreate(
            ['user_id' => $talent2->id],
            [
                'title' => 'UI/UX Designer',
                'bio' => 'Creative designer passionate about user-centered design.',
                'location' => 'San Francisco, USA',
                'hourly_rate' => 60.00,
                'availability' => 'part_time',
                'profile_visibility' => 'public',
            ]
        );

        // Add skills to talent2
        $uiuxSkill = Skill::where('slug', 'ui-ux-design')->first();
        $figmaSkill = Skill::where('slug', 'figma')->first();

        TalentSkill::firstOrCreate(
            ['user_id' => $talent2->id, 'skill_id' => $uiuxSkill->id],
            ['proficiency_level' => 'expert', 'years_of_experience' => 6, 'is_primary' => true]
        );

        TalentSkill::firstOrCreate(
            ['user_id' => $talent2->id, 'skill_id' => $figmaSkill->id],
            ['proficiency_level' => 'expert', 'years_of_experience' => 4, 'is_primary' => false]
        );

        // Recruiter User 1
        $recruiter1 = User::firstOrCreate(
            ['email' => 'recruiter1@example.com'],
            [
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'password' => bcrypt('Password123!'),
                'user_type' => 'recruiter',
                'email_verified_at' => now(),
            ]
        );

        RecruiterProfile::firstOrCreate(
            ['user_id' => $recruiter1->id],
            [
                'company_name' => 'Tech Innovations Inc.',
                'company_website' => 'https://techinnovations.example.com',
                'company_size' => '50-100',
                'industry' => 'Technology',
                'bio' => 'Leading tech company looking for talented developers.',
            ]
        );

        // Create Projects
        echo "Creating projects...\n";

        Project::firstOrCreate(
            ['title' => 'E-commerce Website Development'],
            [
                'description' => 'We need a full stack developer to build a modern e-commerce platform with Laravel backend and React frontend.',
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $webDevCategory->id,
                'budget_type' => 'fixed',
                'budget_min' => 5000,
                'budget_max' => 10000,
                'budget_currency' => 'USD',
                'duration' => '2-3 months',
                'experience_level' => 'intermediate',
                'project_type' => 'remote',
                'status' => 'active',
                'visibility' => 'public',
            ]
        );

        Project::firstOrCreate(
            ['title' => 'Mobile App UI/UX Redesign'],
            [
                'description' => 'Looking for a talented UI/UX designer to redesign our mobile app interface.',
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $designCategory->id,
                'budget_type' => 'hourly',
                'budget_min' => 50,
                'budget_max' => 80,
                'budget_currency' => 'USD',
                'duration' => '1-2 months',
                'experience_level' => 'expert',
                'project_type' => 'remote',
                'status' => 'active',
                'visibility' => 'public',
            ]
        );

        Project::firstOrCreate(
            ['title' => 'React Native Developer Needed'],
            [
                'description' => 'We are building a cross-platform mobile app and need an experienced React Native developer.',
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $mobileDevCategory->id,
                'budget_type' => 'fixed',
                'budget_min' => 8000,
                'budget_max' => 15000,
                'budget_currency' => 'USD',
                'duration' => '3-6 months',
                'experience_level' => 'advanced',
                'project_type' => 'remote',
                'status' => 'active',
                'visibility' => 'public',
            ]
        );

        echo "\n✅ Test data seeded successfully!\n\n";
        echo "Test Accounts Created:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Talent 1:\n";
        echo "  Email: talent1@example.com\n";
        echo "  Password: Password123!\n";
        echo "  Role: Full Stack Developer\n\n";
        echo "Talent 2:\n";
        echo "  Email: talent2@example.com\n";
        echo "  Password: Password123!\n";
        echo "  Role: UI/UX Designer\n\n";
        echo "Recruiter 1:\n";
        echo "  Email: recruiter1@example.com\n";
        echo "  Password: Password123!\n";
        echo "  Company: Tech Innovations Inc.\n\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Categories: 5 created\n";
        echo "Skills: 20 created\n";
        echo "Projects: 3 created\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}