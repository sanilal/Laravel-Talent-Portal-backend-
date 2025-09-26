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
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('employment_type')->nullable(); // full-time, part-time, contract, etc.
            $table->json('skills_used')->nullable();
            $table->json('achievements')->nullable();
            $table->string('company_website')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'order']);
            $table->index(['is_current', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
