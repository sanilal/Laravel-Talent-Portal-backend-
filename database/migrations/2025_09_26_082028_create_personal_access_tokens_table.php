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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('tokenable'); // Uses UUID foreign keys instead of integer
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->ipAddress('created_ip')->nullable();
            $table->ipAddress('last_used_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Additional indexes for performance
            $table->index('expires_at');
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index(['last_used_at', 'expires_at']);
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
