<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check database driver and use appropriate syntax
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            // MySQL: Modify ENUM column directly
            DB::statement("
                ALTER TABLE projects 
                MODIFY COLUMN status ENUM(
                    'draft', 
                    'open', 
                    'in_progress', 
                    'completed', 
                    'cancelled', 
                    'paused'
                ) NOT NULL DEFAULT 'draft'
            ");
        } else {
            // PostgreSQL: Drop and add constraint
            DB::statement("ALTER TABLE projects DROP CONSTRAINT projects_status_check");
            DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'open', 'in_progress', 'completed', 'cancelled', 'paused'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            // MySQL: Revert ENUM column
            DB::statement("
                ALTER TABLE projects 
                MODIFY COLUMN status ENUM(
                    'draft', 
                    'published', 
                    'in_progress', 
                    'completed', 
                    'cancelled', 
                    'expired'
                ) NOT NULL DEFAULT 'draft'
            ");
        } else {
            // PostgreSQL: Drop and add constraint
            DB::statement("ALTER TABLE projects DROP CONSTRAINT projects_status_check");
            DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'published', 'in_progress', 'completed', 'cancelled', 'expired'))");
        }
    }
};