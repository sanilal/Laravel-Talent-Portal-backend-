<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds JSON columns for storing embeddings (384-dimensional vectors)
     * This is a Windows-compatible alternative to pgvector
     */
    public function up(): void
    {
        // Talent Profiles - 3 embeddings for granular matching
        Schema::table('talent_profiles', function (Blueprint $table) {
            $table->json('profile_embedding')->nullable()->comment('384-dim vector for profile text');
            $table->json('skills_embedding')->nullable()->comment('384-dim vector for skills');
            $table->json('experience_embedding')->nullable()->comment('384-dim vector for experience');
            $table->timestamp('embeddings_generated_at')->nullable();
            $table->string('embedding_model', 100)->nullable()->default('all-MiniLM-L6-v2');
            
            $table->index('embeddings_generated_at');
        });

        // Projects - 2 embeddings for matching
        Schema::table('projects', function (Blueprint $table) {
            $table->json('requirements_embedding')->nullable()->comment('384-dim vector for requirements');
            $table->json('required_skills_embedding')->nullable()->comment('384-dim vector for required skills');
            $table->timestamp('embeddings_generated_at')->nullable();
            $table->string('embedding_model', 100)->nullable()->default('all-MiniLM-L6-v2');
            
            $table->index('embeddings_generated_at');
        });

        // Portfolios - 1 embedding for similarity search
        Schema::table('portfolios', function (Blueprint $table) {
            $table->json('description_embedding')->nullable()->comment('384-dim vector for description');
            $table->timestamp('embeddings_generated_at')->nullable();
            $table->string('embedding_model', 100)->nullable()->default('all-MiniLM-L6-v2');
            
            $table->index('embeddings_generated_at');
        });

        // Skills - 1 embedding for clustering/similarity
        Schema::table('skills', function (Blueprint $table) {
            $table->json('skill_embedding')->nullable()->comment('384-dim vector for skill name+description');
            $table->timestamp('embeddings_generated_at')->nullable();
            $table->string('embedding_model', 100)->nullable()->default('all-MiniLM-L6-v2');
            
            $table->index('embeddings_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'profile_embedding',
                'skills_embedding', 
                'experience_embedding',
                'embeddings_generated_at',
                'embedding_model'
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'requirements_embedding',
                'required_skills_embedding',
                'embeddings_generated_at',
                'embedding_model'
            ]);
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn([
                'description_embedding',
                'embeddings_generated_at',
                'embedding_model'
            ]);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn([
                'skill_embedding',
                'embeddings_generated_at',
                'embedding_model'
            ]);
        });
    }
};