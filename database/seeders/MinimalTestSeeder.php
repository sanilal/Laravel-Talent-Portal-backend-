<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Skill;

class MinimalTestSeeder extends Seeder
{
    public function run(): void
    {
        echo "🌱 Creating minimal test data...\n\n";

        echo "Creating test users...\n";
        
        User::firstOrCreate(
            ['email' => 'talent1@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
            ]
        );
        echo "  ✓ Created talent1@example.com\n";

        User::firstOrCreate(
            ['email' => 'talent2@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'password' => bcrypt('Password123!'),
                'user_type' => 'talent',
                'email_verified_at' => now(),
            ]
        );
        echo "  ✓ Created talent2@example.com\n";

        User::firstOrCreate(
            ['email' => 'recruiter1@example.com'],
            [
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'password' => bcrypt('Password123!'),
                'user_type' => 'recruiter',
                'email_verified_at' => now(),
            ]
        );
        echo "  ✓ Created recruiter1@example.com\n";

        echo "\nCreating categories...\n";
        
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Web development skills'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'Mobile app development'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'Design and creative skills'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis and ML'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'DevOps and infrastructure'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
            echo "  ✓ Created category: {$category['name']}\n";
        }

        echo "\nCreating skills...\n";
        
        $webDevCategory = Category::where('slug', 'web-development')->first();
        $mobileDevCategory = Category::where('slug', 'mobile-development')->first();
        $designCategory = Category::where('slug', 'design')->first();

        $skills = [
            ['name' => 'PHP', 'slug' => 'php', 'category_id' => $webDevCategory->id],
            ['name' => 'Laravel', 'slug' => 'laravel', 'category_id' => $webDevCategory->id],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category_id' => $webDevCategory->id],
            ['name' => 'React', 'slug' => 'react', 'category_id' => $webDevCategory->id],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'category_id' => $webDevCategory->id],
            ['name' => 'Node.js', 'slug' => 'nodejs', 'category_id' => $webDevCategory->id],
            ['name' => 'React Native', 'slug' => 'react-native', 'category_id' => $mobileDevCategory->id],
            ['name' => 'Flutter', 'slug' => 'flutter', 'category_id' => $mobileDevCategory->id],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'category_id' => $designCategory->id],
            ['name' => 'Figma', 'slug' => 'figma', 'category_id' => $designCategory->id],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['slug' => $skill['slug']], $skill);
            echo "  ✓ Created skill: {$skill['name']}\n";
        }

        echo "\n✅ Minimal test data created successfully!\n\n";
        echo "Test Accounts Created:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Talent 1: talent1@example.com\n";
        echo "Talent 2: talent2@example.com\n";
        echo "Recruiter: recruiter1@example.com\n";
        echo "Password: Password123! (for all)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Categories: 5 created\n";
        echo "Skills: 10 created\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}