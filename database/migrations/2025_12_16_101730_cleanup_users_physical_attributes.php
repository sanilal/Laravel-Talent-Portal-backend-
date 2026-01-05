<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * CLEANUP MIGRATION: Drop physical attribute columns from users table
 * 
 * ⚠️ WARNING: This migration is DESTRUCTIVE!
 * 
 * Prerequisites MUST be met:
 * 1. ✅ Data migration completed (migrate_physical_attributes_data.php)
 * 2. ✅ Data integrity verified (all data successfully migrated)
 * 3. ✅ API updated to read from talent_skill_attributes
 * 4. ✅ Frontend updated to use new dynamic attributes
 * 5. ✅ Tested thoroughly on staging
 * 6. ✅ Monitoring period completed (recommended 1-2 weeks)
 * 7. ✅ Full database backup taken
 * 
 * This migration will:
 * - Drop 8 columns from users table
 * - Cannot be rolled back (data is gone)
 * - Should only be run after verification period
 * 
 * To run manually:
 * php artisan migrate --path=database/migrations/cleanup_users_physical_attributes.php
 */
return new class extends Migration
{
    private $columnsToRemove = [
        'height',
        'weight',
        'chest',
        'waist',
        'hips',
        'shoe_size',
        'hair_color',
        'eye_color',
    ];

    public function up(): void
    {
        $this->log("⚠️  STARTING DESTRUCTIVE CLEANUP MIGRATION");
        $this->log("This will DROP 8 columns from the users table");
        $this->log("");

        // Final safety check
        if (!$this->confirmPrerequisites()) {
            throw new \Exception("Prerequisites not met! Aborting migration. Please verify all checks pass.");
        }

        $this->log("\n✅ All prerequisite checks passed");
        $this->log("Proceeding with column removal...\n");

        Schema::table('users', function (Blueprint $table) {
            foreach ($this->columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                    $this->log("✅ Dropped column: {$column}");
                } else {
                    $this->log("⚠️  Column already removed: {$column}");
                }
            }
        });

        $this->log("\n🎉 Cleanup migration completed successfully!");
        $this->log("Users table is now clean and follows the dynamic attributes pattern");
    }

    private function confirmPrerequisites(): bool
    {
        $checks = [];
        
        // Check 1: Verify talent_skill_attributes has data
        $attributeCount = DB::table('talent_skill_attributes')
            ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
            ->whereIn('subcategory_attributes.field_name', [
                'height', 'weight', 'bust_chest', 'chest', 
                'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'
            ])
            ->count();

        if ($attributeCount > 0) {
            $this->log("✅ Check 1: talent_skill_attributes has {$attributeCount} physical attribute records");
            $checks[] = true;
        } else {
            $this->log("❌ Check 1 FAILED: No physical attributes found in talent_skill_attributes");
            $this->log("   Did you run the data migration first?");
            $checks[] = false;
        }

        // Check 2: Verify talents with attributes in users table also have them migrated
        $usersWithAttributes = DB::table('users')
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
            ->count();

        $migratedUsers = DB::table('users')
            ->join('talent_profiles', 'users.id', '=', 'talent_profiles.user_id')
            ->join('talent_skills', 'talent_profiles.id', '=', 'talent_skills.talent_profile_id')
            ->join('talent_skill_attributes', 'talent_skills.id', '=', 'talent_skill_attributes.talent_skill_id')
            ->join('subcategory_attributes', 'talent_skill_attributes.attribute_id', '=', 'subcategory_attributes.id')
            ->whereIn('subcategory_attributes.field_name', [
                'height', 'weight', 'bust_chest', 'chest', 
                'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'
            ])
            ->distinct('users.id')
            ->count('users.id');

        $percentage = $usersWithAttributes > 0 ? round(($migratedUsers / $usersWithAttributes) * 100, 2) : 0;

        if ($percentage >= 95) {
            $this->log("✅ Check 2: {$migratedUsers}/{$usersWithAttributes} talents migrated ({$percentage}%)");
            $checks[] = true;
        } else {
            $this->log("❌ Check 2 FAILED: Only {$migratedUsers}/{$usersWithAttributes} talents migrated ({$percentage}%)");
            $this->log("   Expected at least 95% migration rate");
            $checks[] = false;
        }

        // Check 3: Verify subcategory_attributes exist for all needed attributes
        $requiredAttributes = ['height', 'weight', 'bust_chest', 'waist', 'hips', 'shoe_size', 'hair_color', 'eye_color'];
        $foundAttributes = DB::table('subcategory_attributes')
            ->whereIn('field_name', $requiredAttributes)
            ->distinct('field_name')
            ->pluck('field_name')
            ->toArray();

        $missingAttributes = array_diff($requiredAttributes, $foundAttributes);

        if (empty($missingAttributes)) {
            $this->log("✅ Check 3: All required subcategory_attributes exist");
            $checks[] = true;
        } else {
            $this->log("❌ Check 3 FAILED: Missing subcategory_attributes: " . implode(', ', $missingAttributes));
            $checks[] = false;
        }

        // Check 4: Manual confirmation prompt
        $this->log("\n📋 Manual Checks Required:");
        $this->log("   - API controllers updated? (TalentProfileController, TalentSkillsController)");
        $this->log("   - Frontend updated? (profile forms, skill forms)");
        $this->log("   - Tested on staging?");
        $this->log("   - Monitoring period completed? (recommended 1-2 weeks)");
        $this->log("   - Full database backup taken?");
        
        // Since we can't prompt in migration, we'll check for an environment variable
        $manualConfirmation = env('CONFIRM_CLEANUP_MIGRATION', false);
        
        if ($manualConfirmation === true || $manualConfirmation === 'true' || $manualConfirmation === '1') {
            $this->log("✅ Check 4: Manual confirmation received via CONFIRM_CLEANUP_MIGRATION=true");
            $checks[] = true;
        } else {
            $this->log("❌ Check 4 FAILED: Manual confirmation not received");
            $this->log("   Set CONFIRM_CLEANUP_MIGRATION=true in .env to proceed");
            $checks[] = false;
        }

        // Return true only if all checks pass
        $allPassed = !in_array(false, $checks);
        
        if (!$allPassed) {
            $this->log("\n❌ PREREQUISITE CHECKS FAILED");
            $this->log("Please address the issues above before running this migration");
        }

        return $allPassed;
    }

    public function down(): void
    {
        $this->log("⚠️  ROLLBACK: Re-adding physical attribute columns to users table");
        $this->log("Note: DATA WILL NOT BE RESTORED automatically!");
        $this->log("You'll need to restore from backup if data is needed\n");

        Schema::table('users', function (Blueprint $table) {
            // Re-add the columns
            if (!Schema::hasColumn('users', 'height')) {
                $table->string('height')->nullable()->after('languages');
                $this->log("✅ Added column: height");
            }
            
            if (!Schema::hasColumn('users', 'weight')) {
                $table->string('weight')->nullable()->after('height');
                $this->log("✅ Added column: weight");
            }
            
            if (!Schema::hasColumn('users', 'chest')) {
                $table->string('chest')->nullable()->after('weight');
                $this->log("✅ Added column: chest");
            }
            
            if (!Schema::hasColumn('users', 'waist')) {
                $table->string('waist')->nullable()->after('chest');
                $this->log("✅ Added column: waist");
            }
            
            if (!Schema::hasColumn('users', 'hips')) {
                $table->string('hips')->nullable()->after('waist');
                $this->log("✅ Added column: hips");
            }
            
            if (!Schema::hasColumn('users', 'shoe_size')) {
                $table->string('shoe_size')->nullable()->after('hips');
                $this->log("✅ Added column: shoe_size");
            }
            
            if (!Schema::hasColumn('users', 'hair_color')) {
                $table->string('hair_color')->nullable()->after('shoe_size');
                $this->log("✅ Added column: hair_color");
            }
            
            if (!Schema::hasColumn('users', 'eye_color')) {
                $table->string('eye_color')->nullable()->after('hair_color');
                $this->log("✅ Added column: eye_color");
            }
        });

        $this->log("\n✅ Columns restored");
        $this->log("⚠️  Remember: You need to restore data from backup separately!");
    }

    private function log(string $message): void
    {
        echo "[" . now()->toDateTimeString() . "] {$message}\n";
    }
};