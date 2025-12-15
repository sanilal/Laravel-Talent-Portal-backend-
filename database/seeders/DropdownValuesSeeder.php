<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DropdownValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Dropdown Types:
     * 1 = Height
     * 2 = Skin Tone
     * 3 = Weight
     * 4 = Age Range
     * 5 = Vehicle Type
     * 6 = Service Type
     * 7 = Event Type
     * 8 = Budget Range
     * 9 = Eye Color
     * 10 = Hair Color
     * 11 = Body Type
     * 12 = Vocal Range
     * 13 = Experience Level
     * 14 = Language Proficiency
     * 15 = Gender
     */
    public function run(): void
    {
        $dropdownValues = [];

        // Type 1: Heights
        $heights = [
            "4'6\"", "4'7\"", "4'8\"", "4'9\"", "4'10\"", "4'11\"",
            "5'0\"", "5'1\"", "5'2\"", "5'3\"", "5'4\"", "5'5\"", "5'6\"", "5'7\"", "5'8\"", "5'9\"", "5'10\"", "5'11\"",
            "6'0\"", "6'1\"", "6'2\"", "6'3\"", "6'4\"", "6'5\"", "6'6\"", "6'7\"", "6'8\"", "6'9\"", "6'10\"", "6'11\"",
            "7'0\" and above"
        ];
        foreach ($heights as $index => $height) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 1,
                'value' => $height,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 2: Skin Tones
        $skinTones = ['Fair', 'Light', 'Medium', 'Olive', 'Tan', 'Brown', 'Dark'];
        foreach ($skinTones as $index => $tone) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 2,
                'value' => $tone,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 3: Weight Ranges (in Kg)
        $weightRanges = [
            '20-25 Kg', '25-30 Kg', '30-35 Kg', '35-40 Kg', '40-45 Kg', '45-50 Kg',
            '50-55 Kg', '55-60 Kg', '60-65 Kg', '65-70 Kg', '70-75 Kg', '75-80 Kg',
            '80-85 Kg', '85-90 Kg', '90-95 Kg', '95-100 Kg', '100-105 Kg', '105-110 Kg',
            '110-115 Kg', '115-120 Kg', '120-125 Kg', '125-130 Kg', '130-135 Kg', '135-140 Kg',
            '140-145 Kg', '145-150 Kg', '150-155 Kg', '155-160 Kg', '160-165 Kg', '165-170 Kg',
            '170-175 Kg', '175-180 Kg', '180-185 Kg', '185-190 Kg', '190-195 Kg', '195-200 Kg'
        ];
        foreach ($weightRanges as $index => $range) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 3,
                'value' => $range,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 4: Age Ranges
        $ageRanges = [
            '0-5', '5-10', '10-15', '15-20', '20-25', '25-30', '30-35', '35-40', '40-45', '45-50',
            '50-55', '55-60', '60-65', '65-70', '70-75', '75-80', '80-85', '85-90', '90-95', '95-100',
            '100 and above'
        ];
        foreach ($ageRanges as $index => $range) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 4,
                'value' => $range,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 5: Vehicle Types (for Transportation vendors)
        $vehicleTypes = ['Car', 'Van', 'Bus', 'SUV', 'Truck', 'Limousine', 'Motorcycle'];
        foreach ($vehicleTypes as $index => $type) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 5,
                'value' => $type,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 6: Service Types (for Transportation vendors)
        $serviceTypes = ['Shuttle', 'Car Rental', 'Package Rate', 'Hourly Rate', 'Daily Rate'];
        foreach ($serviceTypes as $index => $type) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 6,
                'value' => $type,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 7: Event Types (for Event Management)
        $eventTypes = [
            'Wedding', 'Corporate Event', 'Concert', 'Festival', 'Conference', 
            'Exhibition', 'Product Launch', 'Birthday Party', 'Anniversary', 'Other'
        ];
        foreach ($eventTypes as $index => $type) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 7,
                'value' => $type,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 8: Budget Ranges (in AED/INR) - WITH EXTRA FIELDS
        $budgetRanges = [
            ['value' => '0-10000', 'min' => 0, 'max' => 10000],
            ['value' => '10000-25000', 'min' => 10000, 'max' => 25000],
            ['value' => '25000-50000', 'min' => 25000, 'max' => 50000],
            ['value' => '50000-100000', 'min' => 50000, 'max' => 100000],
            ['value' => '100000-500000', 'min' => 100000, 'max' => 500000],
            ['value' => '500000-Above', 'min' => 500000, 'max' => null],
        ];
        foreach ($budgetRanges as $index => $range) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 8,
                'value' => $range['value'],
                'value_secondary' => (string) $range['min'], // Convert to string
                'code' => $range['max'] !== null ? (string) $range['max'] : null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => json_encode(['min' => $range['min'], 'max' => $range['max']]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 9: Eye Colors
        $eyeColors = ['Brown', 'Blue', 'Green', 'Hazel', 'Gray', 'Amber', 'Black'];
        foreach ($eyeColors as $index => $color) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 9,
                'value' => $color,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 10: Hair Colors
        $hairColors = ['Black', 'Brown', 'Blonde', 'Red', 'Gray', 'White', 'Other'];
        foreach ($hairColors as $index => $color) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 10,
                'value' => $color,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 11: Body Types
        $bodyTypes = ['Slim', 'Athletic', 'Average', 'Muscular', 'Curvy', 'Plus Size'];
        foreach ($bodyTypes as $index => $type) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 11,
                'value' => $type,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 12: Vocal Ranges (for Singers)
        $vocalRanges = ['Soprano', 'Mezzo-Soprano', 'Alto', 'Tenor', 'Baritone', 'Bass'];
        foreach ($vocalRanges as $index => $range) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 12,
                'value' => $range,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 13: Experience Levels
        $experienceLevels = ['Beginner', 'Intermediate', 'Advanced', 'Expert', 'Professional'];
        foreach ($experienceLevels as $index => $level) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 13,
                'value' => $level,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 14: Language Proficiency
        $proficiencyLevels = ['Basic', 'Conversational', 'Fluent', 'Native'];
        foreach ($proficiencyLevels as $index => $level) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 14,
                'value' => $level,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Type 15: Gender
        $genders = ['Male', 'Female', 'Non-Binary', 'Other'];
        foreach ($genders as $index => $gender) {
            $dropdownValues[] = [
                'id' => Str::uuid()->toString(),
                'type' => 15,
                'value' => $gender,
                'value_secondary' => null,
                'code' => null,
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert all dropdown values
        foreach (array_chunk($dropdownValues, 100) as $chunk) {
            DB::table('dropdown_values')->insert($chunk);
        }

        $this->command->info('Dropdown values seeded successfully!');
        $this->command->info('Total values seeded: ' . count($dropdownValues));
    }
}