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
        Schema::create('experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('talent_profile_id');
            $table->uuid('category_id')->nullable();
            $table->string('title'); // Role/Position title
            $table->string('company_name')->nullable();
            $table->string('project_name')->nullable(); // For entertainment industry
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('employment_type')->nullable(); // full-time, contract, freelance, etc.
            $table->json('skills_used')->nullable();
            $table->json('achievements')->nullable();
            $table->string('company_website')->nullable();
            $table->decimal('compensation', 10, 2)->nullable();
            $table->string('compensation_type')->nullable(); // hourly, daily, project, salary
            $table->json('media_attachments')->nullable(); // Photos, videos from the experience
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('talent_profile_id')->references('id')->on('talent_profiles')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->index(['talent_profile_id', 'order']);
            $table->index(['is_current', 'end_date']);
            $table->index(['category_id', 'is_featured']);
            $table->fullText(['title', 'company_name', 'project_name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
