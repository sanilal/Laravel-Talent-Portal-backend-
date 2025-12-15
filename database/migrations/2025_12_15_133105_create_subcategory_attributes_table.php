<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table defines what additional fields each subcategory should have.
     * For example:
     * - Singing subcategory has: vocal_range, music_genres, singing_experience
     * - Actor subcategory has: height, weight, skin_tone, chest, waist, hips
     * - Photography subcategory has: camera_equipment, photography_styles
     */
    public function up(): void
    {
        Schema::create('subcategory_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subcategory_id');
            $table->string('field_name'); // e.g., 'vocal_range', 'vehicle_type'
            $table->string('field_label'); // e.g., 'Vocal Range', 'Vehicle Type'
            $table->enum('field_type', [
                'text',           // Short text input
                'textarea',       // Long text input
                'number',         // Numeric input
                'select',         // Single select dropdown
                'multiselect',    // Multiple select dropdown
                'radio',          // Radio buttons
                'checkbox',       // Checkboxes
                'date',           // Date picker
                'file',           // File upload
                'url',            // URL input
                'email',          // Email input
                'phone',          // Phone input
                'color',          // Color picker
                'range',          // Slider
            ])->default('text');
            $table->json('field_options')->nullable(); // For select/multiselect/radio/checkbox
            $table->text('field_description')->nullable();
            $table->string('field_placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false); // Can filter profiles by this field
            $table->boolean('is_public')->default(true); // Show on public profile
            $table->string('validation_rules')->nullable(); // Laravel validation rules
            $table->integer('min_value')->nullable(); // For number/range fields
            $table->integer('max_value')->nullable(); // For number/range fields
            $table->integer('min_length')->nullable(); // For text fields
            $table->integer('max_length')->nullable(); // For text fields
            $table->string('unit')->nullable(); // e.g., 'years', 'kg', 'cm'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional configuration
            $table->timestamps();

            // Foreign keys
            $table->foreign('subcategory_id')
                ->references('id')
                ->on('subcategories')
                ->onDelete('cascade');

            // Indexes
            $table->index('subcategory_id');
            $table->index(['subcategory_id', 'is_active']);
            $table->index('is_searchable');
            $table->unique(['subcategory_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategory_attributes');
    }
};