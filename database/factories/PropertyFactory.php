<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $rentType = $this->faker->randomElement(['daily','monthly']);
        $title = $this->faker->randomElement([
            'Modern Apartment near Metro',
            'Cozy House with Garden',
            'Spacious Villa with Pool',
            'Budget Room in City Center',
            'Premium Commercial Space'
        ]);

        $city  = $this->faker->randomElement(['Chennai','Coimbatore','Bengaluru','Hyderabad','Pune','Mumbai','Delhi']);
        $state = $this->faker->randomElement(['TN','KA','TG','MH','DL']);
        $country = 'india';

        $from = Carbon::now()->addDays($this->faker->numberBetween(1, 10));
        $to   = (clone $from)->addDays($this->faker->numberBetween(10, 40));

        return [
            // FK must point to existing users and property_types in your DB
            'user_id'          => User::query()->inRandomOrder()->value('id') ?? 1,
            'property_type_id' => PropertyType::query()->inRandomOrder()->value('id') ?? 1,

            'title'            => $title,
            'description'      => $this->faker->paragraph(3),
            'price'            => $rentType === 'daily'
                                    ? $this->faker->randomFloat(2, 20, 200)
                                    : $this->faker->randomFloat(2, 3000, 45000),
            'rent_type'        => $rentType,

            'bedrooms'         => $this->faker->numberBetween(1, 4),
            'bathrooms'        => $this->faker->numberBetween(1, 3),
            'kitchen'          => 1,
            'balcony'          => $this->faker->numberBetween(0, 2),
            'hall'             => 1,
            'floors'           => $this->faker->numberBetween(1, 5),
            'parking'          => $this->faker->boolean(70),

            'area'             => $this->faker->numberBetween(250, 2500) . ' sqft',
            'door_no'          => (string) $this->faker->numberBetween(1, 300),
            'street'           => $this->faker->streetName(),
            'district'         => $this->faker->citySuffix(),
            'city'             => $city,
            'state'            => $state,
            'country'          => $country,
            'postal_code'      => (string) $this->faker->numerify('######'),
            'phone_number'     => $this->faker->numerify('9#########'),

            // optional geo (nullable in your schema)
            'latitude'         => $this->faker->randomFloat(7, 8.0, 28.0),
            'longitude'        => $this->faker->randomFloat(7, 72.0, 88.0),

            'available_from'   => $from->toDateString(),
            'available_to'     => $to->toDateString(),

            'status'           => $this->faker->randomElement(['active','pending']),
            'rejection_reason' => null,
            'approved_at'      => null,
            'approved_by'      => null,
            'view_count'       => 0,
            'slug'             => Str::slug($title) . '-' . Str::random(5),
        ];
    }
}
