<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check and add missing fields to users table
        if (!Schema::hasColumn('users', 'professional_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('professional_title')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('bio')->nullable()->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('bio');
            });
        }

        if (!Schema::hasColumn('users', 'cover_image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cover_image')->nullable()->after('avatar');
            });
        }

        if (!Schema::hasColumn('users', 'city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('city')->nullable()->after('cover_image');
                $table->string('state')->nullable()->after('city');
                $table->string('country')->nullable()->after('state');
            });
        }

        if (!Schema::hasColumn('users', 'hourly_rate')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('hourly_rate', 10, 2)->nullable()->after('country');
                $table->string('currency', 3)->default('USD')->after('hourly_rate');
            });
        }

        if (!Schema::hasColumn('users', 'experience_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('experience_level', ['entry', 'intermediate', 'senior', 'expert'])->nullable()->after('currency');
            });
        }

        if (!Schema::hasColumn('users', 'availability_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('availability_status', ['available', 'busy', 'not_available'])->default('available')->after('experience_level');
            });
        }

        if (!Schema::hasColumn('users', 'profile_views')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('profile_views')->default(0)->after('availability_status');
            });
        }

        if (!Schema::hasColumn('users', 'profile_completion')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('profile_completion')->default(0)->after('profile_views');
            });
        }

        if (!Schema::hasColumn('users', 'languages')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('languages')->nullable()->after('profile_completion');
            });
        }

        if (!Schema::hasColumn('users', 'website')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('website')->nullable()->after('languages');
                $table->string('linkedin_url')->nullable()->after('website');
                $table->string('twitter_url')->nullable()->after('linkedin_url');
                $table->string('instagram_url')->nullable()->after('twitter_url');
            });
        }

        // Check talent_skills table
        if (!Schema::hasColumn('talent_skills', 'description')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->text('description')->nullable()->after('skill_id');
            });
        }

        if (!Schema::hasColumn('talent_skills', 'proficiency_level')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate')->after('description');
            });
        }

        if (!Schema::hasColumn('talent_skills', 'years_of_experience')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->integer('years_of_experience')->nullable()->after('proficiency_level');
            });
        }

        if (!Schema::hasColumn('talent_skills', 'image_path')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('years_of_experience');
                $table->string('video_url')->nullable()->after('image_path');
            });
        }

        if (!Schema::hasColumn('talent_skills', 'is_primary')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('video_url');
                $table->integer('display_order')->default(0)->after('is_primary');
                $table->boolean('show_on_profile')->default(true)->after('display_order');
            });
        }

        // Check skills table
        if (!Schema::hasColumn('skills', 'icon')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('skills', 'talents_count')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->integer('talents_count')->default(0)->after('icon');
            });
        }

        if (!Schema::hasColumn('skills', 'is_active')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('talents_count');
            });
        }

        // Check experiences table
        if (!Schema::hasColumn('experiences', 'user_id')) {
            Schema::table('experiences', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('experiences', 'is_current')) {
            Schema::table('experiences', function (Blueprint $table) {
                $table->boolean('is_current')->default(false)->after('end_date');
            });
        }

        // Check education table  
        if (!Schema::hasColumn('education', 'user_id')) {
            Schema::table('education', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('education', 'is_current')) {
            Schema::table('education', function (Blueprint $table) {
                $table->boolean('is_current')->default(false)->after('end_date');
            });
        }

        // Check portfolios table
        if (!Schema::hasColumn('portfolios', 'is_featured')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->after('external_url');
                $table->integer('display_order')->default(0)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        // Reverse the changes if needed
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'professional_title', 'bio', 'avatar', 'cover_image',
                'city', 'state', 'country', 'hourly_rate', 'currency',
                'experience_level', 'availability_status', 'profile_views',
                'profile_completion', 'languages', 'website', 'linkedin_url',
                'twitter_url', 'instagram_url'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('talent_skills', function (Blueprint $table) {
            $columns = [
                'description', 'proficiency_level', 'years_of_experience',
                'image_path', 'video_url', 'is_primary', 'display_order',
                'show_on_profile'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('talent_skills', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('skills', function (Blueprint $table) {
            $columns = ['icon', 'talents_count', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('skills', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('experiences', function (Blueprint $table) {
            if (Schema::hasColumn('experiences', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });

        Schema::table('education', function (Blueprint $table) {
            if (Schema::hasColumn('education', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });

        Schema::table('portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('portfolios', 'is_featured')) {
                $table->dropColumn(['is_featured', 'display_order']);
            }
        });
    }
};