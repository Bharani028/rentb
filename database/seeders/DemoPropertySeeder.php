<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPropertySeeder extends Seeder
{
    public function run(int $count = 20): void
    {
        $amenityIds = Amenity::query()->pluck('id')->all();

        $properties = Property::factory()->count($count)->create();

        foreach ($properties as $property) {
            // Random amenities (2–8)
            if (!empty($amenityIds)) {
                $property->amenities()->sync(
                    collect($amenityIds)->shuffle()->take(rand(2, 8))->values()->all()
                );
            }

            // ---------- TYPE-AWARE IMAGES ----------
            // Look up the selected property type name
            $type = PropertyType::find($property->property_type_id);
            $query = $this->imageQueryForType(optional($type)->name ?? '');

            // Fetch 2–5 images that match the property type
            $imagesToAdd = rand(2, 5);
            for ($i = 0; $i < $imagesToAdd; $i++) {
                // Unsplash "source" API returns a different image each time
                // (no API key; &sig= makes the URL unique)
                $sig = uniqid((string) $property->id, true);
                $url = 'https://source.unsplash.com/1200x800/?' . urlencode($query) . '&sig=' . $sig;

                try {
                    $property
                        ->addMediaFromUrl($url)
                        ->usingName("{$query}-{$i}-{$property->id}")
                        // Give Spatie a filename since Unsplash URLs don't have one
                        ->usingFileName("{$property->id}-{$i}.jpg")
                        ->toMediaCollection('images');
                } catch (\Throwable $e) {
                    report($e); // continue even if one image fails
                }
            }
        }
    }

    /**
     * Map a PropertyType (name/slug) to Unsplash keywords.
     * Return a comma-separated string; Unsplash treats commas as OR terms.
     */
    protected function imageQueryForType(string $typeName): string
    {
        $slug = Str::slug($typeName);

        $map = [
            'apartment'      => 'apartment,interior,living-room,real-estate',
            'flat'           => 'apartment,interior,living-room,real-estate',
            'house'          => 'house,home,exterior,real-estate',
            'villa'          => 'villa,luxury,house,exterior,real-estate',
            'studio'         => 'studio-apartment,interior,small-space',
            'room'           => 'bedroom,room,interior,cozy',
            'hostel'         => 'hostel,dorm,shared-room,bunk-bed',
            'pg'             => 'shared-room,co-living,apartment,interior',
            'cottage'        => 'cottage,cozy,wood,interior,cabin',
            'cabin'          => 'cabin,wood,forest,cozy,interior',
            'farmhouse'      => 'farmhouse,rustic,house,countryside',
            'beach-house'    => 'beach-house,coastal,sea,exterior',
            'lake-house'     => 'lake-house,lakeside,cabin,exterior',
            'resort'         => 'resort,pool,holiday,hotel',
            'hotel'          => 'hotel,room,interior,lobby',
            'commercial'     => 'commercial-building,retail,storefront,real-estate',
            'office'         => 'office,workspace,meeting-room,modern',
            'coworking'      => 'coworking,office,workspace,startup',
            'warehouse'      => 'warehouse,industrial,storage,logistics',
            'shop'           => 'shop,retail,store,interior',
            'plot'           => 'vacant-land,plot,real-estate,land',
            'land'           => 'vacant-land,plot,real-estate,land',
            'tent'           => 'tent,camping,glamping,outdoors',
            // add any custom types you use…
        ];

        // Exact match first
        if (isset($map[$slug])) {
            return $map[$slug];
        }

        // Heuristic fallbacks
        if (str_contains($slug, 'tent')) return $map['tent'];
        if (str_contains($slug, 'office')) return $map['office'];
        if (str_contains($slug, 'shop')) return $map['shop'];
        if (str_contains($slug, 'villa')) return $map['villa'];
        if (str_contains($slug, 'room')) return $map['room'];
        if (str_contains($slug, 'hotel')) return $map['hotel'];
        if (str_contains($slug, 'hostel')) return $map['hostel'];
        if (str_contains($slug, 'warehouse')) return $map['warehouse'];
        if (str_contains($slug, 'plot') || str_contains($slug, 'land')) return $map['land'];

        // Generic catch-all
        return 'real-estate,interior,house,apartment';
    }
}
