<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing data to use the new enum values
        DB::statement("UPDATE applications SET status = 'under_review' WHERE status = 'reviewing'");
        DB::statement("UPDATE applications SET status = 'interview_scheduled' WHERE status = 'interview'");

        // For SQLite in testing, we need to handle ENUM differently
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ENUM, so we'll just ensure the values are updated
            // The validation happens at the application level
        } else {
            // For MySQL/MariaDB, update the ENUM definition
            DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending', 'under_review', 'shortlisted', 'interview_scheduled', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data changes
        DB::statement("UPDATE applications SET status = 'reviewing' WHERE status = 'under_review'");
        DB::statement("UPDATE applications SET status = 'interview' WHERE status = 'interview_scheduled'");

        if (DB::getDriverName() !== 'sqlite') {
            // Restore original ENUM values
            DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending', 'reviewing', 'shortlisted', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending'");
        }
    }
};
