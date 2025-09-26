<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reviewer_id');
            $table->uuidMorphs('reviewable'); // This creates the index automatically
            $table->uuid('project_id')->nullable();
            $table->integer('rating');
            $table->text('review_text')->nullable();
            $table->json('rating_categories')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('work_completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            // Remove this duplicate line: $table->index(['reviewable_type', 'reviewable_id']);
            $table->index(['rating', 'is_public']);
            $table->index(['is_verified', 'is_featured']);
            $table->index(['project_id', 'work_completed_at']);
            $table->fullText('review_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};