<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old constraint
        DB::statement("ALTER TABLE projects DROP CONSTRAINT projects_status_check");
        
        // Add new constraint with updated values
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'open', 'in_progress', 'completed', 'cancelled', 'paused'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE projects DROP CONSTRAINT projects_status_check");
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'published', 'in_progress', 'completed', 'cancelled', 'expired'))");
    }
};