<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Talent',
                'description' => 'On-screen and performance talent including actors, models, voice artists, and performers',
            ],
            [
                'name' => 'Technical',
                'description' => 'Camera, lighting, sound, and other technical crew members',
            ],
            [
                'name' => 'Post-Production',
                'description' => 'Editing, VFX, color grading, sound design, and post-production services',
            ],
            [
                'name' => 'Production',
                'description' => 'Directors, producers, coordinators, and production management',
            ],
            [
                'name' => 'Creative',
                'description' => 'Creative direction, art direction, copywriting, and concept development',
            ],
            [
                'name' => 'Styling & Makeup',
                'description' => 'Makeup artists, hair stylists, costume designers, and wardrobe professionals',
            ],
            [
                'name' => 'Equipment & Services',
                'description' => 'Equipment rental services, studios, and production support services',
            ],
            [
                'name' => 'Casting & Management',
                'description' => 'Casting directors, talent agencies, and artist management services',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}