<?php

namespace App\Support;

class AmenityIcons
{
    /** @var array<string,string> */
    protected static array $map = [
        // Internet & Tech
        'wifi' => 'fa-wifi', 'wi-fi' => 'fa-wifi', 'internet' => 'fa-wifi',
        'cable' => 'fa-tv', 'tv' => 'fa-tv', 'television' => 'fa-tv', 'netflix' => 'fa-tv', 'smart tv' => 'fa-tv',

        // Gaming
        'ps1' => 'fa-gamepad', 'ps2' => 'fa-gamepad', 'ps3' => 'fa-gamepad', 'ps4' => 'fa-gamepad', 'ps5' => 'fa-gamepad',
        'playstation' => 'fa-gamepad', 'xbox' => 'fa-gamepad', 'nintendo' => 'fa-gamepad', 'gaming' => 'fa-gamepad', 'game' => 'fa-gamepad',

        // Climate
        'ac' => 'fa-snowflake', 'air conditioning' => 'fa-snowflake', 'aircon' => 'fa-snowflake', 'air con' => 'fa-snowflake',
        'fan' => 'fa-fan', 'heater' => 'fa-temperature-high', 'heating' => 'fa-temperature-high',

        // Bedroom & Sleep
        'bedroom' => 'fa-bed', 'bed' => 'fa-bed', 'double bed' => 'fa-bed', 'single bed' => 'fa-bed',
        'bunk bed' => 'fa-bed', 'sofa bed' => 'fa-bed', 'pillow' => 'fa-bed', 'blanket' => 'fa-bed', 'linens' => 'fa-bed',

        // Bathroom
        'shower' => 'fa-shower', 'bathroom' => 'fa-shower', 'hot shower' => 'fa-shower', 'hot water' => 'fa-shower',
        'bathtub' => 'fa-bath', 'bath' => 'fa-bath', 'towel' => 'fa-bath', 'toilet' => 'fa-toilet', 'restroom' => 'fa-toilet',

        // Kitchen & Food
        'kitchen' => 'fa-utensils', 'cooking' => 'fa-utensils', 'dining' => 'fa-utensils',
        'ref' => 'fa-temperature-low', 'refrigerator' => 'fa-temperature-low', 'fridge' => 'fa-temperature-low',
        'microwave' => 'fa-fire-burner', 'stove' => 'fa-fire-burner', 'oven' => 'fa-fire-burner', 'rice cooker' => 'fa-fire-burner',
        'coffee' => 'fa-mug-hot', 'coffee maker' => 'fa-mug-hot', 'kettle' => 'fa-mug-hot',

        // Outdoor & BBQ
        'bbq' => 'fa-fire', 'barbecue' => 'fa-fire', 'grill' => 'fa-fire', 'bonfire' => 'fa-fire',
        'garden' => 'fa-tree', 'outdoor' => 'fa-tree', 'balcony' => 'fa-building', 'terrace' => 'fa-building', 'patio' => 'fa-building',

        // Pool & Recreation
        'pool' => 'fa-water', 'swimming pool' => 'fa-water', 'jacuzzi' => 'fa-water', 'hot tub' => 'fa-water',
        'billiards' => 'fa-circle', 'karaoke' => 'fa-microphone', 'videoke' => 'fa-microphone',

        // Transport & Parking
        'parking' => 'fa-car', 'garage' => 'fa-car', 'car park' => 'fa-car', 'motorcycle' => 'fa-motorcycle', 'bicycle' => 'fa-bicycle',

        // Laundry
        'washing machine' => 'fa-jug-detergent', 'laundry' => 'fa-jug-detergent', 'washer' => 'fa-jug-detergent', 'dryer' => 'fa-jug-detergent',

        // Safety & Security
        'cctv' => 'fa-camera', 'security' => 'fa-shield-halved', 'safe' => 'fa-lock',
        'first aid' => 'fa-kit-medical', 'fire extinguisher' => 'fa-fire-extinguisher',

        // Utilities
        'electricity' => 'fa-bolt', 'generator' => 'fa-bolt', 'water' => 'fa-droplet', 'drinking water' => 'fa-droplet',

        // Workspace
        'desk' => 'fa-briefcase', 'workspace' => 'fa-briefcase', 'work area' => 'fa-briefcase',

        // Pets
        'pet friendly' => 'fa-paw', 'pets allowed' => 'fa-paw', 'pets' => 'fa-paw',

        // Accessibility
        'wheelchair' => 'fa-wheelchair', 'elevator' => 'fa-elevator',
    ];

    public static function icon(string $amenity): string
    {
        $amenity = strtolower(trim($amenity));

        if (isset(self::$map[$amenity])) {
            return self::$map[$amenity];
        }

        foreach (self::$map as $keyword => $icon) {
            if (str_contains($amenity, $keyword) || str_contains($keyword, $amenity)) {
                return $icon;
            }
        }

        return 'fa-circle-check';
    }
}
