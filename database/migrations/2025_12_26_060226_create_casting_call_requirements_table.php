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
        Schema::create('casting_call_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('casting_call_id');
            
            // Talent criteria
            $table->enum('gender', ['male', 'female', 'non-binary', 'any'])->nullable();
            $table->string('age_group')->nullable(); // e.g., "20-25"
            $table->string('skin_tone')->nullable(); // Fair, Light, Medium, etc.
            $table->string('height')->nullable(); // e.g., "5'6""
            
            // Role details
            $table->uuid('subcategory_id')->nullable(); // Actor, Actress, Model, etc.
            $table->string('role_name');
            $table->text('role_description')->nullable();
            
            // Display order for multiple requirements
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('casting_call_id')
                  ->references('id')
                  ->on('casting_calls')
                  ->onDelete('cascade');
                  
            $table->foreign('subcategory_id')
                  ->references('id')
                  ->on('subcategories')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('casting_call_id');
            $table->index('subcategory_id');
            $table->index('gender');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casting_call_requirements');
    }
};