<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CastingCallDropdownValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Type codes (as variables)
        $TYPE_HEIGHT = 1;
        $TYPE_SKIN_TONE = 2;
        $TYPE_AGE_GROUP = 4;
        $TYPE_GENDER = 5;

        $dropdowns = [];

        // Age Groups
        $ageGroups = [
            '0-5', '5-10', '10-15', '15-20', '20-25', '25-30', '30-35', '35-40',
            '40-45', '45-50', '50-55', '55-60', '60-65', '65-70', '70-75', '75-80',
            '80-85', '85-90', '90-95', '95-100', '100 and above'
        ];

        foreach ($ageGroups as $index => $ageGroup) {
            $dropdowns[] = [
                'id' => Str::uuid()->toString(),
                'type' => $TYPE_AGE_GROUP,
                'value' => $ageGroup,
                'display_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Skin Tones
        $skinTones = ['Fair', 'Light', 'Medium', 'Olive', 'Tan', 'Brown', 'Dark'];
        
        foreach ($skinTones as $index => $skinTone) {
            $dropdowns[] = [
                'id' => Str::uuid()->toString(),
                'type' => $TYPE_SKIN_TONE,
                'value' => $skinTone,
                'display_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Heights
        $heights = [
            "4'6\"", "4'7\"", "4'8\"", "4'9\"", "4'10\"", "4'11\"",
            "5'0\"", "5'1\"", "5'2\"", "5'3\"", "5'4\"", "5'5\"", "5'6\"",
            "5'7\"", "5'8\"", "5'9\"", "5'10\"", "5'11\"",
            "6'0\"", "6'1\"", "6'2\"", "6'3\"", "6'4\"", "6'5\"", "6'6\"",
            "6'7\"", "6'8\"", "6'9\"", "6'10\"", "6'11\"", "7'0\" and above"
        ];

        foreach ($heights as $index => $height) {
            $dropdowns[] = [
                'id' => Str::uuid()->toString(),
                'type' => $TYPE_HEIGHT,
                'value' => $height,
                'display_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Genders
        $genders = ['Male', 'Female', 'Non-Binary', 'Any'];
        
        foreach ($genders as $index => $gender) {
            $dropdowns[] = [
                'id' => Str::uuid()->toString(),
                'type' => $TYPE_GENDER,
                'value' => $gender,
                'display_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('dropdown_values')->insert($dropdowns);
        
        $this->command->info('Casting call dropdown values seeded successfully!');
        $this->command->info('Age Groups: ' . count($ageGroups));
        $this->command->info('Skin Tones: ' . count($skinTones));
        $this->command->info('Heights: ' . count($heights));
        $this->command->info('Genders: ' . count($genders));
    }
}