<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update the casting_calls status enum to use 'published' instead of 'open'
     * This aligns the database schema with the model's published() scope
     */
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM column
        // First, update any existing 'open' values to 'published'
        DB::statement("UPDATE casting_calls SET status = 'published' WHERE status = 'open'");

        // Then alter the ENUM to replace 'open' with 'published'
        DB::statement("ALTER TABLE casting_calls MODIFY COLUMN status ENUM('draft', 'published', 'closed', 'filled', 'cancelled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'published' back to 'open'
        DB::statement("UPDATE casting_calls SET status = 'open' WHERE status = 'published'");

        // Restore the original ENUM
        DB::statement("ALTER TABLE casting_calls MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'filled', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};
