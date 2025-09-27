<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Room Types for Hotel ID 1:\n";
$hotel = App\Models\Hotel::find(1);
if ($hotel) {
    $roomTypes = $hotel->roomtypes;
    foreach ($roomTypes as $rt) {
        echo "ID: {$rt->id}, Name: {$rt->name}, Adult Capacity: {$rt->adult_capacity}\n";
    }
    echo "Total room types: " . $roomTypes->count() . "\n";
} else {
    echo "Hotel not found\n";
}

echo "\nRoom Rates:\n";
$rates = App\Models\RoomRate::take(5)->get();
foreach ($rates as $rate) {
    echo "ID: {$rate->id}, Room Type: {$rate->room_type_id}, Date: {$rate->date}, Rate: {$rate->rate}\n";
}
echo "Total rates: " . App\Models\RoomRate::count() . "\n";