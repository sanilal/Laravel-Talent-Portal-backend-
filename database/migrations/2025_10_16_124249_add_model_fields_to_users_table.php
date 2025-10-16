<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Social media links (separate columns for frontend)
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('twitter_url')->nullable()->after('linkedin_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
            
            // Model-specific fields
            $table->string('height')->nullable()->after('languages');
            $table->string('weight')->nullable()->after('height');
            $table->string('chest')->nullable()->after('weight');
            $table->string('waist')->nullable()->after('chest');
            $table->string('hips')->nullable()->after('waist');
            $table->string('shoe_size')->nullable()->after('hips');
            $table->string('hair_color')->nullable()->after('shoe_size');
            $table->string('eye_color')->nullable()->after('hair_color');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_url',
                'twitter_url',
                'instagram_url',
                'height',
                'weight',
                'chest',
                'waist',
                'hips',
                'shoe_size',
                'hair_color',
                'eye_color',
            ]);
        });
    }
};