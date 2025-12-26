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
        Schema::table('casting_calls', function (Blueprint $table) {
            // Drop project_id if it exists
            if (Schema::hasColumn('casting_calls', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
            
            // Add new project-related fields
            $table->uuid('genre_id')->nullable()->after('recruiter_id');
            $table->string('project_name')->after('genre_id');
            $table->string('director')->nullable()->after('project_name');
            $table->string('production_company')->nullable()->after('director');
            $table->string('city')->nullable()->after('location');
            $table->text('synopsis')->nullable()->after('description');
            $table->text('additional_notes')->nullable()->after('synopsis');
            
            // Add foreign key for genre
            $table->foreign('genre_id')
                  ->references('id')
                  ->on('genres')
                  ->onDelete('set null');
                  
            // Add indexes
            $table->index('genre_id');
            $table->index('city');
            $table->index('project_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('casting_calls', function (Blueprint $table) {
            $table->dropForeign(['genre_id']);
            $table->dropColumn([
                'genre_id',
                'project_name',
                'director',
                'production_company',
                'city',
                'synopsis',
                'additional_notes'
            ]);
            
            // Restore project_id if needed
            $table->uuid('project_id')->nullable();
        });
    }
};