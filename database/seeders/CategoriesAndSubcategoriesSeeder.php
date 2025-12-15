<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoriesAndSubcategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Based on yourmoca.com structure
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Artist',
                'slug' => 'artist',
                'description' => 'Actors, models, singers, and performers',
                'icon' => 'user-star',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'subcategories' => [
                    ['name' => 'Actor', 'slug' => 'actor', 'sort_order' => 1],
                    ['name' => 'Actress', 'slug' => 'actress', 'sort_order' => 2],
                    ['name' => 'Male Model', 'slug' => 'male-model', 'sort_order' => 3],
                    ['name' => 'Female Model', 'slug' => 'female-model', 'sort_order' => 4],
                    ['name' => 'Child Artist', 'slug' => 'child-artist', 'sort_order' => 5],
                    ['name' => 'Influencers', 'slug' => 'influencers', 'sort_order' => 6],
                    ['name' => 'Male Singer', 'slug' => 'male-singer', 'sort_order' => 7],
                    ['name' => 'Female Singer', 'slug' => 'female-singer', 'sort_order' => 8],
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Crew',
                'slug' => 'crew',
                'description' => 'Technical and creative crew members',
                'icon' => 'users',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'subcategories' => [
                    ['name' => 'Director', 'slug' => 'director', 'sort_order' => 1],
                    ['name' => 'Director of Photography', 'slug' => 'dop', 'sort_order' => 2],
                    ['name' => 'Editor', 'slug' => 'editor', 'sort_order' => 3],
                    ['name' => 'Spot Editor', 'slug' => 'spot-editor', 'sort_order' => 4],
                    ['name' => 'Colorist', 'slug' => 'colorist', 'sort_order' => 5],
                    ['name' => 'Focus Puller', 'slug' => 'focus-puller', 'sort_order' => 6],
                    ['name' => 'Vfx', 'slug' => 'vfx', 'sort_order' => 7],
                    ['name' => 'Script Writer', 'slug' => 'script-writer', 'sort_order' => 8],
                    ['name' => 'Story Board', 'slug' => 'story-board', 'sort_order' => 9],
                    ['name' => 'Photographer', 'slug' => 'photographer', 'sort_order' => 10],
                    ['name' => 'Camera Assistant', 'slug' => 'camera-assistant', 'sort_order' => 11],
                    ['name' => 'Assistant Director', 'slug' => 'assistant-director', 'sort_order' => 12],
                    ['name' => 'Casting Director', 'slug' => 'casting-director', 'sort_order' => 13],
                    ['name' => 'Content Writer', 'slug' => 'content-writer', 'sort_order' => 14],
                    ['name' => 'Art Director', 'slug' => 'art-director', 'sort_order' => 15],
                    ['name' => 'Music Director', 'slug' => 'music-director', 'sort_order' => 16],
                    ['name' => 'Sync Sound', 'slug' => 'sync-sound', 'sort_order' => 17],
                    ['name' => 'Sound Design', 'slug' => 'sound-design', 'sort_order' => 18],
                    ['name' => 'Foley', 'slug' => 'foley', 'sort_order' => 19],
                    ['name' => 'Aerial Cinematographer', 'slug' => 'aerial-cinematographer', 'sort_order' => 20],
                    ['name' => 'Makeup', 'slug' => 'makeup', 'sort_order' => 21],
                    ['name' => 'Costume Designer', 'slug' => 'costume-designer', 'sort_order' => 22],
                    ['name' => 'Voiceover', 'slug' => 'voiceover', 'sort_order' => 23],
                    ['name' => 'Dance Choreographer', 'slug' => 'dance-choreographer', 'sort_order' => 24],
                    ['name' => 'Action Choreographer', 'slug' => 'action-choreographer', 'sort_order' => 25],
                    ['name' => 'Producer', 'slug' => 'producer', 'sort_order' => 26],
                    ['name' => 'Executive Producer', 'slug' => 'executive-producer', 'sort_order' => 27],
                    ['name' => 'Co-Producer', 'slug' => 'co-producer', 'sort_order' => 28],
                    ['name' => 'Line Producer', 'slug' => 'line-producer', 'sort_order' => 29],
                    ['name' => 'Associate Producer', 'slug' => 'associate-producer', 'sort_order' => 30],
                    ['name' => 'Creative Producer', 'slug' => 'creative-producer', 'sort_order' => 31],
                    ['name' => 'Production Controller', 'slug' => 'production-controller', 'sort_order' => 32],
                    ['name' => 'Financier / Investor', 'slug' => 'financier-investor', 'sort_order' => 33],
                    ['name' => 'Movie Promoter', 'slug' => 'movie-promoter', 'sort_order' => 34],
                    ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'sort_order' => 35],
                    ['name' => 'Location Manager', 'slug' => 'location-manager', 'sort_order' => 36],
                    ['name' => 'Ad Filmmaker', 'slug' => 'ad-filmmaker', 'sort_order' => 37],
                    ['name' => 'Anchor', 'slug' => 'anchor', 'sort_order' => 38],
                    ['name' => 'Associate Director', 'slug' => 'associate-director', 'sort_order' => 39],
                    ['name' => 'Camera Associate', 'slug' => 'camera-associate', 'sort_order' => 40],
                    ['name' => 'Gaffer', 'slug' => 'gaffer', 'sort_order' => 41],
                    ['name' => 'Film Critics', 'slug' => 'film-critics', 'sort_order' => 42],
                    ['name' => 'Film Distributor', 'slug' => 'film-distributor', 'sort_order' => 43],
                    ['name' => 'Film unit', 'slug' => 'film-unit', 'sort_order' => 44],
                    ['name' => 'Poster Design', 'slug' => 'poster-design', 'sort_order' => 45],
                    ['name' => 'Light Man', 'slug' => 'light-man', 'sort_order' => 46],
                    ['name' => 'Boom Operator', 'slug' => 'boom-operator', 'sort_order' => 47],
                    ['name' => 'Sound Mixing Engineer', 'slug' => 'sound-mixing-engineer', 'sort_order' => 48],
                    ['name' => 'Music Programmer', 'slug' => 'music-programmer', 'sort_order' => 49],
                    ['name' => 'Dancer', 'slug' => 'dancer', 'sort_order' => 50],
                    ['name' => 'Stuntman', 'slug' => 'stuntman', 'sort_order' => 51],
                    ['name' => 'Prosthetics', 'slug' => 'prosthetics', 'sort_order' => 52],
                    ['name' => 'Artist Management', 'slug' => 'artist-management', 'sort_order' => 53],
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Vendor',
                'slug' => 'vendor',
                'description' => 'Equipment, locations, and services',
                'icon' => 'building',
                'color' => '#95E1D3',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
                'subcategories' => [
                    ['name' => 'Film Equipment', 'slug' => 'film-equipment', 'sort_order' => 1],
                    ['name' => 'Studio', 'slug' => 'studio', 'sort_order' => 2],
                    ['name' => 'Location', 'slug' => 'location', 'sort_order' => 3],
                    ['name' => 'Mess ( Food )', 'slug' => 'mess-food', 'sort_order' => 4],
                    ['name' => 'Transportation', 'slug' => 'transportation', 'sort_order' => 5],
                    ['name' => 'Caravan', 'slug' => 'caravan', 'sort_order' => 6],
                    ['name' => 'Ad Film Agency', 'slug' => 'ad-film-agency', 'sort_order' => 7],
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Wedding Filmmaker',
                'slug' => 'wedding-filmmaker',
                'description' => 'Wedding and event professionals',
                'icon' => 'heart',
                'color' => '#F38181',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
                'subcategories' => [
                    ['name' => 'Wedding Photographer', 'slug' => 'wedding-photographer', 'sort_order' => 1],
                    ['name' => 'Wedding Videographer', 'slug' => 'wedding-videographer', 'sort_order' => 2],
                    ['name' => 'Wedding Makeup', 'slug' => 'wedding-makeup', 'sort_order' => 3],
                    ['name' => 'Wedding Costume Designer', 'slug' => 'wedding-costume-designer', 'sort_order' => 4],
                    ['name' => 'Event Management', 'slug' => 'event-management', 'sort_order' => 5],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            // Insert category
            DB::table('categories')->insert($categoryData);

            // Insert subcategories
            foreach ($subcategories as $sub) {
                DB::table('subcategories')->insert([
                    'id' => Str::uuid()->toString(),
                    'category_id' => $categoryData['id'],
                    'name' => $sub['name'],
                    'slug' => $sub['slug'],
                    'description' => null,
                    'icon' => null,
                    'color' => null,
                    'is_active' => true,
                    'sort_order' => $sub['sort_order'],
                    'metadata' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Categories and subcategories seeded successfully!');
    }
}