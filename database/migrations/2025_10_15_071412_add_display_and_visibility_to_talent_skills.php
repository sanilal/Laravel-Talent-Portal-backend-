<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_skills', function (Blueprint $table) {
            if (!Schema::hasColumn('talent_skills', 'display_order')) {
                $table->integer('display_order')->default(0)->after('is_verified');
            }
            
            if (!Schema::hasColumn('talent_skills', 'show_on_profile')) {
                $table->boolean('show_on_profile')->default(true)->after('display_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talent_skills', function (Blueprint $table) {
            $table->dropColumn(['display_order', 'show_on_profile']);
        });
    }
};