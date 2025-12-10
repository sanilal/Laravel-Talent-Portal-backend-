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
            if (!Schema::hasColumn('applications', 'recruiter_id')) {
                $table->foreignUuid('recruiter_id')->nullable()->after('talent_id')
                      ->constrained('users')->onDelete('cascade');
            }
            
            // Add pitch (short intro separate from cover_letter)
            if (!Schema::hasColumn('applications', 'pitch')) {
                $table->text('pitch')->nullable()->after('message');
            }
            
            // Add resume URL
            if (!Schema::hasColumn('applications', 'resume_url')) {
                $table->string('resume_url')->nullable()->after('audition_video_url');
            }
            
            // Add public feedback (separate from private recruiter_notes)
            if (!Schema::hasColumn('applications', 'feedback_to_talent')) {
                $table->text('feedback_to_talent')->nullable()->after('recruiter_notes');
            }
            
            // Add interview/callback fields
            if (!Schema::hasColumn('applications', 'interview_date')) {
                $table->timestamp('interview_date')->nullable()->after('responded_at');
            }
            
            if (!Schema::hasColumn('applications', 'interview_location')) {
                $table->string('interview_location')->nullable()->after('interview_date');
            }
            
            if (!Schema::hasColumn('applications', 'interview_type')) {
                $table->enum('interview_type', ['in_person', 'video_call', 'phone'])->nullable()->after('interview_location');
            }
            
            if (!Schema::hasColumn('applications', 'interview_notes')) {
                $table->text('interview_notes')->nullable()->after('interview_type');
            }
            
            // Add timeline tracking
            if (!Schema::hasColumn('applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('viewed_at');
            }
            
            if (!Schema::hasColumn('applications', 'shortlisted_at')) {
                $table->timestamp('shortlisted_at')->nullable()->after('reviewed_at');
            }
            
            if (!Schema::hasColumn('applications', 'interview_scheduled_at')) {
                $table->timestamp('interview_scheduled_at')->nullable()->after('shortlisted_at');
            }
            
            if (!Schema::hasColumn('applications', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('interview_scheduled_at');
            }
            
            if (!Schema::hasColumn('applications', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            }
            
            // Add read status
            if (!Schema::hasColumn('applications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('rating');
            }
            
            if (!Schema::hasColumn('applications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            
            // Add source tracking
            if (!Schema::hasColumn('applications', 'source')) {
                $table->string('source')->nullable()->after('metadata');
            }
            
            if (!Schema::hasColumn('applications', 'referral_code')) {
                $table->string('referral_code')->nullable()->after('source');
            }
        });

        // Add indexes - check if they exist first using raw SQL
        $this->addIndexIfNotExists('applications', 'recruiter_id', 'applications_recruiter_id_index');
        $this->addIndexIfNotExists('applications', 'is_read', 'applications_is_read_index');
        $this->addCompositeIndexIfNotExists('applications', ['status', 'created_at'], 'applications_status_created_at_index');
        $this->addCompositeIndexIfNotExists('applications', ['recruiter_id', 'status'], 'applications_recruiter_id_status_index');

        // Backfill recruiter_id from projects - MySQL/MariaDB compatible syntax
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement('
                UPDATE applications
                INNER JOIN projects ON applications.project_id = projects.id
                SET applications.recruiter_id = projects.posted_by
                WHERE applications.recruiter_id IS NULL
            ');
        } else {
            // PostgreSQL syntax
            DB::statement('
                UPDATE applications 
                SET recruiter_id = projects.posted_by 
                FROM projects 
                WHERE applications.project_id = projects.id 
                AND applications.recruiter_id IS NULL
            ');
        }
        
        // Add unique constraints - MySQL/MariaDB compatible
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            $this->addCompositeIndexIfNotExists('applications', ['talent_id', 'casting_call_id'], 'idx_talent_casting_call');
        } else {
            // For PostgreSQL: Use partial unique index
            $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'unique_talent_casting_call'");
            if (empty($indexExists)) {
                DB::statement('
                    CREATE UNIQUE INDEX unique_talent_casting_call 
                    ON applications(talent_id, casting_call_id) 
                    WHERE casting_call_id IS NOT NULL AND deleted_at IS NULL
                ');
            }
        }
    }

    public function down(): void
    {
        // Drop index based on database driver
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            $this->dropIndexIfExists('applications', 'idx_talent_casting_call');
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_talent_casting_call');
        }
        
        // Drop indexes
        $this->dropIndexIfExists('applications', 'applications_recruiter_id_index');
        $this->dropIndexIfExists('applications', 'applications_is_read_index');
        $this->dropIndexIfExists('applications', 'applications_status_created_at_index');
        $this->dropIndexIfExists('applications', 'applications_recruiter_id_status_index');
        
        Schema::table('applications', function (Blueprint $table) {
            // Check for foreign keys before dropping
            if (Schema::hasColumn('applications', 'recruiter_id')) {
                try {
                    $table->dropForeign(['recruiter_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            }
            
            // Only drop columns that exist
            $columnsToCheck = [
                'referral_code', 'source', 'read_at', 'is_read',
                'rejected_at', 'accepted_at', 'interview_scheduled_at',
                'shortlisted_at', 'reviewed_at', 'interview_notes',
                'interview_type', 'interview_location', 'interview_date',
                'feedback_to_talent', 'resume_url', 'pitch', 'recruiter_id'
            ];
            
            $existingColumns = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }

    /**
     * Add single column index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            DB::statement("CREATE INDEX `$indexName` ON `$table` (`$column`)");
        }
    }

    /**
     * Add composite index if it doesn't exist
     */
    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            $columnList = implode('`, `', $columns);
            DB::statement("CREATE INDEX `$indexName` ON `$table` (`$columnList`)");
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            try {
                DB::statement("DROP INDEX `$indexName` ON `$table`");
            } catch (\Exception $e) {
                // Index might not exist
            }
        }
    }

    /**
     * Check if index exists using raw SQL (works without Doctrine)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            $result = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
            return !empty($result);
        } else {
            // PostgreSQL
            $result = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = ?", [$indexName]);
            return !empty($result);
        }
    }
};