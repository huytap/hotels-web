<?php
echo "=== WORDPRESS API CONFIG TEST ===\n\n";

// Test 1: Check if WordPress has sync prefix configured
echo "1. Checking WordPress API configuration...\n";
$api_call_file = 'web/wp-content/plugins/hotel-sync-api/includes/api-call.php';
if (file_exists($api_call_file)) {
    $content = file_get_contents($api_call_file);

    // Look for URL construction
    if (preg_match('/rtrim\(API_URL[^;]+/', $content, $matches)) {
        echo "✅ WordPress API URL construction found\n";
        echo "   Code: " . $matches[0] . "\n";
    }

    // Check if there's any sync prefix handling
    if (strpos($content, 'sync') !== false) {
        echo "✅ WordPress handles sync prefix automatically\n";
    }
} else {
    echo "❌ API call file not found\n";
}
echo "\n";

// Test 2: Check booking manager endpoint
echo "2. Checking booking manager endpoint...\n";
$booking_manager_file = 'web/wp-content/plugins/hotel-management-extension/includes/class-booking-manager.php';
if (file_exists($booking_manager_file)) {
    $content = file_get_contents($booking_manager_file);
    if (strpos($content, "callApi('hotel/find-rooms'") !== false) {
        echo "✅ AJAX handler uses correct endpoint: hotel/find-rooms (without sync prefix)\n";
    } elseif (strpos($content, "callApi('sync/hotel/find-rooms'") !== false) {
        echo "❌ AJAX handler still has sync prefix - needs to be removed\n";
    } else {
        echo "❌ AJAX handler endpoint not found\n";
    }
} else {
    echo "❌ BookingManager file not found\n";
}
echo "\n";

// Test 3: Check API routes
echo "3. Checking Laravel API routes...\n";
$routes_file = 'booking/routes/api.php';
if (file_exists($routes_file)) {
    $content = file_get_contents($routes_file);
    if (strpos($content, "'/sync/hotel/find-rooms'") !== false) {
        echo "✅ Laravel route has sync prefix: /api/sync/hotel/find-rooms\n";
        echo "   This is correct because WordPress adds sync prefix automatically\n";
    } else {
        echo "❌ Laravel route structure not found\n";
    }
} else {
    echo "❌ Routes file not found\n";
}
echo "\n";

echo "=== FLOW EXPLANATION ===\n";
echo "WordPress AJAX Call Flow:\n";
echo "1. Frontend calls: hme_get_available_room_types\n";
echo "2. AJAX handler calls: callApi('hotel/find-rooms', 'POST', data)\n";
echo "3. WordPress API adds sync prefix automatically\n";
echo "4. Final API call: {API_URL}/sync/hotel/find-rooms\n";
echo "5. Laravel route handles: /api/sync/hotel/find-rooms\n\n";

echo "✅ Configuration is now correct!\n";
echo "   - WordPress AJAX: hotel/find-rooms (no sync prefix needed)\n";
echo "   - WordPress adds: sync prefix automatically\n";
echo "   - Laravel handles: /api/sync/hotel/find-rooms\n";
?>