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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('benefits')->nullable();
            $table->string('job_type'); // full-time, part-time, contract, freelance, internship
            $table->string('work_type'); // remote, hybrid, onsite
            $table->string('experience_level'); // entry, junior, mid, senior, expert
            $table->string('category')->nullable();
            $table->json('skills_required')->nullable();
            $table->json('location')->nullable();
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->string('salary_period')->nullable(); // hourly, daily, weekly, monthly, yearly
            $table->boolean('salary_negotiable')->default(false);
            $table->integer('positions_available')->default(1);
            $table->date('application_deadline')->nullable();
            $table->date('start_date')->nullable();
            $table->string('duration')->nullable(); // for contracts
            $table->enum('status', ['draft', 'published', 'closed', 'expired', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->json('application_questions')->nullable();
            $table->boolean('requires_cover_letter')->default(false);
            $table->boolean('requires_portfolio')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['job_type', 'work_type']);
            $table->index(['category', 'experience_level']);
            $table->index(['salary_min', 'salary_max']);
            $table->index(['is_featured', 'is_urgent']);
            $table->index('application_deadline');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
