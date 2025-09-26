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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable(); // startup, small, medium, large, enterprise
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('social_links')->nullable();
            $table->year('founded_year')->nullable();
            $table->string('company_type')->nullable(); // public, private, startup, non-profit
            $table->integer('employee_count')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->json('benefits')->nullable();
            $table->json('culture_values')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['industry', 'status']);
            $table->index(['size', 'status']);
            $table->index('is_featured');
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
