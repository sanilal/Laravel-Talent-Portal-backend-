<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('recipient_id');
            $table->text('subject')->nullable();
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('message_type')->default('direct');
            $table->uuid('project_id')->nullable();
            $table->uuid('application_id')->nullable();
            $table->uuid('parent_id')->nullable(); // Keep the column, remove the foreign key
            $table->boolean('is_important')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
            // Remove this line: $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
            
            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'is_read']);
            $table->index(['project_id', 'message_type']);
            $table->index(['parent_id', 'created_at']);
            $table->index(['is_archived', 'created_at']);
            $table->fullText(['subject', 'body']);
        });

        // Add the self-referencing foreign key after table creation
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};