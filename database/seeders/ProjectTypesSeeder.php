<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projectTypes = [
            ['name' => 'Feature Film', 'slug' => 'feature-film', 'description' => 'Full-length motion picture', 'sort_order' => 1],
            ['name' => 'Short Film', 'slug' => 'short-film', 'description' => 'Short-form film production', 'sort_order' => 2],
            ['name' => 'Music Video', 'slug' => 'music-video', 'description' => 'Music video production', 'sort_order' => 3],
            ['name' => 'Documentary Film', 'slug' => 'documentary-film', 'description' => 'Non-fiction documentary', 'sort_order' => 4],
            ['name' => 'Ad Film', 'slug' => 'ad-film', 'description' => 'Commercial advertisement', 'sort_order' => 5],
            ['name' => 'Corporate Video', 'slug' => 'corporate-video', 'description' => 'Corporate content production', 'sort_order' => 6],
            ['name' => 'Animation Film', 'slug' => 'animation-film', 'description' => 'Animated production', 'sort_order' => 7],
            ['name' => 'Educational Video', 'slug' => 'educational-video', 'description' => 'Educational content', 'sort_order' => 8],
            ['name' => 'Web Series', 'slug' => 'web-series', 'description' => 'Online series production', 'sort_order' => 9],
            ['name' => 'Event Coverage', 'slug' => 'event-coverage', 'description' => 'Event filming and coverage', 'sort_order' => 10],
            ['name' => 'Wedding Film', 'slug' => 'wedding-film', 'description' => 'Wedding cinematography', 'sort_order' => 11],
            ['name' => 'Travel Video', 'slug' => 'travel-video', 'description' => 'Travel content production', 'sort_order' => 12],
        ];

        foreach ($projectTypes as $type) {
            DB::table('project_types')->insert([
                'id' => DB::table('project_types')->max('id') + 1,
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => $type['description'],
                'icon' => null,
                'is_active' => true,
                'sort_order' => $type['sort_order'],
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Project types seeded successfully!');
    }
}