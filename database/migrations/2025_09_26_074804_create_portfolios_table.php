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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->string('project_type')->nullable(); // web, mobile, design, etc.
            $table->json('technologies_used')->nullable();
            $table->string('project_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('client_name')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->text('solutions_implemented')->nullable();
            $table->json('project_images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['is_featured', 'is_public']);
            $table->index(['project_type', 'is_public']);
            $table->index(['user_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
