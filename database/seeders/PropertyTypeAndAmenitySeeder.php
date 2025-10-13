<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyTypeAndAmenitySeeder extends Seeder
{
    public function run(): void
    {
        // Property Types
        $propertyTypes = [
            ['name' => 'Apartment'],
            ['name' => 'House'],
            ['name' => 'Villa'],
            ['name' => 'Room'],
            ['name' => 'Tent'],
            ['name' => 'Commercial'],
        ];

        foreach ($propertyTypes as $propertyType) {
            DB::table('property_types')->updateOrInsert(
                ['name' => $propertyType['name']], // condition to check if the record exists
                $propertyType // data to insert or update
            );
        }

        // Amenities
        $amenities = [
            ['name' => 'Wifi', 'icon' => 'fa-wifi'],
            ['name' => 'Air Conditioning', 'icon' => 'fa-snowflake'],
            ['name' => 'Parking', 'icon' => 'fa-car'],
            ['name' => 'Swimming Pool', 'icon' => 'fa-swimming-pool'],
            ['name' => 'Kitchen', 'icon' => 'fa-utensils'],
            ['name' => 'Heating', 'icon' => 'fa-thermometer-half'],
            ['name' => 'Pet Friendly', 'icon' => 'fa-paw'],
            ['name' => 'TV', 'icon' => 'fa-tv'],
            ['name' => 'Washer', 'icon' => 'fa-soap'],
            ['name' => 'Dryer', 'icon' => 'fa-tshirt'],
        ];

        foreach ($amenities as $amenity) {
            DB::table('amenities')->updateOrInsert(
                ['name' => $amenity['name']], // condition to check if the record exists
                $amenity // data to insert or update
            );
        }
    }
}
