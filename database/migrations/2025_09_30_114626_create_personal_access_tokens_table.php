<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This is Laravel Sanctum's token table for API authentication
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            // Auto-incrementing ID (Sanctum requirement)
            $table->id();
            
            // Polymorphic relation to tokenable model (User)
            $table->uuidMorphs('tokenable');
            
            // Token name/identifier
            $table->string('name');
            
            // Hashed token (only hash is stored, never plain text)
            $table->string('token', 64)->unique();
            
            // Token abilities/scopes (JSON array)
            // Example: ["talent:read", "talent:write", "project:apply"]
            $table->text('abilities')->nullable();
            
            // Track last usage for security and analytics
            $table->timestamp('last_used_at')->nullable();
            
            // Token expiration (optional, configurable)
            $table->timestamp('expires_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index('last_used_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};