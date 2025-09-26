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
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable(); // Professional title
            $table->text('summary')->nullable();
            $table->json('skills')->nullable();
            $table->string('experience_level')->nullable(); // entry, junior, mid, senior, expert
            $table->decimal('hourly_rate_min', 8, 2)->nullable();
            $table->decimal('hourly_rate_max', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('availability')->nullable(); // full-time, part-time, contract, freelance
            $table->boolean('open_to_work')->default(true);
            $table->json('preferred_work_types')->nullable(); // remote, hybrid, onsite
            $table->json('preferred_locations')->nullable();
            $table->string('notice_period')->nullable(); // immediate, 2weeks, 1month, etc.
            $table->json('languages')->nullable();
            $table->integer('profile_completion_percentage')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->integer('profile_views')->default(0);
            $table->timestamp('last_profile_update')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['experience_level', 'open_to_work']);
            $table->index(['hourly_rate_min', 'hourly_rate_max']);
            $table->index('is_featured');
            $table->index('profile_views');
            $table->fullText(['title', 'summary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_profiles');
    }
};
