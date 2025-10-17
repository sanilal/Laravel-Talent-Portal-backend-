<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('talent_skills', function (Blueprint $table) {
            // Change proficiency_level from integer to string enum
            $table->string('proficiency_level', 20)->change();
            
            // Alternatively, use enum (PostgreSQL will create an enum type)
            // $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_skills', function (Blueprint $table) {
            // Change back to integer if needed
            $table->integer('proficiency_level')->nullable()->change();
        });
    }
};