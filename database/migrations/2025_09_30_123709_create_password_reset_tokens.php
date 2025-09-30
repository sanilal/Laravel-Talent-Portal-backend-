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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Use email as primary key (Laravel's default approach)
            $table->string('email')->primary();
            
            // Token for password reset
            $table->string('token');
            
            // Track when token was created
            $table->timestamp('created_at')->nullable();
            
            // Index for faster token lookup
            $table->index('token');
            
            // Automatically delete old tokens (Laravel handles this)
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};