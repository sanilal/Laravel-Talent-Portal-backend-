<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores the actual values for dynamic fields per talent skill.
     * For example:
     * - Talent John has Singing skill with vocal_range = "Tenor"
     * - Talent Mary has Dancing skill with dance_styles = ["Ballet", "Contemporary"]
     */
    public function up(): void
    {
        Schema::create('talent_skill_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('talent_skill_id'); // Links to talent_skills table
            $table->uuid('attribute_id'); // Links to subcategory_attributes table
            $table->text('value')->nullable(); // Stores the actual value (JSON for arrays)
            $table->timestamps();

            // Foreign keys
            $table->foreign('talent_skill_id')
                ->references('id')
                ->on('talent_skills')
                ->onDelete('cascade');

            $table->foreign('attribute_id')
                ->references('id')
                ->on('subcategory_attributes')
                ->onDelete('cascade');

            // Indexes
            $table->index('talent_skill_id');
            $table->index('attribute_id');
            $table->unique(['talent_skill_id', 'attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent_skill_attributes');
    }
};