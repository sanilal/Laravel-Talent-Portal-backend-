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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            // CHANGED: Reference job_postings instead of jobs
            $table->foreignId('job_posting_id')->constrained('job_postings')->onDelete('cascade');
            $table->foreignId('talent_id')->constrained('users')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->json('answers')->nullable(); // Answers to application questions
            $table->json('attachments')->nullable(); // Resume, portfolio, etc.
            $table->enum('status', [
                'pending', 'reviewed', 'shortlisted', 'interview_scheduled', 
                'interviewed', 'offered', 'hired', 'rejected', 'withdrawn'
            ])->default('pending');
            $table->text('notes')->nullable(); // Employer notes
            $table->decimal('proposed_salary', 10, 2)->nullable();
            $table->string('availability_start')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // CHANGED: Update unique constraint
            $table->unique(['job_posting_id', 'talent_id']);
            $table->index(['status', 'created_at']);
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
