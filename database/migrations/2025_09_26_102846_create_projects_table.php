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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recruiter_profile_id');
            $table->uuid('posted_by'); // user_id of recruiter
            $table->uuid('primary_category_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('deliverables')->nullable();
            $table->string('project_type'); // casting, modeling, performance, etc.
            $table->string('work_type'); // remote, hybrid, onsite, travel
            $table->string('experience_level'); // entry, junior, mid, senior, expert
            $table->json('skills_required')->nullable();
            $table->json('location')->nullable();
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->string('budget_currency', 3)->default('USD');
            $table->string('budget_type')->nullable(); // fixed, hourly, daily, project
            $table->boolean('budget_negotiable')->default(false);
            $table->integer('positions_available')->default(1);
            $table->date('application_deadline')->nullable();
            $table->date('project_start_date')->nullable();
            $table->date('project_end_date')->nullable();
            $table->string('duration')->nullable();
            $table->enum('status', ['draft', 'published', 'in_progress', 'completed', 'cancelled', 'expired'])->default('draft');
            $table->enum('urgency', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->json('application_questions')->nullable();
            $table->boolean('requires_portfolio')->default(false);
            $table->boolean('requires_demo_reel')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recruiter_profile_id')->references('id')->on('recruiter_profiles')->onDelete('cascade');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('primary_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['status', 'published_at']);
            $table->index(['project_type', 'work_type']);
            $table->index(['primary_category_id', 'experience_level']);
            $table->index(['budget_min', 'budget_max']);
            $table->index(['is_featured', 'urgency']);
            $table->index('application_deadline');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
