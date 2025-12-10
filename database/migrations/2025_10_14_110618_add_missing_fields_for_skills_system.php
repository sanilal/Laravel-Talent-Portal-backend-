<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check and add missing fields to users table
        // Removed ->after() clauses as Laravel 11/12 has different default user table structure
        if (!Schema::hasColumn('users', 'professional_title')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('professional_title')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('bio')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'cover_image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cover_image')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'hourly_rate')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('hourly_rate', 10, 2)->nullable();
                $table->string('currency', 3)->default('USD');
            });
        }

        if (!Schema::hasColumn('users', 'experience_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('experience_level', ['entry', 'intermediate', 'senior', 'expert'])->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'availability_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('availability_status', ['available', 'busy', 'not_available'])->default('available');
            });
        }

        if (!Schema::hasColumn('users', 'profile_views')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('profile_views')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'profile_completion')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('profile_completion')->default(0);
            });
        }

        if (!Schema::hasColumn('users', 'languages')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('languages')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'website')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('website')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->string('twitter_url')->nullable();
                $table->string('instagram_url')->nullable();
            });
        }

        // Check talent_skills table
        if (!Schema::hasColumn('talent_skills', 'description')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        if (!Schema::hasColumn('talent_skills', 'proficiency_level')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            });
        }

        if (!Schema::hasColumn('talent_skills', 'years_of_experience')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->integer('years_of_experience')->nullable();
            });
        }

        if (!Schema::hasColumn('talent_skills', 'image_path')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->string('image_path')->nullable();
                $table->string('video_url')->nullable();
            });
        }

        if (!Schema::hasColumn('talent_skills', 'is_primary')) {
            Schema::table('talent_skills', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false);
                $table->integer('display_order')->default(0);
                $table->boolean('show_on_profile')->default(true);
            });
        }

        // Check skills table
        if (!Schema::hasColumn('skills', 'icon')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->string('icon')->nullable();
            });
        }

        if (!Schema::hasColumn('skills', 'talents_count')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->integer('talents_count')->default(0);
            });
        }

        if (!Schema::hasColumn('skills', 'is_active')) {
            Schema::table('skills', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }

        // Check experiences table - FIXED FOR UUID
        if (!Schema::hasColumn('experiences', 'user_id')) {
            // Step 1: Add column as nullable UUID
            Schema::table('experiences', function (Blueprint $table) {
                $table->uuid('user_id')->nullable();
            });

            // Step 2: Populate existing records
            $firstUserId = DB::table('users')->first()?->id;
            if ($firstUserId) {
                DB::table('experiences')
                    ->whereNull('user_id')
                    ->update(['user_id' => $firstUserId]);
            }
            // Option B: Delete orphaned records (uncomment if you prefer)
            // DB::table('experiences')->whereNull('user_id')->delete();

            // Step 3: Make it NOT NULL and add foreign key
            Schema::table('experiences', function (Blueprint $table) {
                $table->uuid('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('experiences', 'is_current')) {
            Schema::table('experiences', function (Blueprint $table) {
                $table->boolean('is_current')->default(false);
            });
        }

        // Check education table - FIXED FOR UUID
        if (!Schema::hasColumn('education', 'user_id')) {
            // Step 1: Add column as nullable UUID
            Schema::table('education', function (Blueprint $table) {
                $table->uuid('user_id')->nullable();
            });

            // Step 2: Populate existing records
            $firstUserId = DB::table('users')->first()?->id;
            if ($firstUserId) {
                DB::table('education')
                    ->whereNull('user_id')
                    ->update(['user_id' => $firstUserId]);
            }
            // Option B: Delete orphaned records (uncomment if you prefer)
            // DB::table('education')->whereNull('user_id')->delete();

            // Step 3: Make it NOT NULL and add foreign key
            Schema::table('education', function (Blueprint $table) {
                $table->uuid('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('education', 'is_current')) {
            Schema::table('education', function (Blueprint $table) {
                $table->boolean('is_current')->default(false);
            });
        }

        // Check portfolios table
        if (!Schema::hasColumn('portfolios', 'is_featured')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false);
                $table->integer('display_order')->default(0);
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
            if (Schema::hasColumn('experiences', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('experiences', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });

        Schema::table('education', function (Blueprint $table) {
            if (Schema::hasColumn('education', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
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