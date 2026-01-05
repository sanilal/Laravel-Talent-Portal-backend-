<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Action',
            'Ad Film',
            'Adventure',
            'Adventure Fiction',
            'Animation',
            'Biography',
            'Comedy',
            'Crime',
            'Documentary',
            'Drama',
            'Fantasy',
            'Fiction',
            'Historical',
            'Horror',
            'Musical',
            'Mystery',
            'Romance',
            'Sci-Fi (Science Fiction)',
            'Thriller',
            'War',
            'Western',
        ];

        $data = [];
        foreach ($genres as $index => $genre) {
            $data[] = [
                'id' => Str::uuid()->toString(),
                'name' => $genre,
                'slug' => Str::slug($genre),
                'is_active' => true,
                'display_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('genres')->insert($data);
        
        $this->command->info('Genres seeded successfully!');
    }
}