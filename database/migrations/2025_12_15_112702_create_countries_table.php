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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_name');
            $table->string('country_code', 2)->unique(); // ISO 3166-1 alpha-2 code
            $table->string('country_code_alpha3', 3)->nullable(); // ISO 3166-1 alpha-3 code
            $table->string('dialing_code', 10);
            $table->string('emoji', 10)->nullable();
            $table->string('currency', 3)->nullable(); // ISO 4217 currency code
            $table->string('currency_symbol', 10)->nullable();
            $table->string('flag')->nullable(); // Flag image filename
            $table->integer('numeric_code')->nullable(); // ISO 3166-1 numeric code
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('country_code');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};