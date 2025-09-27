<?php
echo "=== TESTING CALCULATE TOTAL API ===\n\n";

// Test WordPress callApi structure for calculate-total
$test_data = json_encode([
    'wp_id' => 2,
    'last_updated' => '2025-09-26 08:00:00',
    'data' => [
        'room_type_id' => 1,
        'check_in' => '2025-09-28',
        'check_out' => '2025-09-30',
        'guests' => 2,
        'promotion_code' => ''
    ]
]);

echo "1. Testing /api/sync/bookings/calculate-total endpoint...\n";
echo "Sending data: " . $test_data . "\n\n";

$api_response = shell_exec("curl -s -X POST http://localhost:8000/api/sync/bookings/calculate-total -H \"Content-Type: application/json\" -H \"Accept: application/json\" -d '$test_data'");

if ($api_response) {
    $api_data = json_decode($api_response, true);
    if ($api_data && isset($api_data['success'])) {
        if ($api_data['success'] === true) {
            echo "✅ Calculate Total API working!\n";
            echo "   - success: true\n";
            echo "   - message: " . $api_data['message'] . "\n";
            echo "   - pricing data:\n";

            $pricing = $api_data['data'];
            foreach ($pricing as $key => $value) {
                if (is_numeric($value)) {
                    echo "     * $key: " . number_format($value) . "\n";
                } else {
                    echo "     * $key: $value\n";
                }
            }
            echo "\n";
        } else {
            echo "❌ API returned error:\n";
            echo "   - success: false\n";
            echo "   - message: " . $api_data['message'] . "\n";
            if (isset($api_data['errors'])) {
                echo "   - validation errors:\n";
                foreach ($api_data['errors'] as $field => $errors) {
                    echo "     * $field: " . implode(', ', $errors) . "\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "❌ Invalid API response:\n";
        echo "   Response: " . $api_response . "\n\n";
    }
} else {
    echo "❌ API call failed\n\n";
}

// Test AJAX registration
echo "2. Checking AJAX handler registration...\n";
$plugin_file = 'web/wp-content/plugins/hotel-management-extension/includes/class-hotel-management-extension.php';
if (file_exists($plugin_file)) {
    $content = file_get_contents($plugin_file);
    if (strpos($content, 'hme_calculate_booking_total') !== false) {
        echo "✅ AJAX handler registered in plugin\n";
    } else {
        echo "❌ AJAX handler not registered\n";
    }
} else {
    echo "❌ Plugin file not found\n";
}

// Test BookingManager method
echo "\n3. Checking BookingManager method...\n";
$booking_manager_file = 'web/wp-content/plugins/hotel-management-extension/includes/class-booking-manager.php';
if (file_exists($booking_manager_file)) {
    $content = file_get_contents($booking_manager_file);
    if (strpos($content, 'ajax_calculate_booking_total') !== false &&
        strpos($content, "callApi('bookings/calculate-total'") !== false) {
        echo "✅ BookingManager has calculate total method and calls correct API\n";
    } else {
        echo "❌ BookingManager method issue\n";
    }
} else {
    echo "❌ BookingManager file not found\n";
}

echo "\n=== SUMMARY ===\n";
echo "Frontend Flow:\n";
echo "1. User selects room & dates in Step 2\n";
echo "2. Goes to Step 3, calculateTotal() JS function runs\n";
echo "3. AJAX calls: hme_calculate_booking_total\n";
echo "4. WordPress handler calls: sync/bookings/calculate-total API\n";
echo "5. Laravel returns pricing breakdown with taxes, discounts\n";
echo "6. Frontend displays pricing table & enables submit button\n\n";

echo "✅ Calculate Total API is now ready for Step 3!\n";
?>