<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old table with wrong structure
        Schema::dropIfExists('reviews');
        
        // Create the new table with correct structure
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reviewer_id');
            $table->uuid('reviewee_id');
            $table->uuid('project_id')->nullable();
            $table->uuid('application_id')->nullable();
            $table->decimal('rating', 3, 1);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->boolean('would_recommend')->nullable();
            $table->integer('work_quality')->nullable();
            $table->integer('communication')->nullable();
            $table->integer('deadline_adherence')->nullable();
            $table->integer('professionalism')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('published');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('set null');
            
            $table->index(['reviewee_id', 'is_public']);
            $table->index(['reviewer_id', 'created_at']);
            $table->index(['rating', 'is_public']);
            $table->index(['status', 'is_featured']);
            $table->index(['project_id', 'application_id']);
            $table->unique(['reviewer_id', 'reviewee_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};