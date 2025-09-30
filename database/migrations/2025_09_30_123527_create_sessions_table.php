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
        Schema::create('sessions', function (Blueprint $table) {
            // Session ID as primary key
            $table->string('id')->primary();
            
            // User ID (nullable for guest sessions, UUID for authenticated users)
            $table->uuid('user_id')->nullable();
            
            // IP address for security tracking
            $table->string('ip_address', 45)->nullable();
            
            // User agent for device tracking
            $table->text('user_agent')->nullable();
            
            // Session payload (encrypted by Laravel)
            $table->longText('payload');
            
            // Last activity timestamp
            $table->integer('last_activity');
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('last_activity');
            
            // Foreign key to users table
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};