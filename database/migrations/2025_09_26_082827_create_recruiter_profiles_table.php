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
        Schema::create('recruiter_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('company_name');
            $table->string('company_slug')->unique();
            $table->text('company_description')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable(); // startup, small, medium, large, enterprise
            $table->string('company_website')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->json('company_address')->nullable();
            $table->string('company_logo_url')->nullable();
            $table->json('social_links')->nullable();
            $table->year('founded_year')->nullable();
            $table->string('company_type')->nullable(); // public, private, startup, non-profit
            $table->integer('employee_count')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->json('company_benefits')->nullable();
            $table->json('culture_values')->nullable();
            $table->json('hiring_preferences')->nullable(); // Preferred talent categories, experience levels
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->integer('active_projects_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['industry', 'is_verified']);
            $table->index(['company_size', 'is_featured']);
            $table->index(['verification_status', 'is_verified']);
            $table->index(['average_rating', 'total_ratings']);
            $table->fullText(['company_name', 'company_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_profiles');
    }
};
