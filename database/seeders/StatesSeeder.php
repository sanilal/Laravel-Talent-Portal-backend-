<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Focus on UAE (id=50), India (id=16), and other GCC countries
     */
    public function run(): void
    {
        $states = [
            // United Arab Emirates (country_id = 50)
            ['country_id' => 50, 'state_name' => 'Abu Dhabi', 'state_code' => 'AZ'],
            ['country_id' => 50, 'state_name' => 'Ajman', 'state_code' => 'AJ'],
            ['country_id' => 50, 'state_name' => 'Dubai', 'state_code' => 'DU'],
            ['country_id' => 50, 'state_name' => 'Fujairah', 'state_code' => 'FU'],
            ['country_id' => 50, 'state_name' => 'Ras Al Khaimah', 'state_code' => 'RK'],
            ['country_id' => 50, 'state_name' => 'Sharjah', 'state_code' => 'SH'],
            ['country_id' => 50, 'state_name' => 'Umm Al Quwain', 'state_code' => 'UQ'],

            // India (country_id = 16) - All states and UTs
            ['country_id' => 16, 'state_name' => 'Andhra Pradesh', 'state_code' => 'AP'],
            ['country_id' => 16, 'state_name' => 'Arunachal Pradesh', 'state_code' => 'AR'],
            ['country_id' => 16, 'state_name' => 'Assam', 'state_code' => 'AS'],
            ['country_id' => 16, 'state_name' => 'Bihar', 'state_code' => 'BR'],
            ['country_id' => 16, 'state_name' => 'Chhattisgarh', 'state_code' => 'CG'],
            ['country_id' => 16, 'state_name' => 'Goa', 'state_code' => 'GA'],
            ['country_id' => 16, 'state_name' => 'Gujarat', 'state_code' => 'GJ'],
            ['country_id' => 16, 'state_name' => 'Haryana', 'state_code' => 'HR'],
            ['country_id' => 16, 'state_name' => 'Himachal Pradesh', 'state_code' => 'HP'],
            ['country_id' => 16, 'state_name' => 'Jharkhand', 'state_code' => 'JH'],
            ['country_id' => 16, 'state_name' => 'Karnataka', 'state_code' => 'KA'],
            ['country_id' => 16, 'state_name' => 'Kerala', 'state_code' => 'KL'],
            ['country_id' => 16, 'state_name' => 'Madhya Pradesh', 'state_code' => 'MP'],
            ['country_id' => 16, 'state_name' => 'Maharashtra', 'state_code' => 'MH'],
            ['country_id' => 16, 'state_name' => 'Manipur', 'state_code' => 'MN'],
            ['country_id' => 16, 'state_name' => 'Meghalaya', 'state_code' => 'ML'],
            ['country_id' => 16, 'state_name' => 'Mizoram', 'state_code' => 'MZ'],
            ['country_id' => 16, 'state_name' => 'Nagaland', 'state_code' => 'NL'],
            ['country_id' => 16, 'state_name' => 'Odisha', 'state_code' => 'OR'],
            ['country_id' => 16, 'state_name' => 'Punjab', 'state_code' => 'PB'],
            ['country_id' => 16, 'state_name' => 'Rajasthan', 'state_code' => 'RJ'],
            ['country_id' => 16, 'state_name' => 'Sikkim', 'state_code' => 'SK'],
            ['country_id' => 16, 'state_name' => 'Tamil Nadu', 'state_code' => 'TN'],
            ['country_id' => 16, 'state_name' => 'Telangana', 'state_code' => 'TG'],
            ['country_id' => 16, 'state_name' => 'Tripura', 'state_code' => 'TR'],
            ['country_id' => 16, 'state_name' => 'Uttar Pradesh', 'state_code' => 'UP'],
            ['country_id' => 16, 'state_name' => 'Uttarakhand', 'state_code' => 'UK'],
            ['country_id' => 16, 'state_name' => 'West Bengal', 'state_code' => 'WB'],
            ['country_id' => 16, 'state_name' => 'Andaman and Nicobar Islands', 'state_code' => 'AN'],
            ['country_id' => 16, 'state_name' => 'Chandigarh', 'state_code' => 'CH'],
            ['country_id' => 16, 'state_name' => 'Dadra and Nagar Haveli and Daman and Diu', 'state_code' => 'DH'],
            ['country_id' => 16, 'state_name' => 'Delhi', 'state_code' => 'DL'],
            ['country_id' => 16, 'state_name' => 'Jammu and Kashmir', 'state_code' => 'JK'],
            ['country_id' => 16, 'state_name' => 'Ladakh', 'state_code' => 'LA'],
            ['country_id' => 16, 'state_name' => 'Lakshadweep', 'state_code' => 'LD'],
            ['country_id' => 16, 'state_name' => 'Puducherry', 'state_code' => 'PY'],

            // Bahrain (country_id = 7)
            ['country_id' => 7, 'state_name' => 'Capital', 'state_code' => 'CAP'],
            ['country_id' => 7, 'state_name' => 'Central', 'state_code' => 'CEN'],
            ['country_id' => 7, 'state_name' => 'Muharraq', 'state_code' => 'MUH'],
            ['country_id' => 7, 'state_name' => 'Northern', 'state_code' => 'NOR'],
            ['country_id' => 7, 'state_name' => 'Southern', 'state_code' => 'SOU'],

            // Saudi Arabia (country_id = 39) - Major regions
            ['country_id' => 39, 'state_name' => 'Riyadh', 'state_code' => 'RI'],
            ['country_id' => 39, 'state_name' => 'Makkah', 'state_code' => 'MK'],
            ['country_id' => 39, 'state_name' => 'Madinah', 'state_code' => 'MD'],
            ['country_id' => 39, 'state_name' => 'Eastern Province', 'state_code' => 'EP'],
            ['country_id' => 39, 'state_name' => 'Asir', 'state_code' => 'AS'],
            ['country_id' => 39, 'state_name' => 'Tabuk', 'state_code' => 'TB'],
            ['country_id' => 39, 'state_name' => 'Hail', 'state_code' => 'HA'],
            ['country_id' => 39, 'state_name' => 'Jizan', 'state_code' => 'JZ'],
            ['country_id' => 39, 'state_name' => 'Najran', 'state_code' => 'NJ'],
            ['country_id' => 39, 'state_name' => 'Al-Qassim', 'state_code' => 'QS'],

            // Kuwait (country_id = 24)
            ['country_id' => 24, 'state_name' => 'Al Ahmadi', 'state_code' => 'AH'],
            ['country_id' => 24, 'state_name' => 'Al Farwaniyah', 'state_code' => 'FA'],
            ['country_id' => 24, 'state_name' => 'Al Jahra', 'state_code' => 'JA'],
            ['country_id' => 24, 'state_name' => 'Capital', 'state_code' => 'CA'],
            ['country_id' => 24, 'state_name' => 'Hawalli', 'state_code' => 'HA'],
            ['country_id' => 24, 'state_name' => 'Mubarak Al-Kabeer', 'state_code' => 'MU'],

            // Oman (country_id = 33)
            ['country_id' => 33, 'state_name' => 'Muscat', 'state_code' => 'MU'],
            ['country_id' => 33, 'state_name' => 'Dhofar', 'state_code' => 'DH'],
            ['country_id' => 33, 'state_name' => 'Musandam', 'state_code' => 'MN'],
            ['country_id' => 33, 'state_name' => 'Al Buraimi', 'state_code' => 'BR'],
            ['country_id' => 33, 'state_name' => 'Ad Dakhiliyah', 'state_code' => 'DA'],
            ['country_id' => 33, 'state_name' => 'Al Batinah North', 'state_code' => 'BN'],
            ['country_id' => 33, 'state_name' => 'Al Batinah South', 'state_code' => 'BS'],

            // Qatar (country_id = 37)
            ['country_id' => 37, 'state_name' => 'Doha', 'state_code' => 'DO'],
            ['country_id' => 37, 'state_name' => 'Al Rayyan', 'state_code' => 'RA'],
            ['country_id' => 37, 'state_name' => 'Al Wakrah', 'state_code' => 'WA'],
            ['country_id' => 37, 'state_name' => 'Al Khor', 'state_code' => 'KH'],
            ['country_id' => 37, 'state_name' => 'Al Shamal', 'state_code' => 'SH'],

            // United States (country_id = 52) - Major states
            ['country_id' => 52, 'state_name' => 'California', 'state_code' => 'CA'],
            ['country_id' => 52, 'state_name' => 'New York', 'state_code' => 'NY'],
            ['country_id' => 52, 'state_name' => 'Texas', 'state_code' => 'TX'],
            ['country_id' => 52, 'state_name' => 'Florida', 'state_code' => 'FL'],
            ['country_id' => 52, 'state_name' => 'Illinois', 'state_code' => 'IL'],
            ['country_id' => 52, 'state_name' => 'Pennsylvania', 'state_code' => 'PA'],
            ['country_id' => 52, 'state_name' => 'Ohio', 'state_code' => 'OH'],
            ['country_id' => 52, 'state_name' => 'Georgia', 'state_code' => 'GA'],
            ['country_id' => 52, 'state_name' => 'North Carolina', 'state_code' => 'NC'],
            ['country_id' => 52, 'state_name' => 'Michigan', 'state_code' => 'MI'],

            // United Kingdom (country_id = 51)
            ['country_id' => 51, 'state_name' => 'England', 'state_code' => 'ENG'],
            ['country_id' => 51, 'state_name' => 'Scotland', 'state_code' => 'SCT'],
            ['country_id' => 51, 'state_name' => 'Wales', 'state_code' => 'WLS'],
            ['country_id' => 51, 'state_name' => 'Northern Ireland', 'state_code' => 'NIR'],

            // Canada (country_id = 11)
            ['country_id' => 11, 'state_name' => 'Ontario', 'state_code' => 'ON'],
            ['country_id' => 11, 'state_name' => 'Quebec', 'state_code' => 'QC'],
            ['country_id' => 11, 'state_name' => 'British Columbia', 'state_code' => 'BC'],
            ['country_id' => 11, 'state_name' => 'Alberta', 'state_code' => 'AB'],
            ['country_id' => 11, 'state_name' => 'Manitoba', 'state_code' => 'MB'],
            ['country_id' => 11, 'state_name' => 'Saskatchewan', 'state_code' => 'SK'],

            // Australia (country_id = 5)
            ['country_id' => 5, 'state_name' => 'New South Wales', 'state_code' => 'NSW'],
            ['country_id' => 5, 'state_name' => 'Victoria', 'state_code' => 'VIC'],
            ['country_id' => 5, 'state_name' => 'Queensland', 'state_code' => 'QLD'],
            ['country_id' => 5, 'state_name' => 'Western Australia', 'state_code' => 'WA'],
            ['country_id' => 5, 'state_name' => 'South Australia', 'state_code' => 'SA'],
            ['country_id' => 5, 'state_name' => 'Tasmania', 'state_code' => 'TAS'],
        ];

        $id = 1;
        foreach ($states as $state) {
            DB::table('states')->insert(array_merge($state, [
                'id' => $id++,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('States seeded successfully!');
        $this->command->info('Total states: ' . count($states));
    }
}