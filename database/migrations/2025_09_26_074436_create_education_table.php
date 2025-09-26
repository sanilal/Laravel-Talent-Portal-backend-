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
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('institution_name');
            $table->string('degree');
            $table->string('field_of_study')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();
            $table->json('activities')->nullable();
            $table->string('institution_website')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'order']);
            $table->index(['is_current', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
