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

class CompleteTestSeeder extends Seeder
{
    /**
     * Run the complete test data seeder with proper schema.
     */
    public function run(): void
    {
        echo "🌱 Seeding complete test data...\n\n";

        // Create Categories
        echo "Creating categories...\n";
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Web development and programming', 'icon' => 'code', 'color' => '#3B82F6'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'Mobile app development', 'icon' => 'mobile', 'color' => '#8B5CF6'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'Design and creative work', 'icon' => 'palette', 'color' => '#EC4899'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis and machine learning', 'icon' => 'chart', 'color' => '#10B981'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'DevOps and infrastructure', 'icon' => 'server', 'color' => '#F59E0B'],
        ];

        foreach ($categories as $index => $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']], 
                array_merge($category, ['is_active' => true, 'sort_order' => $index + 1])
            );
            echo "  ✓ {$category['name']}\n";
        }

        // Create Skills
        echo "\nCreating skills...\n";
        $webDevCategory = Category::where('slug', 'web-development')->first();
        $mobileDevCategory = Category::where('slug', 'mobile-development')->first();
        $designCategory = Category::where('slug', 'design')->first();
        $dataCategory = Category::where('slug', 'data-science')->first();
        $devopsCategory = Category::where('slug', 'devops')->first();

        $skills = [
            // Web Development
            ['name' => 'PHP', 'slug' => 'php', 'category_id' => $webDevCategory->id, 'description' => 'PHP programming language'],
            ['name' => 'Laravel', 'slug' => 'laravel', 'category_id' => $webDevCategory->id, 'description' => 'Laravel PHP framework'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category_id' => $webDevCategory->id, 'description' => 'JavaScript programming'],
            ['name' => 'React', 'slug' => 'react', 'category_id' => $webDevCategory->id, 'description' => 'React library'],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'category_id' => $webDevCategory->id, 'description' => 'Vue.js framework'],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'category_id' => $webDevCategory->id, 'description' => 'Node.js runtime'],
            ['name' => 'Next.js', 'slug' => 'nextjs', 'category_id' => $webDevCategory->id, 'description' => 'Next.js framework'],
            ['name' => 'TypeScript', 'slug' => 'typescript', 'category_id' => $webDevCategory->id, 'description' => 'TypeScript language'],
            
            // Mobile Development
            ['name' => 'React Native', 'slug' => 'react-native', 'category_id' => $mobileDevCategory->id, 'description' => 'React Native framework'],
            ['name' => 'Flutter', 'slug' => 'flutter', 'category_id' => $mobileDevCategory->id, 'description' => 'Flutter framework'],
            ['name' => 'Swift', 'slug' => 'swift', 'category_id' => $mobileDevCategory->id, 'description' => 'Swift for iOS'],
            ['name' => 'Kotlin', 'slug' => 'kotlin', 'category_id' => $mobileDevCategory->id, 'description' => 'Kotlin for Android'],
            
            // Design
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'category_id' => $designCategory->id, 'description' => 'User interface and experience design'],
            ['name' => 'Figma', 'slug' => 'figma', 'category_id' => $designCategory->id, 'description' => 'Figma design tool'],
            ['name' => 'Adobe XD', 'slug' => 'adobe-xd', 'category_id' => $designCategory->id, 'description' => 'Adobe XD design tool'],
            ['name' => 'Photoshop', 'slug' => 'photoshop', 'category_id' => $designCategory->id, 'description' => 'Adobe Photoshop'],
            
            // Data Science
            ['name' => 'Python', 'slug' => 'python', 'category_id' => $dataCategory->id, 'description' => 'Python programming'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'category_id' => $dataCategory->id, 'description' => 'Machine learning'],
            ['name' => 'TensorFlow', 'slug' => 'tensorflow', 'category_id' => $dataCategory->id, 'description' => 'TensorFlow framework'],
            ['name' => 'Data Analysis', 'slug' => 'data-analysis', 'category_id' => $dataCategory->id, 'description' => 'Data analysis'],
            
            // DevOps
            ['name' => 'Docker', 'slug' => 'docker', 'category_id' => $devopsCategory->id, 'description' => 'Docker containerization'],
            ['name' => 'Kubernetes', 'slug' => 'kubernetes', 'category_id' => $devopsCategory->id, 'description' => 'Kubernetes orchestration'],
            ['name' => 'AWS', 'slug' => 'aws', 'category_id' => $devopsCategory->id, 'description' => 'Amazon Web Services'],
            ['name' => 'CI/CD', 'slug' => 'ci-cd', 'category_id' => $devopsCategory->id, 'description' => 'Continuous Integration/Deployment'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['slug' => $skill['slug']], 
                array_merge($skill, ['is_featured' => false, 'is_active' => true, 'usage_count' => 0, 'talents_count' => 0])
            );
        }
        echo "  ✓ Created 24 skills\n";

        // Create Talent Users with Full Profiles
        echo "\nCreating talent users...\n";

        // Talent 1: Full Stack Developer
        $talent1 = User::firstOrCreate(
            ['email' => 'talent1@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
                'phone' => '+1234567890',
                'bio' => 'Passionate full stack developer with extensive experience in building scalable web applications.',
                'location' => 'New York, USA',
                'professional_title' => 'Senior Full Stack Developer',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'United States',
                'hourly_rate' => 75.00,
                'currency' => 'USD',
                'experience_level' => 'expert',
                'availability_status' => 'available',
                'account_status' => 'active',
                'is_verified' => true,
                'is_email_verified' => true,
                'timezone' => 'America/New_York',
                'profile_views' => 125,
                'profile_completion' => 95,
                'languages' => json_encode(['English', 'Spanish']),
            ]
        );
        echo "  ✓ talent1@example.com (Full Stack Developer)\n";

        TalentProfile::firstOrCreate(
            ['user_id' => $talent1->id],
            [
                'primary_category_id' => $webDevCategory->id,
                'professional_title' => 'Senior Full Stack Developer',
                'summary' => 'Experienced full stack developer specializing in Laravel, React, and Node.js. I have successfully delivered 50+ projects for clients worldwide, focusing on clean code, scalability, and user experience.',
                'experience_level' => 'expert',
                'hourly_rate_min' => 65.00,
                'hourly_rate_max' => 85.00,
                'currency' => 'USD',
                'availability_types' => json_encode(['full_time', 'part_time', 'contract']),
                'is_available' => true,
                'is_public' => true,
                'is_featured' => false,
                'work_preferences' => json_encode(['remote' => true, 'on_site' => false, 'hybrid' => true]),
                'preferred_locations' => json_encode(['United States', 'Europe']),
                'notice_period' => '2 weeks',
                'languages' => json_encode(['English' => 'Native', 'Spanish' => 'Intermediate']),
                'profile_completion_percentage' => 95,
                'profile_views' => 125,
                'average_rating' => 4.8,
                'total_ratings' => 12,
            ]
        );

        // Add skills to talent1
        $talent1Skills = [
            ['skill' => 'php', 'level' => 'expert', 'years' => 7],
            ['skill' => 'laravel', 'level' => 'expert', 'years' => 5],
            ['skill' => 'javascript', 'level' => 'expert', 'years' => 6],
            ['skill' => 'react', 'level' => 'advanced', 'years' => 4],
            ['skill' => 'nodejs', 'level' => 'advanced', 'years' => 4],
        ];

        foreach ($talent1Skills as $index => $skillData) {
            $skill = Skill::where('slug', $skillData['skill'])->first();
            if ($skill) {
                TalentSkill::firstOrCreate(
                    ['user_id' => $talent1->id, 'skill_id' => $skill->id],
                    [
                        'proficiency_level' => $skillData['level'],
                        'years_of_experience' => $skillData['years'],
                        'is_primary' => $index === 0,
                        'display_order' => $index + 1,
                        'is_visible' => true,
                    ]
                );
            }
        }

        // Talent 2: UI/UX Designer
        $talent2 = User::firstOrCreate(
            ['email' => 'talent2@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
                'phone' => '+1234567891',
                'bio' => 'Creative UI/UX designer focused on user-centered design and beautiful interfaces.',
                'location' => 'San Francisco, USA',
                'professional_title' => 'Senior UI/UX Designer',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'United States',
                'hourly_rate' => 60.00,
                'currency' => 'USD',
                'experience_level' => 'advanced',
                'availability_status' => 'available',
                'account_status' => 'active',
                'is_verified' => true,
                'is_email_verified' => true,
                'timezone' => 'America/Los_Angeles',
                'profile_views' => 98,
                'profile_completion' => 90,
                'languages' => json_encode(['English']),
            ]
        );
        echo "  ✓ talent2@example.com (UI/UX Designer)\n";

        TalentProfile::firstOrCreate(
            ['user_id' => $talent2->id],
            [
                'primary_category_id' => $designCategory->id,
                'professional_title' => 'Senior UI/UX Designer',
                'summary' => 'Passionate about creating intuitive and beautiful user experiences. Specializing in mobile and web design with expertise in Figma, user research, and prototyping.',
                'experience_level' => 'advanced',
                'hourly_rate_min' => 50.00,
                'hourly_rate_max' => 70.00,
                'currency' => 'USD',
                'availability_types' => json_encode(['part_time', 'contract']),
                'is_available' => true,
                'is_public' => true,
                'is_featured' => false,
                'work_preferences' => json_encode(['remote' => true, 'on_site' => false, 'hybrid' => true]),
                'preferred_locations' => json_encode(['United States']),
                'notice_period' => '1 month',
                'languages' => json_encode(['English' => 'Native']),
                'profile_completion_percentage' => 90,
                'profile_views' => 98,
                'average_rating' => 4.9,
                'total_ratings' => 8,
            ]
        );

        // Add skills to talent2
        $talent2Skills = [
            ['skill' => 'ui-ux-design', 'level' => 'expert', 'years' => 6],
            ['skill' => 'figma', 'level' => 'expert', 'years' => 5],
            ['skill' => 'adobe-xd', 'level' => 'advanced', 'years' => 4],
            ['skill' => 'photoshop', 'level' => 'advanced', 'years' => 5],
        ];

        foreach ($talent2Skills as $index => $skillData) {
            $skill = Skill::where('slug', $skillData['skill'])->first();
            if ($skill) {
                TalentSkill::firstOrCreate(
                    ['user_id' => $talent2->id, 'skill_id' => $skill->id],
                    [
                        'proficiency_level' => $skillData['level'],
                        'years_of_experience' => $skillData['years'],
                        'is_primary' => $index === 0,
                        'display_order' => $index + 1,
                        'is_visible' => true,
                    ]
                );
            }
        }

        // Create Recruiter User
        echo "\nCreating recruiter user...\n";
        $recruiter1 = User::firstOrCreate(
            ['email' => 'recruiter1@example.com'],
            [
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'password' => bcrypt('Password123!'),
                'user_type' => 'recruiter',
                'email_verified_at' => now(),
                'phone' => '+1234567892',
                'bio' => 'Hiring manager at Tech Innovations Inc.',
                'location' => 'Austin, USA',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'United States',
                'account_status' => 'active',
                'is_verified' => true,
                'is_email_verified' => true,
                'timezone' => 'America/Chicago',
                'currency' => 'USD',
                'availability_status' => 'available',
                'profile_views' => 45,
                'profile_completion' => 85,
            ]
        );
        echo "  ✓ recruiter1@example.com (Tech Innovations Inc.)\n";

        RecruiterProfile::firstOrCreate(
            ['user_id' => $recruiter1->id],
            [
                'company_name' => 'Tech Innovations Inc.',
                'company_slug' => 'tech-innovations-inc',
                'company_description' => 'Leading technology company focused on innovative software solutions. We build cutting-edge products that transform businesses.',
                'industry' => 'Technology',
                'company_size' => '50-100',
                'company_website' => 'https://techinnovations.example.com',
                'company_email' => 'contact@techinnovations.example.com',
                'company_phone' => '+1234567893',
                'founded_year' => 2015,
                'company_type' => 'Private',
                'employee_count' => 75,
                'is_verified' => true,
                'is_featured' => false,
                'verification_status' => 'verified',
                'average_rating' => 4.5,
                'total_ratings' => 6,
                'active_projects_count' => 3,
            ]
        );

        // Create Projects
        echo "\nCreating projects...\n";
        
        $project1 = Project::firstOrCreate(
            ['slug' => 'e-commerce-website-development'],
            [
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $webDevCategory->id,
                'title' => 'E-commerce Website Development',
                'description' => 'We need an experienced full stack developer to build a modern e-commerce platform. The project includes building a Laravel backend API and React frontend with payment integration, inventory management, and admin dashboard.',
                'requirements' => json_encode([
                    '5+ years of experience with Laravel and React',
                    'Experience with payment gateway integration (Stripe, PayPal)',
                    'Strong understanding of REST API design',
                    'Experience with database optimization',
                ]),
                'responsibilities' => json_encode([
                    'Build and maintain backend API with Laravel',
                    'Develop responsive frontend with React',
                    'Integrate payment gateways',
                    'Implement admin dashboard',
                    'Write comprehensive tests',
                ]),
                'deliverables' => json_encode([
                    'Fully functional e-commerce platform',
                    'Admin dashboard',
                    'API documentation',
                    'Source code and deployment guide',
                ]),
                'project_type' => 'web_application',
                'work_type' => 'remote',
                'experience_level' => 'expert',
                'skills_required' => json_encode(['PHP', 'Laravel', 'React', 'JavaScript', 'MySQL']),
                'budget_min' => 8000,
                'budget_max' => 12000,
                'budget_currency' => 'USD',
                'budget_type' => 'fixed',
                'budget_negotiable' => true,
                'positions_available' => 1,
                'application_deadline' => now()->addMonths(1)->format('Y-m-d'),
                'duration' => '3-4 months',
                'status' => 'active',
                'urgency' => 'medium',
                'is_featured' => true,
                'views_count' => 156,
                'applications_count' => 8,
                'requires_portfolio' => true,
                'requires_demo_reel' => false,
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );
        echo "  ✓ E-commerce Website Development\n";

        $project2 = Project::firstOrCreate(
            ['slug' => 'mobile-app-ui-ux-redesign'],
            [
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $designCategory->id,
                'title' => 'Mobile App UI/UX Redesign',
                'description' => 'Looking for a talented UI/UX designer to redesign our existing mobile app. We want to improve user experience, modernize the interface, and increase user engagement.',
                'requirements' => json_encode([
                    'Strong portfolio showcasing mobile app designs',
                    'Experience with Figma',
                    'Understanding of iOS and Android design guidelines',
                    'User research and testing experience',
                ]),
                'responsibilities' => json_encode([
                    'Conduct user research and analysis',
                    'Create wireframes and prototypes',
                    'Design high-fidelity mockups',
                    'Collaborate with development team',
                    'Conduct usability testing',
                ]),
                'deliverables' => json_encode([
                    'Complete UI/UX design in Figma',
                    'Interactive prototype',
                    'Design system and guidelines',
                    'Asset export for developers',
                ]),
                'project_type' => 'mobile_app',
                'work_type' => 'remote',
                'experience_level' => 'advanced',
                'skills_required' => json_encode(['UI/UX Design', 'Figma', 'Mobile Design', 'User Research']),
                'budget_min' => 4000,
                'budget_max' => 6000,
                'budget_currency' => 'USD',
                'budget_type' => 'fixed',
                'budget_negotiable' => true,
                'positions_available' => 1,
                'application_deadline' => now()->addMonths(1)->format('Y-m-d'),
                'duration' => '1-2 months',
                'status' => 'active',
                'urgency' => 'high',
                'is_featured' => false,
                'views_count' => 89,
                'applications_count' => 12,
                'requires_portfolio' => true,
                'requires_demo_reel' => false,
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );
        echo "  ✓ Mobile App UI/UX Redesign\n";

        $project3 = Project::firstOrCreate(
            ['slug' => 'react-native-developer-needed'],
            [
                'recruiter_profile_id' => $recruiter1->recruiterProfile->id,
                'posted_by' => $recruiter1->id,
                'primary_category_id' => $mobileDevCategory->id,
                'title' => 'React Native Developer Needed',
                'description' => 'We are building a cross-platform mobile app and need an experienced React Native developer. The app will include real-time features, offline support, and integration with our existing API.',
                'requirements' => json_encode([
                    '3+ years of React Native experience',
                    'Experience with Redux and Context API',
                    'Knowledge of native iOS and Android',
                    'Experience with Firebase or similar',
                ]),
                'responsibilities' => json_encode([
                    'Develop cross-platform mobile application',
                    'Implement real-time features',
                    'Integrate with REST API',
                    'Optimize app performance',
                    'Submit to App Store and Google Play',
                ]),
                'deliverables' => json_encode([
                    'Fully functional mobile app',
                    'App Store and Google Play submissions',
                    'Technical documentation',
                    'Training for internal team',
                ]),
                'project_type' => 'mobile_app',
                'work_type' => 'remote',
                'experience_level' => 'intermediate',
                'skills_required' => json_encode(['React Native', 'JavaScript', 'Mobile Development', 'Firebase']),
                'budget_min' => 10000,
                'budget_max' => 15000,
                'budget_currency' => 'USD',
                'budget_type' => 'fixed',
                'budget_negotiable' => false,
                'positions_available' => 2,
                'application_deadline' => now()->addMonths(2)->format('Y-m-d'),
                'duration' => '4-6 months',
                'status' => 'active',
                'urgency' => 'medium',
                'is_featured' => false,
                'views_count' => 134,
                'applications_count' => 15,
                'requires_portfolio' => true,
                'requires_demo_reel' => false,
                'visibility' => 'public',
                'published_at' => now(),
            ]
        );
        echo "  ✓ React Native Developer Needed\n";

        echo "\n✅ Complete test data seeded successfully!\n\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Test Accounts Created (Password: Password123!):\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        echo "👨‍💻 Talent 1 - Full Stack Developer\n";
        echo "   Email: talent1@example.com\n";
        echo "   Profile: Senior Full Stack Developer\n";
        echo "   Skills: PHP, Laravel, JavaScript, React, Node.js\n";
        echo "   Rate: \$65-85/hour\n\n";
        
        echo "🎨 Talent 2 - UI/UX Designer\n";
        echo "   Email: talent2@example.com\n";
        echo "   Profile: Senior UI/UX Designer\n";
        echo "   Skills: UI/UX Design, Figma, Adobe XD, Photoshop\n";
        echo "   Rate: \$50-70/hour\n\n";
        
        echo "🏢 Recruiter - Tech Innovations Inc.\n";
        echo "   Email: recruiter1@example.com\n";
        echo "   Company: Tech Innovations Inc.\n";
        echo "   Active Projects: 3\n\n";
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Data Summary:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✓ Categories: 5\n";
        echo "✓ Skills: 24\n";
        echo "✓ Users: 3 (2 talents, 1 recruiter)\n";
        echo "✓ Talent Profiles: 2 (with complete details)\n";
        echo "✓ Recruiter Profiles: 1\n";
        echo "✓ Projects: 3 (all active and public)\n";
        echo "✓ Talent Skills: 9 skill assignments\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        echo "🎉 Your database is now fully seeded with realistic test data!\n\n";
        echo "Next Steps:\n";
        echo "1. Test login: http://localhost:3000/login\n";
        echo "2. Use: talent1@example.com / Password123!\n";
        echo "3. Browse projects, view profiles, test features\n\n";
    }
}