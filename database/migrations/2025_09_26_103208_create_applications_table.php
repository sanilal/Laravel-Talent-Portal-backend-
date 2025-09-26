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
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('talent_id');
            $table->uuid('talent_profile_id');
            $table->text('cover_letter')->nullable();
            $table->json('answers')->nullable(); // Answers to application questions
            $table->json('attachments')->nullable(); // Resume, portfolio, demo reel, etc.
            $table->decimal('proposed_rate', 10, 2)->nullable();
            $table->string('proposed_rate_type')->nullable(); // hourly, daily, fixed
            $table->text('proposed_timeline')->nullable();
            $table->enum('status', [
                'pending', 'reviewed', 'shortlisted', 'interview_scheduled', 
                'interviewed', 'offered', 'accepted', 'rejected', 'withdrawn', 'hired'
            ])->default('pending');
            $table->text('recruiter_notes')->nullable();
            $table->json('interview_details')->nullable();
            $table->decimal('offered_rate', 10, 2)->nullable();
            $table->json('contract_terms')->nullable();
            $table->string('availability_start')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('talent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('talent_profile_id')->references('id')->on('talent_profiles')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['project_id', 'talent_id']);
            $table->index(['status', 'created_at']);
            $table->index(['reviewed_at', 'status']);
            $table->index('responded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
