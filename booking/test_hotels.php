<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Hotels in database:\n";
$hotels = App\Models\Hotel::all();
foreach ($hotels as $hotel) {
    echo "ID: {$hotel->id}, WP_ID: {$hotel->wp_id}, Name: {$hotel->name}\n";
}
echo "Total hotels: " . $hotels->count() . "\n";