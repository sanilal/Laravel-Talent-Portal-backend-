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
        Schema::create('talent_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('talent_profile_id');
            $table->uuid('skill_id');
            $table->integer('proficiency_level'); // 1-5 scale
            $table->integer('years_of_experience')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->text('description')->nullable();
            $table->json('certifications')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('talent_profile_id')->references('id')->on('talent_profiles')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->unique(['talent_profile_id', 'skill_id']);
            $table->index(['proficiency_level', 'years_of_experience']);
            $table->index(['is_primary', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_skills');
    }
};
