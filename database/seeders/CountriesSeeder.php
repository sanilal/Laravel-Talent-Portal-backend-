<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Focus on major countries, especially UAE, India, and GCC countries
     */
    public function run(): void
    {
        $countries = [
            ['id' => 1, 'country_name' => 'Afghanistan', 'country_code' => 'AF', 'country_code_alpha3' => 'AFG', 'dialing_code' => '+93', 'emoji' => '🇦🇫', 'currency' => 'AFN', 'currency_symbol' => '؋', 'numeric_code' => 4],
            ['id' => 2, 'country_name' => 'Albania', 'country_code' => 'AL', 'country_code_alpha3' => 'ALB', 'dialing_code' => '+355', 'emoji' => '🇦🇱', 'currency' => 'ALL', 'currency_symbol' => 'Lek', 'numeric_code' => 8],
            ['id' => 3, 'country_name' => 'Algeria', 'country_code' => 'DZ', 'country_code_alpha3' => 'DZA', 'dialing_code' => '+213', 'emoji' => '🇩🇿', 'currency' => 'DZD', 'currency_symbol' => 'د.ج', 'numeric_code' => 12],
            ['id' => 4, 'country_name' => 'Argentina', 'country_code' => 'AR', 'country_code_alpha3' => 'ARG', 'dialing_code' => '+54', 'emoji' => '🇦🇷', 'currency' => 'ARS', 'currency_symbol' => '$', 'numeric_code' => 32],
            ['id' => 5, 'country_name' => 'Australia', 'country_code' => 'AU', 'country_code_alpha3' => 'AUS', 'dialing_code' => '+61', 'emoji' => '🇦🇺', 'currency' => 'AUD', 'currency_symbol' => '$', 'numeric_code' => 36],
            ['id' => 6, 'country_name' => 'Austria', 'country_code' => 'AT', 'country_code_alpha3' => 'AUT', 'dialing_code' => '+43', 'emoji' => '🇦🇹', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 40],
            ['id' => 7, 'country_name' => 'Bahrain', 'country_code' => 'BH', 'country_code_alpha3' => 'BHR', 'dialing_code' => '+973', 'emoji' => '🇧🇭', 'currency' => 'BHD', 'currency_symbol' => '.د.ب', 'numeric_code' => 48],
            ['id' => 8, 'country_name' => 'Bangladesh', 'country_code' => 'BD', 'country_code_alpha3' => 'BGD', 'dialing_code' => '+880', 'emoji' => '🇧🇩', 'currency' => 'BDT', 'currency_symbol' => '৳', 'numeric_code' => 50],
            ['id' => 9, 'country_name' => 'Belgium', 'country_code' => 'BE', 'country_code_alpha3' => 'BEL', 'dialing_code' => '+32', 'emoji' => '🇧🇪', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 56],
            ['id' => 10, 'country_name' => 'Brazil', 'country_code' => 'BR', 'country_code_alpha3' => 'BRA', 'dialing_code' => '+55', 'emoji' => '🇧🇷', 'currency' => 'BRL', 'currency_symbol' => 'R$', 'numeric_code' => 76],
            ['id' => 11, 'country_name' => 'Canada', 'country_code' => 'CA', 'country_code_alpha3' => 'CAN', 'dialing_code' => '+1', 'emoji' => '🇨🇦', 'currency' => 'CAD', 'currency_symbol' => '$', 'numeric_code' => 124],
            ['id' => 12, 'country_name' => 'China', 'country_code' => 'CN', 'country_code_alpha3' => 'CHN', 'dialing_code' => '+86', 'emoji' => '🇨🇳', 'currency' => 'CNY', 'currency_symbol' => '¥', 'numeric_code' => 156],
            ['id' => 13, 'country_name' => 'Egypt', 'country_code' => 'EG', 'country_code_alpha3' => 'EGY', 'dialing_code' => '+20', 'emoji' => '🇪🇬', 'currency' => 'EGP', 'currency_symbol' => '£', 'numeric_code' => 818],
            ['id' => 14, 'country_name' => 'France', 'country_code' => 'FR', 'country_code_alpha3' => 'FRA', 'dialing_code' => '+33', 'emoji' => '🇫🇷', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 250],
            ['id' => 15, 'country_name' => 'Germany', 'country_code' => 'DE', 'country_code_alpha3' => 'DEU', 'dialing_code' => '+49', 'emoji' => '🇩🇪', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 276],
            ['id' => 16, 'country_name' => 'India', 'country_code' => 'IN', 'country_code_alpha3' => 'IND', 'dialing_code' => '+91', 'emoji' => '🇮🇳', 'currency' => 'INR', 'currency_symbol' => '₹', 'numeric_code' => 356],
            ['id' => 17, 'country_name' => 'Indonesia', 'country_code' => 'ID', 'country_code_alpha3' => 'IDN', 'dialing_code' => '+62', 'emoji' => '🇮🇩', 'currency' => 'IDR', 'currency_symbol' => 'Rp', 'numeric_code' => 360],
            ['id' => 18, 'country_name' => 'Iran', 'country_code' => 'IR', 'country_code_alpha3' => 'IRN', 'dialing_code' => '+98', 'emoji' => '🇮🇷', 'currency' => 'IRR', 'currency_symbol' => '﷼', 'numeric_code' => 364],
            ['id' => 19, 'country_name' => 'Iraq', 'country_code' => 'IQ', 'country_code_alpha3' => 'IRQ', 'dialing_code' => '+964', 'emoji' => '🇮🇶', 'currency' => 'IQD', 'currency_symbol' => 'ع.د', 'numeric_code' => 368],
            ['id' => 20, 'country_name' => 'Israel', 'country_code' => 'IL', 'country_code_alpha3' => 'ISR', 'dialing_code' => '+972', 'emoji' => '🇮🇱', 'currency' => 'ILS', 'currency_symbol' => '₪', 'numeric_code' => 376],
            ['id' => 21, 'country_name' => 'Italy', 'country_code' => 'IT', 'country_code_alpha3' => 'ITA', 'dialing_code' => '+39', 'emoji' => '🇮🇹', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 380],
            ['id' => 22, 'country_name' => 'Japan', 'country_code' => 'JP', 'country_code_alpha3' => 'JPN', 'dialing_code' => '+81', 'emoji' => '🇯🇵', 'currency' => 'JPY', 'currency_symbol' => '¥', 'numeric_code' => 392],
            ['id' => 23, 'country_name' => 'Jordan', 'country_code' => 'JO', 'country_code_alpha3' => 'JOR', 'dialing_code' => '+962', 'emoji' => '🇯🇴', 'currency' => 'JOD', 'currency_symbol' => 'د.ا', 'numeric_code' => 400],
            ['id' => 24, 'country_name' => 'Kuwait', 'country_code' => 'KW', 'country_code_alpha3' => 'KWT', 'dialing_code' => '+965', 'emoji' => '🇰🇼', 'currency' => 'KWD', 'currency_symbol' => 'د.ك', 'numeric_code' => 414],
            ['id' => 25, 'country_name' => 'Lebanon', 'country_code' => 'LB', 'country_code_alpha3' => 'LBN', 'dialing_code' => '+961', 'emoji' => '🇱🇧', 'currency' => 'LBP', 'currency_symbol' => 'ل.ل', 'numeric_code' => 422],
            ['id' => 26, 'country_name' => 'Malaysia', 'country_code' => 'MY', 'country_code_alpha3' => 'MYS', 'dialing_code' => '+60', 'emoji' => '🇲🇾', 'currency' => 'MYR', 'currency_symbol' => 'RM', 'numeric_code' => 458],
            ['id' => 27, 'country_name' => 'Mexico', 'country_code' => 'MX', 'country_code_alpha3' => 'MEX', 'dialing_code' => '+52', 'emoji' => '🇲🇽', 'currency' => 'MXN', 'currency_symbol' => '$', 'numeric_code' => 484],
            ['id' => 28, 'country_name' => 'Morocco', 'country_code' => 'MA', 'country_code_alpha3' => 'MAR', 'dialing_code' => '+212', 'emoji' => '🇲🇦', 'currency' => 'MAD', 'currency_symbol' => 'د.م.', 'numeric_code' => 504],
            ['id' => 29, 'country_name' => 'Netherlands', 'country_code' => 'NL', 'country_code_alpha3' => 'NLD', 'dialing_code' => '+31', 'emoji' => '🇳🇱', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 528],
            ['id' => 30, 'country_name' => 'New Zealand', 'country_code' => 'NZ', 'country_code_alpha3' => 'NZL', 'dialing_code' => '+64', 'emoji' => '🇳🇿', 'currency' => 'NZD', 'currency_symbol' => '$', 'numeric_code' => 554],
            ['id' => 31, 'country_name' => 'Nigeria', 'country_code' => 'NG', 'country_code_alpha3' => 'NGA', 'dialing_code' => '+234', 'emoji' => '🇳🇬', 'currency' => 'NGN', 'currency_symbol' => '₦', 'numeric_code' => 566],
            ['id' => 32, 'country_name' => 'Norway', 'country_code' => 'NO', 'country_code_alpha3' => 'NOR', 'dialing_code' => '+47', 'emoji' => '🇳🇴', 'currency' => 'NOK', 'currency_symbol' => 'kr', 'numeric_code' => 578],
            ['id' => 33, 'country_name' => 'Oman', 'country_code' => 'OM', 'country_code_alpha3' => 'OMN', 'dialing_code' => '+968', 'emoji' => '🇴🇲', 'currency' => 'OMR', 'currency_symbol' => 'ر.ع.', 'numeric_code' => 512],
            ['id' => 34, 'country_name' => 'Pakistan', 'country_code' => 'PK', 'country_code_alpha3' => 'PAK', 'dialing_code' => '+92', 'emoji' => '🇵🇰', 'currency' => 'PKR', 'currency_symbol' => '₨', 'numeric_code' => 586],
            ['id' => 35, 'country_name' => 'Palestine', 'country_code' => 'PS', 'country_code_alpha3' => 'PSE', 'dialing_code' => '+970', 'emoji' => '🇵🇸', 'currency' => 'ILS', 'currency_symbol' => '₪', 'numeric_code' => 275],
            ['id' => 36, 'country_name' => 'Philippines', 'country_code' => 'PH', 'country_code_alpha3' => 'PHL', 'dialing_code' => '+63', 'emoji' => '🇵🇭', 'currency' => 'PHP', 'currency_symbol' => '₱', 'numeric_code' => 608],
            ['id' => 37, 'country_name' => 'Qatar', 'country_code' => 'QA', 'country_code_alpha3' => 'QAT', 'dialing_code' => '+974', 'emoji' => '🇶🇦', 'currency' => 'QAR', 'currency_symbol' => 'ر.ق', 'numeric_code' => 634],
            ['id' => 38, 'country_name' => 'Russia', 'country_code' => 'RU', 'country_code_alpha3' => 'RUS', 'dialing_code' => '+7', 'emoji' => '🇷🇺', 'currency' => 'RUB', 'currency_symbol' => '₽', 'numeric_code' => 643],
            ['id' => 39, 'country_name' => 'Saudi Arabia', 'country_code' => 'SA', 'country_code_alpha3' => 'SAU', 'dialing_code' => '+966', 'emoji' => '🇸🇦', 'currency' => 'SAR', 'currency_symbol' => 'ر.س', 'numeric_code' => 682],
            ['id' => 40, 'country_name' => 'Singapore', 'country_code' => 'SG', 'country_code_alpha3' => 'SGP', 'dialing_code' => '+65', 'emoji' => '🇸🇬', 'currency' => 'SGD', 'currency_symbol' => '$', 'numeric_code' => 702],
            ['id' => 41, 'country_name' => 'South Africa', 'country_code' => 'ZA', 'country_code_alpha3' => 'ZAF', 'dialing_code' => '+27', 'emoji' => '🇿🇦', 'currency' => 'ZAR', 'currency_symbol' => 'R', 'numeric_code' => 710],
            ['id' => 42, 'country_name' => 'South Korea', 'country_code' => 'KR', 'country_code_alpha3' => 'KOR', 'dialing_code' => '+82', 'emoji' => '🇰🇷', 'currency' => 'KRW', 'currency_symbol' => '₩', 'numeric_code' => 410],
            ['id' => 43, 'country_name' => 'Spain', 'country_code' => 'ES', 'country_code_alpha3' => 'ESP', 'dialing_code' => '+34', 'emoji' => '🇪🇸', 'currency' => 'EUR', 'currency_symbol' => '€', 'numeric_code' => 724],
            ['id' => 44, 'country_name' => 'Sri Lanka', 'country_code' => 'LK', 'country_code_alpha3' => 'LKA', 'dialing_code' => '+94', 'emoji' => '🇱🇰', 'currency' => 'LKR', 'currency_symbol' => 'Rs', 'numeric_code' => 144],
            ['id' => 45, 'country_name' => 'Sweden', 'country_code' => 'SE', 'country_code_alpha3' => 'SWE', 'dialing_code' => '+46', 'emoji' => '🇸🇪', 'currency' => 'SEK', 'currency_symbol' => 'kr', 'numeric_code' => 752],
            ['id' => 46, 'country_name' => 'Switzerland', 'country_code' => 'CH', 'country_code_alpha3' => 'CHE', 'dialing_code' => '+41', 'emoji' => '🇨🇭', 'currency' => 'CHF', 'currency_symbol' => 'Fr', 'numeric_code' => 756],
            ['id' => 47, 'country_name' => 'Syria', 'country_code' => 'SY', 'country_code_alpha3' => 'SYR', 'dialing_code' => '+963', 'emoji' => '🇸🇾', 'currency' => 'SYP', 'currency_symbol' => '£', 'numeric_code' => 760],
            ['id' => 48, 'country_name' => 'Thailand', 'country_code' => 'TH', 'country_code_alpha3' => 'THA', 'dialing_code' => '+66', 'emoji' => '🇹🇭', 'currency' => 'THB', 'currency_symbol' => '฿', 'numeric_code' => 764],
            ['id' => 49, 'country_name' => 'Turkey', 'country_code' => 'TR', 'country_code_alpha3' => 'TUR', 'dialing_code' => '+90', 'emoji' => '🇹🇷', 'currency' => 'TRY', 'currency_symbol' => '₺', 'numeric_code' => 792],
            ['id' => 50, 'country_name' => 'United Arab Emirates', 'country_code' => 'AE', 'country_code_alpha3' => 'ARE', 'dialing_code' => '+971', 'emoji' => '🇦🇪', 'currency' => 'AED', 'currency_symbol' => 'د.إ', 'numeric_code' => 784],
            ['id' => 51, 'country_name' => 'United Kingdom', 'country_code' => 'GB', 'country_code_alpha3' => 'GBR', 'dialing_code' => '+44', 'emoji' => '🇬🇧', 'currency' => 'GBP', 'currency_symbol' => '£', 'numeric_code' => 826],
            ['id' => 52, 'country_name' => 'United States', 'country_code' => 'US', 'country_code_alpha3' => 'USA', 'dialing_code' => '+1', 'emoji' => '🇺🇸', 'currency' => 'USD', 'currency_symbol' => '$', 'numeric_code' => 840],
            ['id' => 53, 'country_name' => 'Vietnam', 'country_code' => 'VN', 'country_code_alpha3' => 'VNM', 'dialing_code' => '+84', 'emoji' => '🇻🇳', 'currency' => 'VND', 'currency_symbol' => '₫', 'numeric_code' => 704],
            ['id' => 54, 'country_name' => 'Yemen', 'country_code' => 'YE', 'country_code_alpha3' => 'YEM', 'dialing_code' => '+967', 'emoji' => '🇾🇪', 'currency' => 'YER', 'currency_symbol' => '﷼', 'numeric_code' => 887],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->insert(array_merge($country, [
                'is_active' => true,
                'sort_order' => $country['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Countries seeded successfully!');
        $this->command->info('Total countries: ' . count($countries));
    }
}