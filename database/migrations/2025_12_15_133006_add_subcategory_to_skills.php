<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Link skills to subcategories for dynamic field management
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            // Add subcategory_id to skills table
            $table->uuid('subcategory_id')->nullable()->after('category_id');
            
            // Add foreign key
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('set null');
            
            // Add index
            $table->index('subcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });
    }
};