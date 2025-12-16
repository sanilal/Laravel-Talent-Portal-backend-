<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Subcategory;

class SubcategoryAttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Define dynamic fields for each subcategory
     */
    public function run(): void
    {
        $this->seedArtistAttributes();
        $this->seedCrewAttributes();
        $this->seedVendorAttributes();
        $this->seedWeddingFilmmakerAttributes();

        $this->command->info('Subcategory attributes seeded successfully!');
    }

    /**
     * Seed attributes for Artist subcategories
     */
    private function seedArtistAttributes(): void
    {
        // Actor/Actress attributes
        $actorSubcategories = Subcategory::whereIn('slug', ['actor', 'actress'])->get();
        foreach ($actorSubcategories as $sub) {
            $this->createAttributes($sub->id, [
                [
                    'field_name' => 'height',
                    'field_label' => 'Height',
                    'field_type' => 'select',
                    'field_description' => 'Select your height',
                    'field_options' => $this->getHeightOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'weight',
                    'field_label' => 'Weight Range',
                    'field_type' => 'select',
                    'field_description' => 'Select your weight range',
                    'field_options' => $this->getWeightOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'skin_tone',
                    'field_label' => 'Skin Tone',
                    'field_type' => 'select',
                    'field_description' => 'Select your skin tone',
                    'field_options' => $this->getSkinToneOptions(),
                    'is_searchable' => true,
                    'sort_order' => 3,
                ],
                [
                    'field_name' => 'eye_color',
                    'field_label' => 'Eye Color',
                    'field_type' => 'select',
                    'field_options' => $this->getEyeColorOptions(),
                    'is_searchable' => true,
                    'sort_order' => 4,
                ],
                [
                    'field_name' => 'hair_color',
                    'field_label' => 'Hair Color',
                    'field_type' => 'select',
                    'field_options' => $this->getHairColorOptions(),
                    'is_searchable' => true,
                    'sort_order' => 5,
                ],
                [
                    'field_name' => 'chest',
                    'field_label' => 'Chest (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 60,
                    'unit' => 'inches',
                    'sort_order' => 6,
                ],
                [
                    'field_name' => 'waist',
                    'field_label' => 'Waist (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 60,
                    'unit' => 'inches',
                    'sort_order' => 7,
                ],
                [
                    'field_name' => 'hips',
                    'field_label' => 'Hips (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 60,
                    'unit' => 'inches',
                    'sort_order' => 8,
                ],
                [
                    'field_name' => 'body_type',
                    'field_label' => 'Body Type',
                    'field_type' => 'select',
                    'field_options' => $this->getBodyTypeOptions(),
                    'is_searchable' => true,
                    'sort_order' => 9,
                ],
                [
                    'field_name' => 'acting_experience',
                    'field_label' => 'Years of Acting Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 50,
                    'unit' => 'years',
                    'is_required' => true,
                    'sort_order' => 10,
                ],
                [
                    'field_name' => 'training',
                    'field_label' => 'Acting Training',
                    'field_type' => 'textarea',
                    'field_description' => 'List your acting training, workshops, or courses',
                    'max_length' => 1000,
                    'sort_order' => 11,
                ],
                [
                    'field_name' => 'acting_styles',
                    'field_label' => 'Acting Styles',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getActingStylesOptions(),
                    'sort_order' => 12,
                ],
            ]);
        }

        // Male/Female Model attributes
        $modelSubcategories = Subcategory::whereIn('slug', ['male-model', 'female-model'])->get();
        foreach ($modelSubcategories as $sub) {
            $this->createAttributes($sub->id, [
                [
                    'field_name' => 'height',
                    'field_label' => 'Height',
                    'field_type' => 'select',
                    'field_options' => $this->getHeightOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'weight',
                    'field_label' => 'Weight Range',
                    'field_type' => 'select',
                    'field_options' => $this->getWeightOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'bust_chest',
                    'field_label' => 'Bust/Chest (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 60,
                    'unit' => 'inches',
                    'sort_order' => 3,
                ],
                [
                    'field_name' => 'waist',
                    'field_label' => 'Waist (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 50,
                    'unit' => 'inches',
                    'sort_order' => 4,
                ],
                [
                    'field_name' => 'hips',
                    'field_label' => 'Hips (inches)',
                    'field_type' => 'number',
                    'min_value' => 20,
                    'max_value' => 60,
                    'unit' => 'inches',
                    'sort_order' => 5,
                ],
                [
                    'field_name' => 'shoe_size',
                    'field_label' => 'Shoe Size',
                    'field_type' => 'number',
                    'min_value' => 3,
                    'max_value' => 15,
                    'unit' => 'US',
                    'sort_order' => 6,
                ],
                [
                    'field_name' => 'modeling_experience',
                    'field_label' => 'Years of Modeling Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 30,
                    'unit' => 'years',
                    'sort_order' => 7,
                ],
                [
                    'field_name' => 'modeling_types',
                    'field_label' => 'Modeling Types',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getModelingTypesOptions(),
                    'sort_order' => 8,
                ],
            ]);
        }

        // Singer attributes (Male/Female)
        $singerSubcategories = Subcategory::whereIn('slug', ['male-singer', 'female-singer'])->get();
        foreach ($singerSubcategories as $sub) {
            $this->createAttributes($sub->id, [
                [
                    'field_name' => 'vocal_range',
                    'field_label' => 'Vocal Range',
                    'field_type' => 'select',
                    'field_options' => $this->getVocalRangeOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'singing_experience',
                    'field_label' => 'Years of Singing Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 50,
                    'unit' => 'years',
                    'is_required' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'music_genres',
                    'field_label' => 'Music Genres',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getMusicGenresOptions(),
                    'is_searchable' => true,
                    'sort_order' => 3,
                ],
                [
                    'field_name' => 'can_read_music',
                    'field_label' => 'Can Read Music',
                    'field_type' => 'radio',
                    'field_options' => [
                        ['label' => 'Yes', 'value' => 'yes'],
                        ['label' => 'No', 'value' => 'no'],
                    ],
                    'sort_order' => 4,
                ],
                [
                    'field_name' => 'languages_sung',
                    'field_label' => 'Languages You Can Sing In',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getLanguagesOptions(),
                    'sort_order' => 5,
                ],
            ]);
        }

        // Influencer attributes
        $influencerSub = Subcategory::where('slug', 'influencers')->first();
        if ($influencerSub) {
            $this->createAttributes($influencerSub->id, [
                [
                    'field_name' => 'primary_platform',
                    'field_label' => 'Primary Platform',
                    'field_type' => 'select',
                    'field_options' => $this->getSocialPlatformsOptions(),
                    'is_required' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'follower_count',
                    'field_label' => 'Follower Count Range',
                    'field_type' => 'select',
                    'field_options' => $this->getFollowerCountOptions(),
                    'is_searchable' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'niche',
                    'field_label' => 'Content Niche',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getInfluencerNicheOptions(),
                    'is_searchable' => true,
                    'sort_order' => 3,
                ],
            ]);
        }
    }

    /**
     * Seed attributes for Crew subcategories
     */
    private function seedCrewAttributes(): void
    {
        // Photographer attributes
        $photographerSub = Subcategory::where('slug', 'photographer')->first();
        if ($photographerSub) {
            $this->createAttributes($photographerSub->id, [
                [
                    'field_name' => 'photography_experience',
                    'field_label' => 'Years of Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 40,
                    'unit' => 'years',
                    'is_required' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'photography_styles',
                    'field_label' => 'Photography Styles',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getPhotographyStylesOptions(),
                    'is_searchable' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'camera_equipment',
                    'field_label' => 'Camera Equipment',
                    'field_type' => 'textarea',
                    'field_description' => 'List your camera bodies, lenses, and lighting equipment',
                    'max_length' => 1000,
                    'sort_order' => 3,
                ],
                [
                    'field_name' => 'has_studio',
                    'field_label' => 'Own Studio',
                    'field_type' => 'radio',
                    'field_options' => [
                        ['label' => 'Yes', 'value' => 'yes'],
                        ['label' => 'No', 'value' => 'no'],
                    ],
                    'sort_order' => 4,
                ],
            ]);
        }

        // Dancer attributes
        $dancerSub = Subcategory::where('slug', 'dancer')->first();
        if ($dancerSub) {
            $this->createAttributes($dancerSub->id, [
                [
                    'field_name' => 'dance_styles',
                    'field_label' => 'Dance Styles',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getDanceStylesOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'dance_experience',
                    'field_label' => 'Years of Dancing Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 40,
                    'unit' => 'years',
                    'is_required' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'can_choreograph',
                    'field_label' => 'Can Choreograph',
                    'field_type' => 'radio',
                    'field_options' => [
                        ['label' => 'Yes', 'value' => 'yes'],
                        ['label' => 'No', 'value' => 'no'],
                    ],
                    'sort_order' => 3,
                ],
            ]);
        }
    }

    /**
     * Seed attributes for Vendor subcategories
     */
    private function seedVendorAttributes(): void
    {
        // Transportation attributes
        $transportSub = Subcategory::where('slug', 'transportation')->first();
        if ($transportSub) {
            $this->createAttributes($transportSub->id, [
                [
                    'field_name' => 'vehicle_types',
                    'field_label' => 'Vehicle Types Available',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getVehicleTypesOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'service_types',
                    'field_label' => 'Service Types',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getTransportServiceTypesOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'fleet_size',
                    'field_label' => 'Fleet Size',
                    'field_type' => 'number',
                    'min_value' => 1,
                    'max_value' => 500,
                    'unit' => 'vehicles',
                    'sort_order' => 3,
                ],
                [
                    'field_name' => 'coverage_areas',
                    'field_label' => 'Coverage Areas',
                    'field_type' => 'textarea',
                    'field_description' => 'List cities/regions you serve',
                    'max_length' => 500,
                    'sort_order' => 4,
                ],
            ]);
        }

        // Film Equipment attributes
        $equipmentSub = Subcategory::where('slug', 'film-equipment')->first();
        if ($equipmentSub) {
            $this->createAttributes($equipmentSub->id, [
                [
                    'field_name' => 'equipment_categories',
                    'field_label' => 'Equipment Categories',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getEquipmentCategoriesOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'rental_terms',
                    'field_label' => 'Rental Terms',
                    'field_type' => 'multiselect',
                    'field_options' => [
                        ['label' => 'Hourly', 'value' => 'hourly'],
                        ['label' => 'Daily', 'value' => 'daily'],
                        ['label' => 'Weekly', 'value' => 'weekly'],
                        ['label' => 'Monthly', 'value' => 'monthly'],
                    ],
                    'sort_order' => 2,
                ],
            ]);
        }
    }

    /**
     * Seed attributes for Wedding Filmmaker subcategories
     */
    private function seedWeddingFilmmakerAttributes(): void
    {
        // Event Management attributes
        $eventSub = Subcategory::where('slug', 'event-management')->first();
        if ($eventSub) {
            $this->createAttributes($eventSub->id, [
                [
                    'field_name' => 'event_types',
                    'field_label' => 'Event Types Managed',
                    'field_type' => 'multiselect',
                    'field_options' => $this->getEventTypesOptions(),
                    'is_required' => true,
                    'is_searchable' => true,
                    'sort_order' => 1,
                ],
                [
                    'field_name' => 'event_management_experience',
                    'field_label' => 'Years of Experience',
                    'field_type' => 'number',
                    'min_value' => 0,
                    'max_value' => 40,
                    'unit' => 'years',
                    'is_required' => true,
                    'sort_order' => 2,
                ],
                [
                    'field_name' => 'max_guest_capacity',
                    'field_label' => 'Maximum Guest Capacity',
                    'field_type' => 'select',
                    'field_options' => [
                        ['label' => 'Up to 50', 'value' => '50'],
                        ['label' => '50-100', 'value' => '100'],
                        ['label' => '100-200', 'value' => '200'],
                        ['label' => '200-500', 'value' => '500'],
                        ['label' => '500+', 'value' => '500plus'],
                    ],
                    'sort_order' => 3,
                ],
            ]);
        }
    }

    /**
     * Helper method to create attributes
     */
    private function createAttributes($subcategoryId, array $attributes): void
    {
        foreach ($attributes as $attr) {
            DB::table('subcategory_attributes')->insert(array_merge([
                'id' => Str::uuid()->toString(),
                'subcategory_id' => $subcategoryId,
                'is_required' => false,
                'is_searchable' => false,
                'is_public' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], $attr, [
                'field_options' => isset($attr['field_options']) ? json_encode($attr['field_options']) : null,
            ]));
        }
    }

    // Option getters
    private function getHeightOptions(): array
    {
        return [
            ['label' => '4\'6"', 'value' => '4_6'],
            ['label' => '4\'7"', 'value' => '4_7'],
            ['label' => '4\'8"', 'value' => '4_8'],
            ['label' => '4\'9"', 'value' => '4_9'],
            ['label' => '4\'10"', 'value' => '4_10'],
            ['label' => '4\'11"', 'value' => '4_11'],
            ['label' => '5\'0"', 'value' => '5_0'],
            ['label' => '5\'1"', 'value' => '5_1'],
            ['label' => '5\'2"', 'value' => '5_2'],
            ['label' => '5\'3"', 'value' => '5_3'],
            ['label' => '5\'4"', 'value' => '5_4'],
            ['label' => '5\'5"', 'value' => '5_5'],
            ['label' => '5\'6"', 'value' => '5_6'],
            ['label' => '5\'7"', 'value' => '5_7'],
            ['label' => '5\'8"', 'value' => '5_8'],
            ['label' => '5\'9"', 'value' => '5_9'],
            ['label' => '5\'10"', 'value' => '5_10'],
            ['label' => '5\'11"', 'value' => '5_11'],
            ['label' => '6\'0"', 'value' => '6_0'],
            ['label' => '6\'1"', 'value' => '6_1'],
            ['label' => '6\'2"', 'value' => '6_2'],
            ['label' => '6\'3"', 'value' => '6_3'],
            ['label' => '6\'4"', 'value' => '6_4'],
            ['label' => '6\'5"', 'value' => '6_5'],
            ['label' => '6\'6"', 'value' => '6_6'],
        ];
    }

    private function getWeightOptions(): array
    {
        $options = [];
        for ($i = 40; $i <= 120; $i += 5) {
            $max = $i + 4;
            $options[] = ['label' => "{$i}-{$max} Kg", 'value' => "{$i}_{$max}"];
        }
        return $options;
    }

    private function getSkinToneOptions(): array
    {
        return [
            ['label' => 'Fair', 'value' => 'fair'],
            ['label' => 'Light', 'value' => 'light'],
            ['label' => 'Medium', 'value' => 'medium'],
            ['label' => 'Olive', 'value' => 'olive'],
            ['label' => 'Tan', 'value' => 'tan'],
            ['label' => 'Brown', 'value' => 'brown'],
            ['label' => 'Dark', 'value' => 'dark'],
        ];
    }

    private function getEyeColorOptions(): array
    {
        return [
            ['label' => 'Brown', 'value' => 'brown'],
            ['label' => 'Blue', 'value' => 'blue'],
            ['label' => 'Green', 'value' => 'green'],
            ['label' => 'Hazel', 'value' => 'hazel'],
            ['label' => 'Gray', 'value' => 'gray'],
            ['label' => 'Amber', 'value' => 'amber'],
            ['label' => 'Black', 'value' => 'black'],
        ];
    }

    private function getHairColorOptions(): array
    {
        return [
            ['label' => 'Black', 'value' => 'black'],
            ['label' => 'Brown', 'value' => 'brown'],
            ['label' => 'Blonde', 'value' => 'blonde'],
            ['label' => 'Red', 'value' => 'red'],
            ['label' => 'Gray', 'value' => 'gray'],
            ['label' => 'White', 'value' => 'white'],
            ['label' => 'Other', 'value' => 'other'],
        ];
    }

    private function getBodyTypeOptions(): array
    {
        return [
            ['label' => 'Slim', 'value' => 'slim'],
            ['label' => 'Athletic', 'value' => 'athletic'],
            ['label' => 'Average', 'value' => 'average'],
            ['label' => 'Muscular', 'value' => 'muscular'],
            ['label' => 'Curvy', 'value' => 'curvy'],
            ['label' => 'Plus Size', 'value' => 'plus_size'],
        ];
    }

    private function getActingStylesOptions(): array
    {
        return [
            ['label' => 'Method Acting', 'value' => 'method'],
            ['label' => 'Classical', 'value' => 'classical'],
            ['label' => 'Comedy', 'value' => 'comedy'],
            ['label' => 'Drama', 'value' => 'drama'],
            ['label' => 'Action', 'value' => 'action'],
            ['label' => 'Voice Acting', 'value' => 'voice_acting'],
        ];
    }

    private function getModelingTypesOptions(): array
    {
        return [
            ['label' => 'Fashion', 'value' => 'fashion'],
            ['label' => 'Commercial', 'value' => 'commercial'],
            ['label' => 'Editorial', 'value' => 'editorial'],
            ['label' => 'Runway', 'value' => 'runway'],
            ['label' => 'Fitness', 'value' => 'fitness'],
            ['label' => 'Plus Size', 'value' => 'plus_size'],
        ];
    }

    private function getVocalRangeOptions(): array
    {
        return [
            ['label' => 'Soprano', 'value' => 'soprano'],
            ['label' => 'Mezzo-Soprano', 'value' => 'mezzo_soprano'],
            ['label' => 'Alto', 'value' => 'alto'],
            ['label' => 'Tenor', 'value' => 'tenor'],
            ['label' => 'Baritone', 'value' => 'baritone'],
            ['label' => 'Bass', 'value' => 'bass'],
        ];
    }

    private function getMusicGenresOptions(): array
    {
        return [
            ['label' => 'Pop', 'value' => 'pop'],
            ['label' => 'Rock', 'value' => 'rock'],
            ['label' => 'Jazz', 'value' => 'jazz'],
            ['label' => 'Classical', 'value' => 'classical'],
            ['label' => 'Country', 'value' => 'country'],
            ['label' => 'R&B', 'value' => 'rnb'],
            ['label' => 'Hip Hop', 'value' => 'hip_hop'],
            ['label' => 'Electronic', 'value' => 'electronic'],
        ];
    }

    private function getLanguagesOptions(): array
    {
        return [
            ['label' => 'English', 'value' => 'english'],
            ['label' => 'Arabic', 'value' => 'arabic'],
            ['label' => 'Hindi', 'value' => 'hindi'],
            ['label' => 'Urdu', 'value' => 'urdu'],
            ['label' => 'French', 'value' => 'french'],
            ['label' => 'Spanish', 'value' => 'spanish'],
        ];
    }

    private function getSocialPlatformsOptions(): array
    {
        return [
            ['label' => 'Instagram', 'value' => 'instagram'],
            ['label' => 'TikTok', 'value' => 'tiktok'],
            ['label' => 'YouTube', 'value' => 'youtube'],
            ['label' => 'Twitter/X', 'value' => 'twitter'],
        ];
    }

    private function getFollowerCountOptions(): array
    {
        return [
            ['label' => '1K - 10K', 'value' => '1k_10k'],
            ['label' => '10K - 50K', 'value' => '10k_50k'],
            ['label' => '50K - 100K', 'value' => '50k_100k'],
            ['label' => '100K - 500K', 'value' => '100k_500k'],
            ['label' => '500K - 1M', 'value' => '500k_1m'],
            ['label' => '1M+', 'value' => '1m_plus'],
        ];
    }

    private function getInfluencerNicheOptions(): array
    {
        return [
            ['label' => 'Fashion', 'value' => 'fashion'],
            ['label' => 'Beauty', 'value' => 'beauty'],
            ['label' => 'Fitness', 'value' => 'fitness'],
            ['label' => 'Travel', 'value' => 'travel'],
            ['label' => 'Food', 'value' => 'food'],
            ['label' => 'Tech', 'value' => 'tech'],
            ['label' => 'Lifestyle', 'value' => 'lifestyle'],
        ];
    }

    private function getPhotographyStylesOptions(): array
    {
        return [
            ['label' => 'Portrait', 'value' => 'portrait'],
            ['label' => 'Fashion', 'value' => 'fashion'],
            ['label' => 'Product', 'value' => 'product'],
            ['label' => 'Event', 'value' => 'event'],
            ['label' => 'Commercial', 'value' => 'commercial'],
            ['label' => 'Artistic', 'value' => 'artistic'],
        ];
    }

    private function getDanceStylesOptions(): array
    {
        return [
            ['label' => 'Ballet', 'value' => 'ballet'],
            ['label' => 'Contemporary', 'value' => 'contemporary'],
            ['label' => 'Hip Hop', 'value' => 'hip_hop'],
            ['label' => 'Jazz', 'value' => 'jazz'],
            ['label' => 'Salsa', 'value' => 'salsa'],
            ['label' => 'Ballroom', 'value' => 'ballroom'],
            ['label' => 'Bollywood', 'value' => 'bollywood'],
        ];
    }

    private function getVehicleTypesOptions(): array
    {
        return [
            ['label' => 'Car', 'value' => 'car'],
            ['label' => 'Van', 'value' => 'van'],
            ['label' => 'Bus', 'value' => 'bus'],
            ['label' => 'SUV', 'value' => 'suv'],
            ['label' => 'Truck', 'value' => 'truck'],
            ['label' => 'Limousine', 'value' => 'limousine'],
        ];
    }

    private function getTransportServiceTypesOptions(): array
    {
        return [
            ['label' => 'Shuttle', 'value' => 'shuttle'],
            ['label' => 'Car Rental', 'value' => 'car_rental'],
            ['label' => 'Package Rate', 'value' => 'package_rate'],
            ['label' => 'Hourly Rate', 'value' => 'hourly_rate'],
            ['label' => 'Daily Rate', 'value' => 'daily_rate'],
        ];
    }

    private function getEquipmentCategoriesOptions(): array
    {
        return [
            ['label' => 'Cameras', 'value' => 'cameras'],
            ['label' => 'Lenses', 'value' => 'lenses'],
            ['label' => 'Lighting', 'value' => 'lighting'],
            ['label' => 'Audio', 'value' => 'audio'],
            ['label' => 'Grip', 'value' => 'grip'],
            ['label' => 'Drones', 'value' => 'drones'],
        ];
    }

    private function getEventTypesOptions(): array
    {
        return [
            ['label' => 'Wedding', 'value' => 'wedding'],
            ['label' => 'Corporate Event', 'value' => 'corporate'],
            ['label' => 'Concert', 'value' => 'concert'],
            ['label' => 'Festival', 'value' => 'festival'],
            ['label' => 'Conference', 'value' => 'conference'],
            ['label' => 'Product Launch', 'value' => 'product_launch'],
        ];
    }
}