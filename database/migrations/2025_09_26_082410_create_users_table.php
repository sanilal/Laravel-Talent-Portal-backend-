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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->enum('user_type', ['talent', 'recruiter', 'admin'])->default('talent');
            $table->enum('account_status', ['active', 'inactive', 'suspended', 'pending_verification', 'banned'])->default('pending_verification');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_email_verified')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->ipAddress('last_login_ip')->nullable();
            $table->string('timezone')->default('UTC');
            $table->json('privacy_settings')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'user_type']);
            $table->index(['account_status', 'user_type']);
            $table->index(['is_verified', 'is_email_verified']);
            $table->index(['created_at', 'user_type']);
            $table->fullText(['first_name', 'last_name', 'bio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
