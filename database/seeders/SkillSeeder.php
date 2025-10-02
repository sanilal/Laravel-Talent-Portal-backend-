<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skillsByCategory = [
            'Talent' => [
                'Acting', 'Voice Over', 'Singing', 'Dancing', 'Modeling',
                'Stand-up Comedy', 'Mimicry', 'Stunts', 'Child Artist',
            ],
            'Technical' => [
                'Camera Operation', 'Cinematography', 'Lighting', 'Sound Recording',
                'Boom Operation', 'Gaffer', 'Grip', 'DIT (Digital Imaging Technician)',
            ],
            'Post-Production' => [
                'Video Editing', 'Color Grading', 'VFX', 'Motion Graphics',
                'Sound Design', 'Sound Mixing', 'Foley Artist', 'Compositing',
                'Rotoscoping', '3D Animation', '2D Animation',
            ],
            'Production' => [
                'Director', 'Assistant Director', 'Production Manager', 'Line Producer',
                'Production Coordinator', 'Location Manager', 'Unit Manager',
                'Production Assistant',
            ],
            'Creative' => [
                'Scriptwriting', 'Copywriting', 'Art Direction', 'Creative Direction',
                'Storyboarding', 'Concept Development', 'Brand Strategy',
            ],
            'Styling & Makeup' => [
                'Makeup Artist', 'Hair Stylist', 'Costume Designer', 'Wardrobe Stylist',
                'Prosthetic Makeup', 'SFX Makeup',
            ],
            'Equipment & Services' => [
                'Camera Rental', 'Lighting Equipment', 'Sound Equipment',
                'Grip Equipment', 'Studio Rental', 'Generator Services',
                'Vehicle Rental', 'Drone Operation',
            ],
            'Casting & Management' => [
                'Casting Director', 'Talent Management', 'Talent Coordination',
                'Extras Coordination',
            ],
        ];

        foreach ($skillsByCategory as $categoryName => $skills) {
            // Try to find or create category
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => $categoryName . ' related skills',
                ]
            );

            foreach ($skills as $skillName) {
                Skill::firstOrCreate(
                    ['slug' => Str::slug($skillName)],
                    [
                        'name' => $skillName,
                        'category_id' => $category->id,
                        'is_active' => true,
                        'is_featured' => false,
                        'usage_count' => 0,
                    ]
                );
            }
        }

        $this->command->info('Skills seeded successfully!');
    }
}