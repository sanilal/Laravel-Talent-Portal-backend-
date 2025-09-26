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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('talent_profile_id');
            $table->uuid('category_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->string('project_type')->nullable(); // film, commercial, theater, etc.
            $table->json('skills_demonstrated')->nullable();
            $table->string('project_url')->nullable();
            $table->string('external_url')->nullable(); // YouTube, Vimeo, etc.
            $table->date('completion_date')->nullable();
            $table->string('client_name')->nullable();
            $table->string('director_name')->nullable(); // For entertainment projects
            $table->text('role_description')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->json('collaborators')->nullable(); // Other people involved
            $table->json('awards')->nullable(); // Any awards received
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_demo_reel')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable(); // Additional project-specific data
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('talent_profile_id')->references('id')->on('talent_profiles')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->unique(['talent_profile_id', 'slug']);
            $table->index(['is_featured', 'is_public']);
            $table->index(['category_id', 'is_public']);
            $table->index(['talent_profile_id', 'order']);
            $table->index(['average_rating', 'total_ratings']);
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
