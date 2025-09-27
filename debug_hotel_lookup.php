<?php
echo "=== DEBUG HOTEL LOOKUP ===\n\n";

// Test direct wp_id access
echo "1. Testing wp_id in root level...\n";
$test_data = json_encode([
    'wp_id' => 2,
    'last_updated' => '2025-09-26 08:00:00',
    'data' => [
        'params' => [
            'check_in' => '2025-09-28',
            'check_out' => '2025-09-30',
            'adults' => 2,
            'children' => 0
        ]
    ]
]);

echo "Data being sent:\n";
$parsed = json_decode($test_data, true);
echo "- wp_id (root): " . ($parsed['wp_id'] ?? 'missing') . "\n";
echo "- data.params exists: " . (isset($parsed['data']['params']) ? 'yes' : 'no') . "\n\n";

// Check hotel directly in database
require 'booking/vendor/autoload.php';
$app = require_once 'booking/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "2. Direct hotel lookup in database...\n";
$hotel = App\Models\Hotel::where('wp_id', 2)->first();
if ($hotel) {
    echo "✅ Hotel found in database:\n";
    echo "   - ID: {$hotel->id}\n";
    echo "   - WP_ID: {$hotel->wp_id}\n";
    echo "   - Name: {$hotel->name}\n";
    echo "   - is_active: " . ($hotel->is_active ? 'true' : 'false') . "\n\n";
} else {
    echo "❌ Hotel not found in database\n\n";
}

// Test API call with debugging
echo "3. Testing API call with current structure...\n";
$api_response = shell_exec("curl -s -X POST http://localhost:8000/api/sync/hotel/find-rooms -H \"Content-Type: application/json\" -H \"Accept: application/json\" -d '$test_data'");

echo "API Response: " . $api_response . "\n\n";

echo "4. Analysis:\n";
if ($hotel && $hotel->is_active) {
    echo "- Database has active hotel with wp_id=2 ✅\n";
    echo "- Request structure has wp_id=2 in root ✅\n";
    echo "- BaseApiController should find the hotel ✅\n";
    echo "\nPossible issues:\n";
    echo "- Request parsing might be different in Laravel\n";
    echo "- BaseApiController getHotelFromRequest() might not work as expected\n";
    echo "- JSON content-type handling issue\n";
} else {
    echo "- Database issue ❌\n";
}
?>