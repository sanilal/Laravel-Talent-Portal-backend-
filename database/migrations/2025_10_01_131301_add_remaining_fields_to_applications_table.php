<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Add recruiter_id for denormalized queries
            $table->foreignUuid('recruiter_id')->nullable()->after('talent_id')
                  ->constrained('users')->onDelete('cascade');
            
            // Add pitch (short intro separate from cover_letter)
            $table->text('pitch')->nullable()->after('message');
            
            // Add resume URL
            $table->string('resume_url')->nullable()->after('audition_video_url');
            
            // Add public feedback (separate from private recruiter_notes)
            $table->text('feedback_to_talent')->nullable()->after('recruiter_notes');
            
            // Add interview/callback fields
            $table->timestamp('interview_date')->nullable()->after('responded_at');
            $table->string('interview_location')->nullable()->after('interview_date');
            $table->enum('interview_type', ['in_person', 'video_call', 'phone'])->nullable()->after('interview_location');
            $table->text('interview_notes')->nullable()->after('interview_type');
            
            // Add timeline tracking
            $table->timestamp('reviewed_at')->nullable()->after('viewed_at');
            $table->timestamp('shortlisted_at')->nullable()->after('reviewed_at');
            $table->timestamp('interview_scheduled_at')->nullable()->after('shortlisted_at');
            $table->timestamp('accepted_at')->nullable()->after('interview_scheduled_at');
            $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            
            // Add read status
            $table->boolean('is_read')->default(false)->after('rating');
            $table->timestamp('read_at')->nullable()->after('is_read');
            
            // Add source tracking
            $table->string('source')->nullable()->after('metadata');
            $table->string('referral_code')->nullable()->after('source');
            
            // Add indexes
            $table->index('recruiter_id');
            $table->index('is_read');
            $table->index(['status', 'created_at']);
            $table->index(['recruiter_id', 'status']);
        });

        // Backfill recruiter_id from projects
        DB::statement('
            UPDATE applications 
            SET recruiter_id = projects.posted_by 
            FROM projects 
            WHERE applications.project_id = projects.id 
            AND applications.recruiter_id IS NULL
        ');
        
        // Add unique constraints
        DB::statement('
            CREATE UNIQUE INDEX unique_talent_casting_call 
            ON applications(talent_id, casting_call_id) 
            WHERE casting_call_id IS NOT NULL AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS unique_talent_casting_call');
        
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['recruiter_id']);
            $table->dropIndex(['is_read']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['recruiter_id', 'status']);
            
            $table->dropForeign(['recruiter_id']);
            $table->dropColumn([
                'referral_code', 'source', 'read_at', 'is_read',
                'rejected_at', 'accepted_at', 'interview_scheduled_at',
                'shortlisted_at', 'reviewed_at', 'interview_notes',
                'interview_type', 'interview_location', 'interview_date',
                'feedback_to_talent', 'resume_url', 'pitch', 'recruiter_id'
            ]);
        });
    }
};