<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Skill;

class FilmIndustrySkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates film/advertising industry skills based on
     * the yourmoca.com structure with Artist, Crew, Vendor, and
     * Wedding Filmmaker categories.
     */
    public function run(): void
    {
        $this->command->info('Starting film industry skills seeding...');
        $this->command->info('');

        // Option to clear existing non-film skills
        if ($this->command->confirm('Do you want to DELETE existing skills first? (Recommended if you have developer skills)', true)) {
            $this->command->info('Deleting existing skills...');
            DB::table('talent_skills')->delete(); // Delete talent skills first (foreign key)
            DB::table('skills')->delete();
            $this->command->info('✓ Existing skills deleted');
            $this->command->info('');
        }

        // Get all subcategories with their categories
        $subcategories = Subcategory::with('category')->orderBy('category_id')->orderBy('sort_order')->get();

        if ($subcategories->isEmpty()) {
            $this->command->error('❌ No subcategories found!');
            $this->command->error('Please run: php artisan db:seed --class=CategoriesAndSubcategoriesSeeder');
            return;
        }

        $this->command->info("Found {$subcategories->count()} subcategories");
        $this->command->info('Creating skills...');
        $this->command->info('');

        $skillsCreated = 0;
        $currentCategory = null;

        foreach ($subcategories as $subcategory) {
            // Show category header when it changes
            if ($currentCategory !== $subcategory->category->name) {
                $currentCategory = $subcategory->category->name;
                $this->command->info("📁 {$currentCategory}");
            }

            // Create skill from subcategory
            $skill = Skill::create([
                'id' => Str::uuid()->toString(),
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
                'description' => $this->getSkillDescription($subcategory->name),
                'category_id' => $subcategory->category_id,
                'subcategory_id' => $subcategory->id,
                'is_active' => true,
                'is_featured' => $this->isFeatured($subcategory->name),
                'usage_count' => 0,
                'talents_count' => 0,
                'metadata' => json_encode([
                    'industry' => 'Film & Advertising',
                    'requires_portfolio' => $this->requiresPortfolio($subcategory->name),
                    'requires_reel' => $this->requiresReel($subcategory->name),
                ]),
            ]);

            $this->command->info("  ✓ {$subcategory->name}");
            $skillsCreated++;
        }

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✅ Film industry skills seeded successfully!');
        $this->command->info("Total skills created: {$skillsCreated}");
        $this->command->info('========================================');
        $this->command->info('');
        
        // Show statistics
        $this->showStatistics();
    }

    /**
     * Get appropriate description for skill
     */
    private function getSkillDescription(string $skillName): string
    {
        $descriptions = [
            // Artist category
            'Actor' => 'Professional acting talent for film, television, and commercial productions',
            'Actress' => 'Professional acting talent for film, television, and commercial productions',
            'Male Model' => 'Professional male modeling for fashion, advertising, and commercial shoots',
            'Female Model' => 'Professional female modeling for fashion, advertising, and commercial shoots',
            'Child Artist' => 'Young talent for film, television, and commercial productions',
            'Influencers' => 'Social media influencers for brand collaborations and marketing campaigns',
            'Male Singer' => 'Professional male vocalist for recordings, live performances, and voiceovers',
            'Female Singer' => 'Professional female vocalist for recordings, live performances, and voiceovers',

            // Crew category
            'Director' => 'Film and commercial director with creative vision and leadership',
            'Director of Photography' => 'Cinematographer specializing in visual storytelling and camera work',
            'Editor' => 'Post-production editor for film, video, and commercial content',
            'Spot Editor' => 'Commercial and advertisement editing specialist',
            'Colorist' => 'Color grading and color correction expert',
            'Focus Puller' => '1st AC - Focus pulling and camera assistant',
            'Vfx' => 'Visual effects artist and compositor',
            'Script Writer' => 'Screenplay and script writing for film and television',
            'Story Board' => 'Storyboard artist for pre-production visualization',
            'Photographer' => 'Professional photographer for stills, events, and productions',
            'Camera Assistant' => 'Camera department assistant and equipment handler',
            'Assistant Director' => 'AD - Production management and coordination',
            'Casting Director' => 'Talent casting and audition management',
            'Content Writer' => 'Content creation for digital media and marketing',
            'Art Director' => 'Visual design and artistic direction for productions',
            'Music Director' => 'Musical composition and direction',
            'Sync Sound' => 'Location sound recording and audio capture',
            'Sound Design' => 'Audio design and post-production sound',
            'Foley' => 'Foley artist for sound effects creation',
            'Aerial Cinematographer' => 'Drone and aerial photography specialist',
            'Makeup' => 'Professional makeup artist for film and photography',
            'Costume Designer' => 'Wardrobe and costume design for productions',
            'Voiceover' => 'Voice acting and narration services',
            'Dance Choreographer' => 'Dance choreography and movement direction',
            'Action Choreographer' => 'Stunt and action sequence choreography',
            'Producer' => 'Film and commercial production management',
            'Executive Producer' => 'Senior production oversight and financing',
            'Co-Producer' => 'Collaborative production management',
            'Line Producer' => 'Production budgeting and logistics',
            'Associate Producer' => 'Production coordination and assistance',
            'Creative Producer' => 'Creative development and production',
            'Production Controller' => 'Budget management and financial control',
            'Financier / Investor' => 'Production financing and investment',
            'Movie Promoter' => 'Film marketing and promotion',
            'Digital Marketing' => 'Digital marketing and social media management',
            'Location Manager' => 'Location scouting and management',
            'Ad Filmmaker' => 'Commercial and advertisement filmmaker',
            'Anchor' => 'On-camera host and presenter',
            'Associate Director' => 'Assistant director and creative support',
            'Camera Associate' => '2nd AC and camera department support',
            'Gaffer' => 'Chief lighting technician',
            'Film Critics' => 'Film review and criticism',
            'Film Distributor' => 'Film distribution and theatrical release',
            'Film unit' => 'Complete film production unit services',
            'Poster Design' => 'Poster and promotional artwork design',
            'Light Man' => 'Lighting technician and grip',
            'Boom Operator' => 'Boom mic operator and sound assistant',
            'Sound Mixing Engineer' => 'Audio mixing and mastering',
            'Music Programmer' => 'Electronic music programming and production',
            'Dancer' => 'Professional dancer for performances and productions',
            'Stuntman' => 'Stunt performer and action specialist',
            'Prosthetics' => 'Prosthetic makeup and special effects makeup',
            'Artist Management' => 'Talent management and representation',

            // Vendor category
            'Film Equipment' => 'Camera, lighting, and film equipment rental',
            'Studio' => 'Film studio and production space rental',
            'Location' => 'Location services and venue rental',
            'Mess ( Food )' => 'Catering and food services for productions',
            'Transportation' => 'Transportation and logistics services',
            'Caravan' => 'Mobile dressing room and caravan rental',
            'Ad Film Agency' => 'Full-service advertising and commercial agency',

            // Wedding Filmmaker category
            'Wedding Photographer' => 'Professional wedding photography services',
            'Wedding Videographer' => 'Cinematic wedding videography',
            'Wedding Makeup' => 'Bridal and wedding makeup services',
            'Wedding Costume Designer' => 'Wedding attire and styling',
            'Event Management' => 'Complete event planning and management',
        ];

        return $descriptions[$skillName] ?? "Professional {$skillName} services for film and advertising industry";
    }

    /**
     * Determine if skill should be featured
     */
    private function isFeatured(string $skillName): bool
    {
        $featured = [
            'Actor', 'Actress', 'Director', 'Director of Photography',
            'Editor', 'Photographer', 'Male Model', 'Female Model',
            'Wedding Photographer', 'Wedding Videographer'
        ];

        return in_array($skillName, $featured);
    }

    /**
     * Determine if skill requires portfolio
     */
    private function requiresPortfolio(string $skillName): bool
    {
        $portfolioRequired = [
            'Photographer', 'Editor', 'Colorist', 'Vfx', 'Poster Design',
            'Art Director', 'Costume Designer', 'Makeup', 'Prosthetics',
            'Story Board', 'Wedding Photographer'
        ];

        return in_array($skillName, $portfolioRequired);
    }

    /**
     * Determine if skill requires demo reel
     */
    private function requiresReel(string $skillName): bool
    {
        $reelRequired = [
            'Actor', 'Actress', 'Director', 'Director of Photography',
            'Editor', 'Spot Editor', 'Child Artist', 'Dancer',
            'Wedding Videographer', 'Ad Filmmaker'
        ];

        return in_array($skillName, $reelRequired);
    }

    /**
     * Show statistics after seeding
     */
    private function showStatistics(): void
    {
        $stats = [
            'total' => Skill::count(),
            'with_subcategory' => Skill::whereNotNull('subcategory_id')->count(),
            'featured' => Skill::where('is_featured', true)->count(),
            'by_category' => DB::table('skills')
                ->join('categories', 'skills.category_id', '=', 'categories.id')
                ->select('categories.name', DB::raw('count(*) as count'))
                ->groupBy('categories.name')
                ->get()
        ];

        $this->command->info('📊 Statistics:');
        $this->command->info('─────────────');
        $this->command->info("Total skills: {$stats['total']}");
        $this->command->info("With subcategories: {$stats['with_subcategory']}");
        $this->command->info("Featured skills: {$stats['featured']}");
        $this->command->info('');
        $this->command->info('Skills by category:');
        foreach ($stats['by_category'] as $category) {
            $this->command->info("  • {$category->name}: {$category->count}");
        }
        $this->command->info('');
    }
}