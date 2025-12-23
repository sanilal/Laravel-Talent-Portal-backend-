<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VERIFICATION SCRIPT: Check Physical Attributes Migration
 * 
 * Run this script to verify the migration was successful:
 * php artisan db:seed --class=VerifyPhysicalAttributesMigration
 * 
 * This will:
 * - Compare data in users table vs talent_skill_attributes
 * - Report any discrepancies
 * - Provide migration success percentage
 * - Generate detailed report
 */
class VerifyPhysicalAttributesMigration extends Seeder
{
    private $report = [];
    
    public function run(): void
    {
        $this->line("\n" . str_repeat("=", 70));
        $this->line("PHYSICAL ATTRIBUTES MIGRATION VERIFICATION REPORT");
        $this->line(str_repeat("=", 70) . "\n");

        // Test 1: Count talents with physical attributes in users table
        $this->test1_countSourceData();

        // Test 2: Count migrated attributes in talent_skill_attributes
        $this->test2_countMigratedData();

        // Test 3: Sample verification - compare specific records
        $this->test3_sampleVerification();

        // Test 4: Check for missing migrations
        $this->test4_missingMigrations();

        // Test 5: Data format validation
        $this->test5_dataFormatValidation();

        // Print summary
        $this->printSummary();
    }

    private function test1_countSourceData(): void
    {
        $this->section("TEST 1: Source Data Analysis");

        $talents = DB::table('users')
            ->where('user_type', 'talent')
            ->get();

        $withAttributes = 0;
        $attributeBreakdown = [];

        foreach ($talents as $talent) {
            $hasAny = false;
            
            foreach (['height', 'weight', 'chest', 'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'] as $attr) {
                if ($talent->$attr !== null && $talent->$attr !== '') {
                    $hasAny = true;
                    $attributeBreakdown[$attr] = ($attributeBreakdown[$attr] ?? 0) + 1;
                }
            }
            
            if ($hasAny) {
                $withAttributes++;
            }
        }

        $this->info("Total talents: " . $talents->count());
        $this->info("Talents with physical attributes: {$withAttributes}");
        $this->line("\nAttribute breakdown:");
        foreach ($attributeBreakdown as $attr => $count) {
            $this->line("  - {$attr}: {$count} records");
        }

        $this->report['total_talents'] = $talents->count();
        $this->report['talents_with_attributes'] = $withAttributes;
        $this->report['attribute_breakdown'] = $attributeBreakdown;
    }

    private function test2_countMigratedData(): void
    {
        $this->section("\nTEST 2: Migrated Data Analysis");

        $migratedAttributes = DB::table('talent_skill_attributes')
            ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
            ->whereIn('subcategory_attributes.field_name', [
                'height', 'weight', 'bust_chest', 'chest', 
                'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'
            ])
            ->select('subcategory_attributes.field_name', DB::raw('COUNT(*) as count'))
            ->groupBy('subcategory_attributes.field_name')
            ->get();

        $totalMigrated = 0;
        $this->line("Migrated attributes:");
        foreach ($migratedAttributes as $attr) {
            $this->line("  - {$attr->field_name}: {$attr->count} records");
            $totalMigrated += $attr->count;
        }

        $this->info("\nTotal migrated attribute values: {$totalMigrated}");
        $this->report['total_migrated'] = $totalMigrated;
        $this->report['migrated_breakdown'] = $migratedAttributes->pluck('count', 'field_name')->toArray();
    }

    private function test3_sampleVerification(): void
    {
        $this->section("\nTEST 3: Sample Record Verification");

        // Get 5 random talents with attributes
        $samples = DB::table('users')
            ->where('user_type', 'talent')
            ->whereNotNull('height')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $matches = 0;
        $mismatches = 0;

        foreach ($samples as $sample) {
            $this->line("\nChecking: {$sample->email}");
            
            // Get talent profile and skills
            $talentProfile = DB::table('talent_profiles')->where('user_id', $sample->id)->first();
            if (!$talentProfile) {
                $this->warn("  ⚠️  No talent_profile found");
                $mismatches++;
                continue;
            }

            $talentSkill = DB::table('talent_skills')
                ->where('talent_profile_id', $talentProfile->id)
                ->first();
            
            if (!$talentSkill) {
                $this->warn("  ⚠️  No talent_skill found");
                $mismatches++;
                continue;
            }

            // Check each attribute
            $allMatch = true;
            foreach (['height', 'weight', 'hair_color', 'eye_color'] as $attr) {
                $oldValue = $sample->$attr;
                if ($oldValue === null) continue;

                // Find migrated value
                $attributeName = $attr === 'chest' ? 'bust_chest' : $attr;
                
                $attribute = DB::table('subcategory_attributes')
                    ->where('field_name', $attributeName)
                    ->first();

                if (!$attribute) continue;

                $migrated = DB::table('talent_skill_attributes')
                    ->where('talent_skill_id', $talentSkill->id)
                    ->where('attribute_id', $attribute->id)
                    ->first();

                if ($migrated) {
                    $this->line("  ✅ {$attr}: {$oldValue} → {$migrated->value}");
                } else {
                    $this->warn("  ❌ {$attr}: {$oldValue} → NOT MIGRATED");
                    $allMatch = false;
                }
            }

            if ($allMatch) {
                $matches++;
            } else {
                $mismatches++;
            }
        }

        $this->info("\nSample verification: {$matches} matches, {$mismatches} mismatches");
        $this->report['sample_matches'] = $matches;
        $this->report['sample_mismatches'] = $mismatches;
    }

    private function test4_missingMigrations(): void
    {
        $this->section("\nTEST 4: Missing Migrations Check");

        // Find talents with attributes that aren't migrated
        $talentsWithAttributes = DB::table('users')
            ->where('user_type', 'talent')
            ->where(function($query) {
                $query->whereNotNull('height')
                    ->orWhereNotNull('weight')
                    ->orWhereNotNull('chest')
                    ->orWhereNotNull('waist')
                    ->orWhereNotNull('hips')
                    ->orWhereNotNull('shoe_size')
                    ->orWhereNotNull('hair_color')
                    ->orWhereNotNull('eye_color');
            })
            ->get();

        $missing = [];
        
        foreach ($talentsWithAttributes as $talent) {
            $talentProfile = DB::table('talent_profiles')->where('user_id', $talent->id)->first();
            if (!$talentProfile) {
                $missing[] = ['email' => $talent->email, 'reason' => 'No talent_profile'];
                continue;
            }

            $talentSkill = DB::table('talent_skills')
                ->where('talent_profile_id', $talentProfile->id)
                ->first();
            
            if (!$talentSkill) {
                $missing[] = ['email' => $talent->email, 'reason' => 'No talent_skill'];
                continue;
            }

            // Check if ANY physical attributes were migrated
            $hasMigrated = DB::table('talent_skill_attributes')
                ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
                ->where('talent_skill_attributes.talent_skill_id', $talentSkill->id)
                ->whereIn('subcategory_attributes.field_name', [
                    'height', 'weight', 'bust_chest', 'chest', 
                    'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'
                ])
                ->exists();

            if (!$hasMigrated) {
                $missing[] = ['email' => $talent->email, 'reason' => 'Has skill but no migrated attributes'];
            }
        }

        if (empty($missing)) {
            $this->info("✅ No missing migrations found!");
        } else {
            $this->warn("⚠️  Found " . count($missing) . " talents with missing migrations:");
            foreach (array_slice($missing, 0, 10) as $item) {
                $this->line("  - {$item['email']}: {$item['reason']}");
            }
            if (count($missing) > 10) {
                $this->line("  ... and " . (count($missing) - 10) . " more");
            }
        }

        $this->report['missing_migrations'] = count($missing);
    }

    private function test5_dataFormatValidation(): void
    {
        $this->section("\nTEST 5: Data Format Validation");

        // Check if migrated values match expected formats
        $invalidFormats = [];

        // Height should be in format like "5_10" or numeric
        $heights = DB::table('talent_skill_attributes')
            ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
            ->where('subcategory_attributes.field_name', 'height')
            ->pluck('talent_skill_attributes.value');

        foreach ($heights as $height) {
            if (!preg_match('/^\d+_\d+$/', $height) && !is_numeric($height)) {
                $invalidFormats[] = ['attribute' => 'height', 'value' => $height];
            }
        }

        // Weight should be in format like "50_54" or numeric
        $weights = DB::table('talent_skill_attributes')
            ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
            ->where('subcategory_attributes.field_name', 'weight')
            ->pluck('talent_skill_attributes.value');

        foreach ($weights as $weight) {
            if (!preg_match('/^\d+_\d+$/', $weight) && !is_numeric($weight)) {
                $invalidFormats[] = ['attribute' => 'weight', 'value' => $weight];
            }
        }

        if (empty($invalidFormats)) {
            $this->info("✅ All data formats appear valid");
        } else {
            $this->warn("⚠️  Found " . count($invalidFormats) . " values with unexpected formats:");
            foreach (array_slice($invalidFormats, 0, 5) as $item) {
                $this->line("  - {$item['attribute']}: {$item['value']}");
            }
        }

        $this->report['invalid_formats'] = count($invalidFormats);
    }

    private function printSummary(): void
    {
        $this->section("\n" . str_repeat("=", 70));
        $this->line("MIGRATION SUCCESS SUMMARY");
        $this->line(str_repeat("=", 70));

        $sourceTalents = $this->report['talents_with_attributes'] ?? 0;
        $missing = $this->report['missing_migrations'] ?? 0;
        $migrated = $sourceTalents - $missing;
        $percentage = $sourceTalents > 0 ? round(($migrated / $sourceTalents) * 100, 2) : 0;

        $this->line("Total talents with physical attributes: {$sourceTalents}");
        $this->line("Successfully migrated: {$migrated}");
        $this->line("Missing migrations: {$missing}");
        $this->line("");
        
        if ($percentage >= 95) {
            $this->info("✅ MIGRATION SUCCESS: {$percentage}% migrated");
            $this->info("✅ Safe to proceed with cleanup migration");
        } elseif ($percentage >= 80) {
            $this->warn("⚠️  PARTIAL SUCCESS: {$percentage}% migrated");
            $this->warn("Review missing migrations before cleanup");
        } else {
            $this->error("❌ MIGRATION NEEDS ATTENTION: Only {$percentage}% migrated");
            $this->error("Do NOT run cleanup migration yet!");
        }

        $this->line("\n" . str_repeat("=", 70) . "\n");
    }

    private function section(string $title): void
    {
        $this->line($title);
        $this->line(str_repeat("-", 70));
    }

    private function line(string $message): void
    {
        echo $message . "\n";
    }

    private function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    private function warn(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }

    private function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }
}