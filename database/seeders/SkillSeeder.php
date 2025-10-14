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
        // Skills organized by category with icons and descriptions
        $skillsByCategory = [
            'Talent' => [
                ['name' => 'Acting', 'icon' => '🎭', 'description' => 'Professional acting for film, TV, and theater'],
                ['name' => 'Voice Over', 'icon' => '🎙️', 'description' => 'Voice-over work for commercials, animation, and audiobooks'],
                ['name' => 'Singing', 'icon' => '🎤', 'description' => 'Vocal performance across various genres'],
                ['name' => 'Dancing', 'icon' => '💃', 'description' => 'Professional dance performance and choreography'],
                ['name' => 'Modeling', 'icon' => '📸', 'description' => 'Fashion, commercial, and editorial modeling'],
                ['name' => 'Stand-up Comedy', 'icon' => '😄', 'description' => 'Comedy performance and entertainment'],
                ['name' => 'Mimicry', 'icon' => '🎭', 'description' => 'Voice and character impersonation'],
                ['name' => 'Stunts', 'icon' => '🤸', 'description' => 'Professional stunt performance and action choreography'],
                ['name' => 'Child Artist', 'icon' => '👶', 'description' => 'Child actors and performers'],
            ],
            'Technical' => [
                ['name' => 'Camera Operation', 'icon' => '📹', 'description' => 'Professional camera operation for film and video'],
                ['name' => 'Cinematography', 'icon' => '🎥', 'description' => 'Visual storytelling through camera work and lighting'],
                ['name' => 'Lighting', 'icon' => '💡', 'description' => 'Professional lighting setup and design'],
                ['name' => 'Sound Recording', 'icon' => '🔊', 'description' => 'Location sound recording and audio capture'],
                ['name' => 'Boom Operation', 'icon' => '🎤', 'description' => 'Boom microphone operation for film and TV'],
                ['name' => 'Gaffer', 'icon' => '💡', 'description' => 'Chief lighting technician'],
                ['name' => 'Grip', 'icon' => '🔧', 'description' => 'Camera support and rigging specialist'],
                ['name' => 'DIT (Digital Imaging Technician)', 'icon' => '💻', 'description' => 'Digital workflow and image management'],
            ],
            'Post-Production' => [
                ['name' => 'Video Editing', 'icon' => '✂️', 'description' => 'Video editing and post-production'],
                ['name' => 'Color Grading', 'icon' => '🎨', 'description' => 'Color correction and grading for film'],
                ['name' => 'VFX', 'icon' => '✨', 'description' => 'Visual effects creation and compositing'],
                ['name' => 'Motion Graphics', 'icon' => '🎬', 'description' => 'Animated graphics and titles'],
                ['name' => 'Sound Design', 'icon' => '🔊', 'description' => 'Audio design and effects creation'],
                ['name' => 'Sound Mixing', 'icon' => '🎚️', 'description' => 'Audio mixing and mastering'],
                ['name' => 'Foley Artist', 'icon' => '🎵', 'description' => 'Custom sound effects creation'],
                ['name' => 'Compositing', 'icon' => '🖼️', 'description' => 'Visual layer compositing and integration'],
                ['name' => 'Rotoscoping', 'icon' => '✏️', 'description' => 'Frame-by-frame video masking'],
                ['name' => '3D Animation', 'icon' => '🎮', 'description' => '3D modeling, rigging, and animation'],
                ['name' => '2D Animation', 'icon' => '🎨', 'description' => 'Traditional and digital 2D animation'],
            ],
            'Production' => [
                ['name' => 'Director', 'icon' => '🎬', 'description' => 'Film and TV direction'],
                ['name' => 'Assistant Director', 'icon' => '📋', 'description' => 'Production coordination and scheduling'],
                ['name' => 'Production Manager', 'icon' => '👔', 'description' => 'Production management and logistics'],
                ['name' => 'Line Producer', 'icon' => '💼', 'description' => 'Budget and resource management'],
                ['name' => 'Production Coordinator', 'icon' => '📊', 'description' => 'Production coordination and administration'],
                ['name' => 'Location Manager', 'icon' => '📍', 'description' => 'Location scouting and management'],
                ['name' => 'Unit Manager', 'icon' => '🏢', 'description' => 'Unit logistics and coordination'],
                ['name' => 'Production Assistant', 'icon' => '🎯', 'description' => 'General production support'],
            ],
            'Creative' => [
                ['name' => 'Scriptwriting', 'icon' => '✍️', 'description' => 'Screenplay and script development'],
                ['name' => 'Copywriting', 'icon' => '📝', 'description' => 'Creative writing for advertising'],
                ['name' => 'Art Direction', 'icon' => '🎨', 'description' => 'Visual style and design direction'],
                ['name' => 'Creative Direction', 'icon' => '💡', 'description' => 'Overall creative vision and strategy'],
                ['name' => 'Storyboarding', 'icon' => '🖼️', 'description' => 'Visual story planning and sketching'],
                ['name' => 'Concept Development', 'icon' => '💭', 'description' => 'Idea generation and concept creation'],
                ['name' => 'Brand Strategy', 'icon' => '🎯', 'description' => 'Brand positioning and strategy'],
            ],
            'Styling & Makeup' => [
                ['name' => 'Makeup Artist', 'icon' => '💄', 'description' => 'Professional makeup application'],
                ['name' => 'Hair Stylist', 'icon' => '💇', 'description' => 'Hair styling and design'],
                ['name' => 'Costume Designer', 'icon' => '👗', 'description' => 'Costume design and creation'],
                ['name' => 'Wardrobe Stylist', 'icon' => '👔', 'description' => 'Wardrobe selection and styling'],
                ['name' => 'Prosthetic Makeup', 'icon' => '🎭', 'description' => 'Prosthetic and special effects makeup'],
                ['name' => 'SFX Makeup', 'icon' => '🩸', 'description' => 'Special effects makeup'],
            ],
            'Equipment & Services' => [
                ['name' => 'Camera Rental', 'icon' => '📷', 'description' => 'Professional camera equipment rental'],
                ['name' => 'Lighting Equipment', 'icon' => '💡', 'description' => 'Lighting gear and equipment rental'],
                ['name' => 'Sound Equipment', 'icon' => '🔊', 'description' => 'Audio equipment rental'],
                ['name' => 'Grip Equipment', 'icon' => '🔧', 'description' => 'Grip and rigging equipment'],
                ['name' => 'Studio Rental', 'icon' => '🏢', 'description' => 'Studio space rental'],
                ['name' => 'Generator Services', 'icon' => '⚡', 'description' => 'Power generation services'],
                ['name' => 'Vehicle Rental', 'icon' => '🚗', 'description' => 'Production vehicle rental'],
                ['name' => 'Drone Operation', 'icon' => '🚁', 'description' => 'Aerial drone filming'],
            ],
            'Casting & Management' => [
                ['name' => 'Casting Director', 'icon' => '🎭', 'description' => 'Talent casting and selection'],
                ['name' => 'Talent Management', 'icon' => '👥', 'description' => 'Artist management and representation'],
                ['name' => 'Talent Coordination', 'icon' => '📋', 'description' => 'Talent scheduling and coordination'],
                ['name' => 'Extras Coordination', 'icon' => '👫', 'description' => 'Background talent management'],
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

            foreach ($skills as $skillData) {
                Skill::updateOrCreate(
                    ['slug' => Str::slug($skillData['name'])],
                    [
                        'name' => $skillData['name'],
                        'category_id' => $category->id,
                        'icon' => $skillData['icon'] ?? '⭐',
                        'description' => $skillData['description'] ?? $skillData['name'],
                        'is_active' => true,
                        'is_featured' => false,
                        'usage_count' => 0,
                        'talents_count' => 0, // New field for caching
                    ]
                );
            }
        }

        $this->command->info('✅ Skills seeded successfully with icons and descriptions!');
        $this->command->info('Total skills: ' . Skill::count());
        $this->command->info('Total categories: ' . Category::count());
    }
}