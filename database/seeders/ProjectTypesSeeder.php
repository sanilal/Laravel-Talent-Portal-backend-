<?php

namespace Database\Seeders;

use App\Models\ProjectType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed Film/TV Production Project Types
     */
    public function run(): void
    {
        $projectTypes = [
            [
                'name' => 'Feature Film',
                'description' => 'Full-length theatrical films and cinema productions',
                'icon' => '🎬',
                'sort_order' => 1,
            ],
            [
                'name' => 'Short Film',
                'description' => 'Short-form narrative or documentary films',
                'icon' => '🎞️',
                'sort_order' => 2,
            ],
            [
                'name' => 'TV Series',
                'description' => 'Television series and episodic content',
                'icon' => '📺',
                'sort_order' => 3,
            ],
            [
                'name' => 'TV Movie',
                'description' => 'Made-for-television film productions',
                'icon' => '📺',
                'sort_order' => 4,
            ],
            [
                'name' => 'Documentary',
                'description' => 'Non-fiction documentary productions',
                'icon' => '🎥',
                'sort_order' => 5,
            ],
            [
                'name' => 'Commercial / Ad',
                'description' => 'Television and online advertising content',
                'icon' => '📢',
                'sort_order' => 6,
            ],
            [
                'name' => 'Music Video',
                'description' => 'Music video productions',
                'icon' => '🎵',
                'sort_order' => 7,
            ],
            [
                'name' => 'Corporate Video',
                'description' => 'Corporate training and promotional videos',
                'icon' => '💼',
                'sort_order' => 8,
            ],
            [
                'name' => 'Web Series',
                'description' => 'Online streaming series and web content',
                'icon' => '🌐',
                'sort_order' => 9,
            ],
            [
                'name' => 'Animation',
                'description' => 'Animated films and series',
                'icon' => '🎨',
                'sort_order' => 10,
            ],
            [
                'name' => 'Reality TV',
                'description' => 'Reality television shows and competitions',
                'icon' => '🎪',
                'sort_order' => 11,
            ],
            [
                'name' => 'Live Event Coverage',
                'description' => 'Live event broadcasting and coverage',
                'icon' => '📹',
                'sort_order' => 12,
            ],
            [
                'name' => 'News Broadcast',
                'description' => 'News reporting and broadcast journalism',
                'icon' => '📰',
                'sort_order' => 13,
            ],
            [
                'name' => 'Talk Show',
                'description' => 'Interview and discussion programs',
                'icon' => '🎤',
                'sort_order' => 14,
            ],
            [
                'name' => 'Game Show',
                'description' => 'Game show and quiz programs',
                'icon' => '🎯',
                'sort_order' => 15,
            ],
            [
                'name' => 'Sports Broadcasting',
                'description' => 'Sports events and analysis programs',
                'icon' => '⚽',
                'sort_order' => 16,
            ],
            [
                'name' => 'Theater Production',
                'description' => 'Live theater and stage performances',
                'icon' => '🎭',
                'sort_order' => 17,
            ],
            [
                'name' => 'Podcast / Audio Drama',
                'description' => 'Audio content and podcast productions',
                'icon' => '🎙️',
                'sort_order' => 18,
            ],
            [
                'name' => 'Educational Content',
                'description' => 'Educational videos and training materials',
                'icon' => '📚',
                'sort_order' => 19,
            ],
            [
                'name' => 'Social Media Content',
                'description' => 'Short-form content for social platforms',
                'icon' => '📱',
                'sort_order' => 20,
            ],
        ];

        foreach ($projectTypes as $type) {
            ProjectType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'icon' => $type['icon'],
                    'is_active' => true,
                    'sort_order' => $type['sort_order'],
                ]
            );
        }

        $this->command->info('✅ Film/TV Project Types seeded successfully!');
        $this->command->info('Total project types: ' . ProjectType::count());
    }
}