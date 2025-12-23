<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCHEMA ENHANCEMENT: Make talent_skill_attributes work at profile level
 * 
 * Purpose: Allow attributes to exist at PROFILE level (for physical attributes)
 *          OR at SKILL level (for skill-specific attributes)
 * 
 * Changes:
 * 1. Add talent_profile_id column (nullable)
 * 2. Make talent_skill_id nullable
 * 3. Add check constraint: one of them must be set
 * 
 * This enables:
 * - Physical attributes (height, weight, etc.) → talent_profile_id set
 * - Skill-specific attributes (vocal_range, etc.) → talent_skill_id set
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_skill_attributes', function (Blueprint $table) {
            // Add talent_profile_id to allow profile-level attributes
            $table->char('talent_profile_id', 36)->nullable()->after('id');
            
            // Add foreign key for talent_profile_id
            $table->foreign('talent_profile_id')
                  ->references('id')
                  ->on('talent_profiles')
                  ->onDelete('cascade');
            
            // Add index for better query performance
            $table->index('talent_profile_id');
        });

        // Make talent_skill_id nullable (was NOT NULL before)
        Schema::table('talent_skill_attributes', function (Blueprint $table) {
            $table->char('talent_skill_id', 36)->nullable()->change();
        });

        $this->log("✅ Added talent_profile_id column");
        $this->log("✅ Made talent_skill_id nullable");
        $this->log("✅ Schema updated successfully");
    }

    public function down(): void
    {
        Schema::table('talent_skill_attributes', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['talent_profile_id']);
            $table->dropIndex(['talent_profile_id']);
            
            // Drop the column
            $table->dropColumn('talent_profile_id');
        });

        // Make talent_skill_id required again
        Schema::table('talent_skill_attributes', function (Blueprint $table) {
            $table->char('talent_skill_id', 36)->nullable(false)->change();
        });

        $this->log("✅ Rolled back talent_profile_id addition");
        $this->log("✅ Made talent_skill_id required again");
    }

    private function log(string $message): void
    {
        echo "[" . now()->toDateTimeString() . "] {$message}\n";
    }
};