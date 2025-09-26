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
        Schema::create('education', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('talent_profile_id');
            $table->string('institution_name');
            $table->string('degree');
            $table->string('field_of_study')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();
            $table->json('activities')->nullable();
            $table->json('certifications')->nullable();
            $table->string('institution_website')->nullable();
            $table->json('attachments')->nullable(); // Certificates, transcripts
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('talent_profile_id')->references('id')->on('talent_profiles')->onDelete('cascade');
            $table->index(['talent_profile_id', 'order']);
            $table->index(['is_current', 'end_date']);
            $table->fullText(['institution_name', 'degree', 'field_of_study']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
