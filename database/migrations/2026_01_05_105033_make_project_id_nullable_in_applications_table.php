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
        Schema::table('applications', function (Blueprint $table) {
            // Make project_id nullable since applications can be for either projects OR casting calls
            $table->uuid('project_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Revert to NOT NULL (be careful - this will fail if there are null values)
            $table->uuid('project_id')->nullable(false)->change();
        });
    }
};
