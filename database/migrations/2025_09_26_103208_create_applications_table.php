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
        Schema::create('applications', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Foreign keys
            $table->uuid('project_id');
            $table->uuid('talent_id'); // References users.id where role = 'talent'
            
            // Application details
            $table->text('cover_letter')->nullable();
            $table->text('message')->nullable(); // Additional message to recruiter
            
            // Application status workflow
            $table->enum('status', [
                'pending',      // Initial submission
                'reviewing',    // Recruiter is reviewing
                'shortlisted',  // Moved to shortlist
                'interview',    // Interview scheduled
                'offered',      // Job offer extended
                'accepted',     // Offer accepted by talent
                'rejected',     // Rejected by recruiter
                'withdrawn'     // Withdrawn by talent
            ])->default('pending');
            
            // Pricing details (if talent provides custom rate)
            $table->decimal('proposed_rate', 10, 2)->nullable();
            $table->string('rate_type', 20)->nullable(); // 'hourly', 'daily', 'fixed'
            $table->string('currency', 3)->default('AED');
            
            // Availability details
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            
            // Attachments (resume, portfolio links)
            $table->json('attachments')->nullable(); // Array of file URLs or media IDs
            $table->json('portfolio_links')->nullable(); // External portfolio URLs
            
            // Recruiter feedback and notes
            $table->text('recruiter_notes')->nullable();
            $table->integer('rating')->nullable(); // 1-5 star rating
            
            // Tracking and analytics
            $table->timestamp('viewed_at')->nullable(); // When recruiter first viewed
            $table->timestamp('responded_at')->nullable(); // When recruiter responded
            $table->timestamp('withdrawn_at')->nullable(); // When talent withdrew
            $table->uuid('withdrawn_by')->nullable(); // Who withdrew (talent or recruiter)
            
            // Metadata for tracking
            $table->json('metadata')->nullable(); // Additional flexible data
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
                  
            $table->foreign('talent_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('withdrawn_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes for performance
            $table->index('project_id');
            $table->index('talent_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['project_id', 'status']); // Composite for recruiter dashboard
            $table->index(['talent_id', 'status']); // Composite for talent dashboard
            
            // Unique constraint: One application per talent per project
            $table->unique(['project_id', 'talent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};