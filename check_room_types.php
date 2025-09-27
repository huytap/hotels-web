<?php
require 'booking/vendor/autoload.php';

$app = require_once 'booking/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking room types table structure and data...\n\n";

// Check room types
echo "1. Room Types in database:\n";
$roomTypes = App\Models\RoomType::all();
if ($roomTypes->count() > 0) {
    foreach ($roomTypes as $rt) {
        $name = isset($rt->name) ? $rt->name : (isset($rt->title) ? $rt->title : 'No name');
        $hotelId = isset($rt->hotel_id) ? $rt->hotel_id : 'missing';
        echo "ID: {$rt->id}, Name: {$name}, Hotel ID: {$hotelId}\n";
    }
    echo "Total room types: " . $roomTypes->count() . "\n\n";
} else {
    echo "No room types found\n\n";
}

// Check if room_type_id=1 exists and has hotel_id
echo "2. Checking room_type_id=1 specifically:\n";
$roomType = App\Models\RoomType::find(1);
if ($roomType) {
    echo "✅ Room Type ID 1 found:\n";
    $name = isset($roomType->name) ? $roomType->name : (isset($roomType->title) ? $roomType->title : 'No name');
    $hotelId = isset($roomType->hotel_id) ? $roomType->hotel_id : 'NULL';
    $isActive = isset($roomType->is_active) ? ($roomType->is_active ? 'true' : 'false') : 'N/A';
    echo "   - Name: {$name}\n";
    echo "   - Hotel ID: {$hotelId}\n";
    echo "   - Is Active: {$isActive}\n";

    if (!$roomType->hotel_id) {
        echo "\n🔧 Room type has no hotel_id, setting to hotel ID 1 (wp_id=2)...\n";
        $roomType->hotel_id = 1; // Hotel ID 1 has wp_id=2
        $roomType->save();
        echo "✅ Updated room type hotel_id\n";
    }
} else {
    echo "❌ Room Type ID 1 not found\n";
}

// Check hotel
echo "\n3. Checking hotel:\n";
$hotel = App\Models\Hotel::find(1);
if ($hotel) {
    echo "✅ Hotel ID 1 (wp_id={$hotel->wp_id}) found: {$hotel->name}\n";
} else {
    echo "❌ Hotel not found\n";
}
?>