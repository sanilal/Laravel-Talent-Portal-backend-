<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores all dropdown values for the platform
     * Types: 
     * 1 = Height
     * 2 = Skin Tone
     * 3 = Weight
     * 4 = Age Range
     * 5 = Vehicle Type (Transportation)
     * 6 = Service Type (Transportation)
     * 7 = Event Type
     * 8 = Budget Range
     * 9 = Eye Color
     * 10 = Hair Color
     * 11 = Body Type
     * 12 = Vocal Range
     * 13 = Experience Level
     * 14 = Language Proficiency
     * 15 = Availability
     */
    public function up(): void
    {
        Schema::create('dropdown_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('type'); // Dropdown type identifier
            $table->string('value'); // The actual value/label
            $table->string('value_secondary')->nullable(); // For ranges or additional info
            $table->string('code')->nullable(); // Short code if needed
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional attributes
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index(['type', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dropdown_values');
    }
};