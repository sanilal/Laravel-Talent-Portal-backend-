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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('subject')->nullable();
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('message_type')->default('direct'); // direct, job_related, system
            $table->foreignId('job_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained('job_applications')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'is_read']);
            $table->index(['job_id', 'message_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
