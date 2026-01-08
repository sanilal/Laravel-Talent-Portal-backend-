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
        Schema::create('application_casting_call_requirement', function (Blueprint $table) {
            $table->id();
            $table->uuid('application_id');
            $table->uuid('casting_call_requirement_id');
            $table->timestamps();

            // Foreign key constraints with custom names to avoid length issues
            $table->foreign('application_id', 'app_cc_req_app_id_fk')
                  ->references('id')
                  ->on('applications')
                  ->onDelete('cascade');

            $table->foreign('casting_call_requirement_id', 'app_cc_req_cc_req_id_fk')
                  ->references('id')
                  ->on('casting_call_requirements')
                  ->onDelete('cascade');

            // Ensure unique combination - one application can't select the same role twice
            $table->unique(['application_id', 'casting_call_requirement_id'], 'app_req_unique');

            // Indexes for performance with custom names
            $table->index('application_id', 'app_cc_req_app_id_idx');
            $table->index('casting_call_requirement_id', 'app_cc_req_cc_req_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_casting_call_requirement');
    }
};
