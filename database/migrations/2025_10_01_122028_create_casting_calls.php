<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casting_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recruiter_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            
            // Casting Call Details
            $table->string('title');
            $table->text('description');
            $table->string('role_name'); // Character name or role
            $table->enum('role_type', ['lead', 'supporting', 'extra', 'background']);
            
            // Requirements
            $table->string('gender_required')->nullable(); // 'male', 'female', 'any', 'non-binary'
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->json('ethnicity_preferences')->nullable(); // Array of preferences
            $table->json('required_skills')->nullable(); // ['acting', 'dancing', 'singing']
            
            // Audition Details
            $table->text('audition_script')->nullable(); // Self-tape script
            $table->integer('audition_duration_seconds')->nullable(); // Max duration
            $table->json('submission_requirements')->nullable(); // What to include in application
            
            // Logistics
            $table->string('location')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamp('audition_date')->nullable();
            $table->string('audition_location')->nullable();
            $table->boolean('is_remote_audition')->default(false);
            
            // Compensation
            $table->enum('compensation_type', ['paid', 'unpaid', 'deferred', 'credit_only', 'tbd']);
            $table->decimal('rate_amount', 10, 2)->nullable();
            $table->string('rate_currency', 3)->default('AED');
            $table->enum('rate_period', ['hourly', 'daily', 'weekly', 'project'])->nullable();
            
            // Status & Visibility
            $table->enum('status', ['draft', 'open', 'closed', 'filled', 'cancelled'])->default('draft');
            $table->enum('visibility', ['public', 'invited_only', 'private'])->default('public');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            
            // Analytics
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('recruiter_id');
            $table->index('project_id');
            $table->index('status');
            $table->index('deadline');
            $table->index(['status', 'visibility', 'deadline']);
            $table->fullText(['title', 'description', 'role_name']);
        });

        // Update applications table to support casting calls
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignUuid('casting_call_id')->nullable()->after('project_id')
                ->constrained('casting_calls')->onDelete('cascade');
            $table->string('audition_video_url')->nullable();
            // cover_letter already exists, skip it
            $table->enum('audition_status', [
                'pending', 'under_review', 'shortlisted', 
                'callback', 'rejected', 'selected'
            ])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['casting_call_id']);
            $table->dropColumn(['casting_call_id', 'audition_video_url', 'cover_letter', 'audition_status']);
        });
        
        Schema::dropIfExists('casting_calls');
    }
};