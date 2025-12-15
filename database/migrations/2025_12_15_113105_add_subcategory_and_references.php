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
        // Add subcategory_id to talent_profiles
        Schema::table('talent_profiles', function (Blueprint $table) {
            $table->uuid('subcategory_id')->nullable()->after('primary_category_id');
            
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('set null');
                
            $table->index('subcategory_id');
        });

        // Add subcategory_id to experiences
        Schema::table('experiences', function (Blueprint $table) {
            $table->uuid('subcategory_id')->nullable()->after('category_id');
            
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('set null');
                
            $table->index('subcategory_id');
        });

        // Add subcategory_id to portfolios
        Schema::table('portfolios', function (Blueprint $table) {
            $table->uuid('subcategory_id')->nullable()->after('category_id');
            
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('set null');
                
            $table->index('subcategory_id');
        });

        // Add project_type_id to projects
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('project_type_id')->nullable()->after('project_type');
            
            $table->foreign('project_type_id')
                ->references('id')
                ->on('project_types')
                ->onDelete('set null');
                
            $table->index('project_type_id');
        });

        // Add country and state references to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable()->after('country');
            $table->unsignedBigInteger('state_id')->nullable()->after('state');
            
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('set null');
                
            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->onDelete('set null');
                
            $table->index('country_id');
            $table->index('state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropColumn(['country_id', 'state_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['project_type_id']);
            $table->dropColumn('project_type_id');
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });

        Schema::table('talent_profiles', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });
    }
};