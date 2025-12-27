<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration cleans up the casting_calls table by:
     * 1. Removing duplicate fields that exist in casting_call_requirements
     * 2. Migrating any existing data to the requirements table
     * 3. Creating a clean, scalable architecture
     */
    public function up(): void
    {
        // STEP 1: Migrate existing data from casting_calls to casting_call_requirements
        // Only for records that don't already have requirements
        
        $castingCalls = DB::table('casting_calls')
            ->whereNotNull('role_name') // Has old data
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('casting_call_requirements')
                    ->whereColumn('casting_call_requirements.casting_call_id', 'casting_calls.id');
            })
            ->get();
        
        foreach ($castingCalls as $call) {
            // Create a requirement record from the old casting_call data
            DB::table('casting_call_requirements')->insert([
                'id' => (string) Str::uuid(),
                'casting_call_id' => $call->id,
                'role_name' => $call->role_name ?? 'Role',
                'role_description' => null,
                'gender' => $this->mapGender($call->gender_required),
                'age_group' => $this->buildAgeGroup($call->age_min, $call->age_max),
                'skin_tone' => $call->ethnicity_preferences,
                'height' => null,
                'subcategory_id' => null,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // STEP 2: Add role_type to casting_call_requirements if it has value in casting_calls
        if (Schema::hasColumn('casting_calls', 'role_type')) {
            // First add the column
            Schema::table('casting_call_requirements', function (Blueprint $table) {
                $table->string('role_type')->nullable()->after('role_name');
            });
            
            // Then migrate the data
            $callsWithRoleType = DB::table('casting_calls')
                ->whereNotNull('role_type')
                ->get();
            
            foreach ($callsWithRoleType as $call) {
                DB::table('casting_call_requirements')
                    ->where('casting_call_id', $call->id)
                    ->update(['role_type' => $call->role_type]);
            }
        }
        
        // STEP 3: Handle required_skills
        // Add to requirements table for role-specific skills
        Schema::table('casting_call_requirements', function (Blueprint $table) {
            $table->json('required_skills')->nullable()->after('subcategory_id');
        });
        
        // Migrate required_skills data
        $callsWithSkills = DB::table('casting_calls')
            ->whereNotNull('required_skills')
            ->get();
        
        foreach ($callsWithSkills as $call) {
            DB::table('casting_call_requirements')
                ->where('casting_call_id', $call->id)
                ->update(['required_skills' => $call->required_skills]);
        }
        
        // STEP 4: Drop duplicate/legacy columns from casting_calls
        Schema::table('casting_calls', function (Blueprint $table) {
            // Role-specific fields (now in casting_call_requirements)
            $table->dropColumn([
                'role_name',           // Duplicate
                'role_type',           // Moved to requirements
                'gender_required',     // Duplicate (as 'gender' in requirements)
                'age_min',            // Duplicate (as 'age_group' in requirements)
                'age_max',            // Duplicate (as 'age_group' in requirements)
                'ethnicity_preferences', // Duplicate (as 'skin_tone' in requirements)
                'required_skills',    // Moved to requirements
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add columns back to casting_calls
        Schema::table('casting_calls', function (Blueprint $table) {
            $table->string('role_name')->nullable();
            $table->string('role_type')->nullable();
            $table->string('gender_required')->nullable();
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->text('ethnicity_preferences')->nullable();
            $table->json('required_skills')->nullable();
        });
        
        // Migrate data back from requirements to casting_calls (first requirement only)
        $requirements = DB::table('casting_call_requirements')
            ->where('display_order', 1)
            ->get();
        
        foreach ($requirements as $req) {
            DB::table('casting_calls')
                ->where('id', $req->casting_call_id)
                ->update([
                    'role_name' => $req->role_name,
                    'role_type' => $req->role_type ?? null,
                    'gender_required' => $req->gender,
                    'age_min' => null, // Can't reverse age_group to age_min/max
                    'age_max' => null,
                    'ethnicity_preferences' => $req->skin_tone,
                    'required_skills' => $req->required_skills,
                ]);
        }
        
        // Drop added columns from requirements
        Schema::table('casting_call_requirements', function (Blueprint $table) {
            $table->dropColumn(['role_type', 'required_skills']);
        });
    }
    
    /**
     * Helper: Map old gender_required to new gender format
     */
    private function mapGender($genderRequired)
    {
        if (empty($genderRequired)) return 'any';
        
        $gender = strtolower($genderRequired);
        if (in_array($gender, ['male', 'female', 'non-binary', 'any'])) {
            return $gender;
        }
        
        return 'any';
    }
    
    /**
     * Helper: Build age_group from age_min and age_max
     */
    private function buildAgeGroup($ageMin, $ageMax)
    {
        if (empty($ageMin) && empty($ageMax)) {
            return null;
        }
        
        if (!empty($ageMin) && !empty($ageMax)) {
            return "{$ageMin}-{$ageMax}";
        }
        
        if (!empty($ageMin)) {
            return "{$ageMin}+";
        }
        
        if (!empty($ageMax)) {
            return "Under {$ageMax}";
        }
        
        return null;
    }
};