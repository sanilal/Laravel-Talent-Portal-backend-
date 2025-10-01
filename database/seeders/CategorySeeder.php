<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development'],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design'],
            ['name' => 'Graphic Design', 'slug' => 'graphic-design'],
            ['name' => 'Content Writing', 'slug' => 'content-writing'],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing'],
            ['name' => 'Video Editing', 'slug' => 'video-editing'],
            ['name' => 'Data Science', 'slug' => 'data-science'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => Str::uuid(),
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['name'] . ' projects and talent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}