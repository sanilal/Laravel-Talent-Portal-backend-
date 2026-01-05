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
            // Add project_type_id (integer, required)
            $table->unsignedBigInteger('project_type_id')->after('recruiter_id');
            $table->foreign('project_type_id')
                  ->references('id')
                  ->on('project_types')
                  ->onDelete('restrict');

            // Add country_id (integer, required)
            $table->unsignedBigInteger('country_id')->after('location');
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('restrict');

            // Add state_id (integer, nullable)
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->foreign('state_id')
                  ->references('id')
                  ->on('states')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('casting_calls', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['project_type_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            
            // Drop columns
            $table->dropColumn(['project_type_id', 'country_id', 'state_id']);
        });
    }
};