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
        Schema::table('skills', function (Blueprint $table) {
            if (!Schema::hasColumn('skills', 'icon')) {
                $table->string('icon')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('skills', 'description')) {
                $table->text('description')->nullable()->after('icon');
            }
            
            if (!Schema::hasColumn('skills', 'talents_count')) {
                $table->integer('talents_count')->default(0)->after('usage_count');
            }
        });

        // Remove this line - it causes the error
        // $this->command->info('✅ Added icon, description, and talents_count to skills table');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            if (Schema::hasColumn('skills', 'icon')) {
                $table->dropColumn('icon');
            }
            
            if (Schema::hasColumn('skills', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('skills', 'talents_count')) {
                $table->dropColumn('talents_count');
            }
        });
    }
};