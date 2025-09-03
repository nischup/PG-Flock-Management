<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('countries')->insert([
            ['name' => 'Afghanistan', 'iso2' => 'AF', 'iso3' => 'AFG', 'phone_code' => '+93'],
            ['name' => 'Albania', 'iso2' => 'AL', 'iso3' => 'ALB', 'phone_code' => '+355'],
            ['name' => 'Algeria', 'iso2' => 'DZ', 'iso3' => 'DZA', 'phone_code' => '+213'],
            ['name' => 'Andorra', 'iso2' => 'AD', 'iso3' => 'AND', 'phone_code' => '+376'],
            ['name' => 'Angola', 'iso2' => 'AO', 'iso3' => 'AGO', 'phone_code' => '+244'],
            ['name' => 'Argentina', 'iso2' => 'AR', 'iso3' => 'ARG', 'phone_code' => '+54'],
            ['name' => 'Armenia', 'iso2' => 'AM', 'iso3' => 'ARM', 'phone_code' => '+374'],
            ['name' => 'Australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'phone_code' => '+61'],
            ['name' => 'Austria', 'iso2' => 'AT', 'iso3' => 'AUT', 'phone_code' => '+43'],
            ['name' => 'Azerbaijan', 'iso2' => 'AZ', 'iso3' => 'AZE', 'phone_code' => '+994'],
            ['name' => 'Bahamas', 'iso2' => 'BS', 'iso3' => 'BHS', 'phone_code' => '+1-242'],
            ['name' => 'Bahrain', 'iso2' => 'BH', 'iso3' => 'BHR', 'phone_code' => '+973'],
            ['name' => 'Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'phone_code' => '+880'],
            ['name' => 'Belgium', 'iso2' => 'BE', 'iso3' => 'BEL', 'phone_code' => '+32'],
            ['name' => 'Brazil', 'iso2' => 'BR', 'iso3' => 'BRA', 'phone_code' => '+55'],
            ['name' => 'Canada', 'iso2' => 'CA', 'iso3' => 'CAN', 'phone_code' => '+1'],
            ['name' => 'China', 'iso2' => 'CN', 'iso3' => 'CHN', 'phone_code' => '+86'],
            ['name' => 'Denmark', 'iso2' => 'DK', 'iso3' => 'DNK', 'phone_code' => '+45'],
            ['name' => 'Egypt', 'iso2' => 'EG', 'iso3' => 'EGY', 'phone_code' => '+20'],
            ['name' => 'France', 'iso2' => 'FR', 'iso3' => 'FRA', 'phone_code' => '+33'],
            ['name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU', 'phone_code' => '+49'],
            ['name' => 'India', 'iso2' => 'IN', 'iso3' => 'IND', 'phone_code' => '+91'],
            ['name' => 'Indonesia', 'iso2' => 'ID', 'iso3' => 'IDN', 'phone_code' => '+62'],
            ['name' => 'Italy', 'iso2' => 'IT', 'iso3' => 'ITA', 'phone_code' => '+39'],
            ['name' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN', 'phone_code' => '+81'],
            ['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'phone_code' => '+60'],
            ['name' => 'Mexico', 'iso2' => 'MX', 'iso3' => 'MEX', 'phone_code' => '+52'],
            ['name' => 'Nepal', 'iso2' => 'NP', 'iso3' => 'NPL', 'phone_code' => '+977'],
            ['name' => 'Netherlands', 'iso2' => 'NL', 'iso3' => 'NLD', 'phone_code' => '+31'],
            ['name' => 'Pakistan', 'iso2' => 'PK', 'iso3' => 'PAK', 'phone_code' => '+92'],
            ['name' => 'Russia', 'iso2' => 'RU', 'iso3' => 'RUS', 'phone_code' => '+7'],
            ['name' => 'Saudi Arabia', 'iso2' => 'SA', 'iso3' => 'SAU', 'phone_code' => '+966'],
            ['name' => 'Singapore', 'iso2' => 'SG', 'iso3' => 'SGP', 'phone_code' => '+65'],
            ['name' => 'South Africa', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'phone_code' => '+27'],
            ['name' => 'Spain', 'iso2' => 'ES', 'iso3' => 'ESP', 'phone_code' => '+34'],
            ['name' => 'Sri Lanka', 'iso2' => 'LK', 'iso3' => 'LKA', 'phone_code' => '+94'],
            ['name' => 'Sweden', 'iso2' => 'SE', 'iso3' => 'SWE', 'phone_code' => '+46'],
            ['name' => 'Switzerland', 'iso2' => 'CH', 'iso3' => 'CHE', 'phone_code' => '+41'],
            ['name' => 'Thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'phone_code' => '+66'],
            ['name' => 'Turkey', 'iso2' => 'TR', 'iso3' => 'TUR', 'phone_code' => '+90'],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '+971'],
            ['name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'phone_code' => '+44'],
            ['name' => 'United States', 'iso2' => 'US', 'iso3' => 'USA', 'phone_code' => '+1'],
            ['name' => 'Vietnam', 'iso2' => 'VN', 'iso3' => 'VNM', 'phone_code' => '+84'],
            ['name' => 'Zimbabwe', 'iso2' => 'ZW', 'iso3' => 'ZWE', 'phone_code' => '+263'],
        ]);
    }
}
