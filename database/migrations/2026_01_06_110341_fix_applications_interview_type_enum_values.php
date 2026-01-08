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
        DB::statement("UPDATE applications SET interview_type = 'in-person' WHERE interview_type = 'in_person'");
        DB::statement("UPDATE applications SET interview_type = 'video' WHERE interview_type = 'video_call'");
        // 'phone' stays the same

        // For SQLite in testing, we need to handle ENUM differently
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ENUM, so we'll just ensure the values are updated
            // The validation happens at the application level
        } else {
            // For MySQL/MariaDB, update the ENUM definition
            DB::statement("ALTER TABLE applications MODIFY COLUMN interview_type ENUM('in-person', 'video', 'phone') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data changes
        DB::statement("UPDATE applications SET interview_type = 'in_person' WHERE interview_type = 'in-person'");
        DB::statement("UPDATE applications SET interview_type = 'video_call' WHERE interview_type = 'video'");

        if (DB::getDriverName() !== 'sqlite') {
            // Restore original ENUM values
            DB::statement("ALTER TABLE applications MODIFY COLUMN interview_type ENUM('in_person', 'video_call', 'phone') NULL");
        }
    }
};
