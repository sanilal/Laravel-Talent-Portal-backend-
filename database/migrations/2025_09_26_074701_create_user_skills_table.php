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
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->integer('proficiency_level'); // 1-5 scale
            $table->integer('years_of_experience')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
            $table->index(['proficiency_level', 'years_of_experience']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skills');
    }
};
