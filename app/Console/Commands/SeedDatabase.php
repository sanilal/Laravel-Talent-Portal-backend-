<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SeedDatabase extends Command
{
    protected $signature = 'db:seed-fresh 
                            {--confirm : Skip the confirmation prompt}
                            {--stats : Show database statistics after seeding}';
    
    protected $description = 'Clear existing data and seed the database with fresh fake data';

    public function handle()
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('⚠️  This will DELETE ALL existing data (except migrations). Continue?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('');
        $this->info('🚀 Starting database seeding...');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');

        $startTime = microtime(true);

        // Run the seeder
        Artisan::call('db:seed', [], $this->output);

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✨ Seeding completed in {$executionTime} seconds");
        $this->info('');

        if ($this->option('stats')) {
            $this->showStatistics();
        } else {
            $this->info('💡 Run with --stats to see database statistics');
        }

        $this->info('');
        $this->info('🔐 Login Credentials:');
        $this->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@talentsyouneed.com', 'password'],
                ['Talents', 'talent1@example.com - talent30@example.com', 'password'],
                ['Recruiters', 'recruiter1@example.com - recruiter15@example.com', 'password'],
            ]
        );

        return 0;
    }

    private function showStatistics(): void
    {
        $this->info('');
        $this->info('📊 Database Statistics:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');

        $tables = [
            'users',
            'talent_profiles',
            'recruiter_profiles',
            'categories',
            'skills',
            'education',
            'experiences',
            'portfolios',
            'projects',
            'casting_calls',
            'applications',
            'messages',
            'reviews',
            'notifications',
            'media',
            'talent_skills',
        ];

        $stats = [];
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $stats[] = [
                'Table' => $table,
                'Records' => number_format($count),
            ];
        }

        $this->table(['Table', 'Records'], $stats);

        // Additional insights
        $this->info('');
        $this->info('📈 Quick Insights:');
        
        $verifiedTalents = DB::table('users')
            ->where('user_type', 'talent')
            ->where('is_verified', true)
            ->count();
        
        $activeCastingCalls = DB::table('casting_calls')
            ->where('status', 'open')
            ->count();
        
        $pendingApplications = DB::table('applications')
            ->where('status', 'pending')
            ->count();

        $this->line("  • Verified Talents: {$verifiedTalents}");
        $this->line("  • Active Casting Calls: {$activeCastingCalls}");
        $this->line("  • Pending Applications: {$pendingApplications}");
    }
}