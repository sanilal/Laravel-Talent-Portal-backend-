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
        Schema::create('talent_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->uuid('primary_category_id')->nullable();
            $table->string('professional_title')->nullable();
            $table->text('summary')->nullable();
            $table->string('experience_level')->nullable(); // entry, junior, mid, senior, expert
            $table->decimal('hourly_rate_min', 8, 2)->nullable();
            $table->decimal('hourly_rate_max', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('availability_types')->nullable(); // full-time, part-time, contract, freelance
            $table->boolean('is_available')->default(true);
            $table->json('work_preferences')->nullable(); // remote, hybrid, onsite
            $table->json('preferred_locations')->nullable();
            $table->string('notice_period')->nullable();
            $table->json('languages')->nullable();
            $table->integer('profile_completion_percentage')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->integer('profile_views')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->json('portfolio_highlights')->nullable();
            $table->timestamp('availability_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('primary_category_id')->references('id')->on('categories')->onDelete('set null');
            $table->index(['is_available', 'is_public']);
            $table->index(['experience_level', 'is_available']);
            $table->index(['hourly_rate_min', 'hourly_rate_max']);
            $table->index(['is_featured', 'average_rating']);
            $table->index(['primary_category_id', 'is_available']);
            $table->fullText(['professional_title', 'summary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_profiles');
    }
};
