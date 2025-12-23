<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DATA MIGRATION: Physical Attributes from users → talent_skill_attributes (PROFILE LEVEL)
 * 
 * This migration is SAFE and NON-DESTRUCTIVE:
 * - Reads data from users table
 * - Writes to talent_skill_attributes table at PROFILE level
 * - Does NOT drop any columns
 * - Can be rolled back
 * 
 * Prerequisites:
 * - 2025_12_16_090000_add_profile_level_attributes.php must run first
 * - subcategory_attributes must have the 8 physical attributes defined
 * 
 * Strategy (Option 1A):
 * - Physical attributes attach to talent_profile_id (NOT skill-specific)
 * - talent_skill_id will be NULL for these attributes
 * - Works for ALL talents (even without skills)
 */
return new class extends Migration
{
    private $attributeMapping = [
        'height' => 'height',
        'weight' => 'weight',
        'chest' => 'bust_chest', // Note: DB uses bust_chest
        'waist' => 'waist',
        'hips' => 'hips',
        'shoe_size' => 'shoe_size',
        'hair_color' => 'hair_color',
        'eye_color' => 'eye_color',
    ];

    public function up(): void
    {
        $this->log("Starting physical attributes migration (Profile Level - Option 1A)...");
        
        // Step 1: Get all talents with physical attributes
        $talents = DB::table('users')
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

        $this->log("Found {$talents->count()} talents with physical attributes");

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($talents as $talent) {
            try {
                $result = $this->migrateTalentAttributes($talent);
                if ($result) {
                    $migrated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->log("Error migrating talent {$talent->id}: {$e->getMessage()}");
            }
        }

        $this->log("\nMigration Summary:");
        $this->log("- Successfully migrated: {$migrated}");
        $this->log("- Skipped (no profile): {$skipped}");
        $this->log("- Errors: {$errors}");
        $this->log("\n✅ Migration completed! Physical attributes now at PROFILE level.");
    }

    private function migrateTalentAttributes($talent): bool
    {
        // Get talent profile
        $talentProfile = DB::table('talent_profiles')
            ->where('user_id', $talent->id)
            ->first();

        if (!$talentProfile) {
            $this->log("Skipping talent {$talent->email} - no talent_profile found");
            return false;
        }

        $attributesMigrated = 0;

        // For each physical attribute in users table
        foreach ($this->attributeMapping as $userColumn => $attributeName) {
            $value = $talent->$userColumn;
            
            if ($value === null || $value === '') {
                continue;
            }

            // Find the subcategory_attribute for this attribute
            // Physical attributes can exist in multiple subcategories, so find ANY
            $attribute = DB::table('subcategory_attributes')
                ->where('field_name', $attributeName)
                ->first();

            if (!$attribute) {
                $this->log("Warning: No attribute definition found for {$attributeName}");
                continue;
            }

            // Check if this attribute value already exists AT PROFILE LEVEL
            $existing = DB::table('talent_skill_attributes')
                ->where('talent_profile_id', $talentProfile->id)
                ->where('attribute_id', $attribute->id)
                ->whereNull('talent_skill_id') // Profile-level attributes have NULL skill_id
                ->first();

            if ($existing) {
                $this->log("Attribute {$attributeName} already exists for {$talent->email}");
                continue;
            }

            // Convert/validate value if needed
            $convertedValue = $this->convertValue($userColumn, $value, $attribute);

            // Insert the attribute value at PROFILE LEVEL
            DB::table('talent_skill_attributes')->insert([
                'id' => Str::uuid()->toString(),
                'talent_profile_id' => $talentProfile->id, // ← Profile level
                'talent_skill_id' => null,                  // ← NULL for profile attributes
                'attribute_id' => $attribute->id,
                'value' => $convertedValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attributesMigrated++;
            $this->log("  ✓ {$attributeName}={$convertedValue}");
        }

        if ($attributesMigrated > 0) {
            $this->log("✅ Migrated {$attributesMigrated} attributes for {$talent->email}");
            return true;
        }

        return false;
    }

    /**
     * Convert old format to new format
     */
    private function convertValue(string $userColumn, $value, $attribute): string
    {
        // Convert based on attribute type
        switch ($userColumn) {
            case 'height':
                // Old format might be: "178", "5'10"", etc.
                // New format wants select values like "5_10"
                return $this->convertHeight($value);
                
            case 'weight':
                // Old format: "53", "70 kg", etc.
                // New format wants select values like "50_54"
                return $this->convertWeight($value);
                
            case 'hair_color':
            case 'eye_color':
                // Convert to lowercase for select options
                return strtolower(trim($value));
                
            case 'chest':
            case 'waist':
            case 'hips':
            case 'shoe_size':
                // These are numbers, just return as-is
                return (string) $value;
                
            default:
                return (string) $value;
        }
    }

    private function convertHeight($value): string
    {
        // Try to parse height and convert to select format
        $value = trim($value);
        
        // If it's already in feet format like "5'10"
        if (preg_match("/(\d+)'(\d+)/", $value, $matches)) {
            $feet = $matches[1];
            $inches = $matches[2];
            return "{$feet}_{$inches}";
        }
        
        // If it's just a number (assume cm), try to convert
        if (is_numeric($value)) {
            $cm = (int) $value;
            $totalInches = (int) ($cm / 2.54);
            $feet = (int) ($totalInches / 12);
            $inches = $totalInches % 12;
            
            // Validate range
            if ($feet >= 4 && $feet <= 6) {
                return "{$feet}_{$inches}";
            }
        }
        
        // Fallback: return as-is
        return $value;
    }

    private function convertWeight($value): string
    {
        $value = trim($value);
        
        // Extract numeric value
        preg_match('/(\d+)/', $value, $matches);
        if (!$matches) {
            return $value;
        }
        
        $weight = (int) $matches[1];
        
        // If weight seems to be in lbs, convert to kg
        if ($weight > 150) {
            $weight = (int) ($weight / 2.205);
        }
        
        // Find the range
        $rangeStart = (int) (floor($weight / 5) * 5);
        $rangeEnd = $rangeStart + 4;
        
        // Ensure within valid ranges (40-124)
        if ($rangeStart < 40) $rangeStart = 40;
        if ($rangeEnd > 124) $rangeEnd = 124;
        
        return "{$rangeStart}_{$rangeEnd}";
    }

    public function down(): void
    {
        $this->log("Rolling back physical attributes migration...");
        
        // Get all profile-level physical attributes
        $attributeIds = DB::table('subcategory_attributes')
            ->whereIn('field_name', [
                'height', 'weight', 'bust_chest', 'chest', 
                'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'
            ])
            ->pluck('id');

        $deleted = DB::table('talent_skill_attributes')
            ->whereIn('attribute_id', $attributeIds)
            ->whereNull('talent_skill_id') // Only delete profile-level ones
            ->delete();

        $this->log("Deleted {$deleted} profile-level attribute records");
        $this->log("Rollback complete - original data in users table unchanged");
    }

    private function log(string $message): void
    {
        echo "[" . now()->toDateTimeString() . "] {$message}\n";
    }
};