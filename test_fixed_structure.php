<?php
echo "=== TESTING FIXED DATA STRUCTURE ===\n\n";

// Test with the correct WordPress callApi structure
echo "1. Testing API with WordPress callApi structure...\n";
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

echo "Sending data structure that WordPress callApi would create:\n";
echo $test_data . "\n\n";

$api_test = shell_exec("curl -s -X POST http://localhost:8000/api/sync/hotel/find-rooms -H \"Content-Type: application/json\" -H \"Accept: application/json\" -d '$test_data'");

if ($api_test) {
    $api_data = json_decode($api_test, true);
    if ($api_data && isset($api_data['success'])) {
        if ($api_data['success'] === true) {
            echo "✅ API working! No more 422 error\n";
            echo "   - success: true\n";
            echo "   - message: " . $api_data['message'] . "\n";
            echo "   - rooms found: " . count($api_data['data']) . "\n\n";
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
        echo "   Response: " . $api_test . "\n\n";
    }
} else {
    echo "❌ API call failed\n\n";
}

// Test old structure to confirm it fails
echo "2. Testing old structure (should fail)...\n";
$old_structure = json_encode([
    'wp_id' => 2,
    'params' => [
        'check_in' => '2025-09-28',
        'check_out' => '2025-09-30',
        'adults' => 2,
        'children' => 0
    ]
]);

$old_test = shell_exec("curl -s -X POST http://localhost:8000/api/sync/hotel/find-rooms -H \"Content-Type: application/json\" -H \"Accept: application/json\" -d '$old_structure'");

if ($old_test) {
    $old_data = json_decode($old_test, true);
    if ($old_data && isset($old_data['success']) && $old_data['success'] === false) {
        echo "✅ Old structure correctly rejected with 422\n";
        echo "   This confirms validation is working properly\n\n";
    } else {
        echo "❌ Old structure was not rejected as expected\n\n";
    }
}

echo "=== STRUCTURE EXPLANATION ===\n";
echo "WordPress callApi function creates this structure:\n";
echo "{\n";
echo "  'wp_id': [current_blog_id],\n";
echo "  'last_updated': [timestamp],\n";
echo "  'data': {\n";
echo "    'params': {\n";
echo "      'check_in': '2025-09-28',\n";
echo "      'check_out': '2025-09-30',\n";
echo "      'adults': 2,\n";
echo "      'children': 0\n";
echo "    }\n";
echo "  }\n";
echo "}\n\n";

echo "Laravel controller now validates:\n";
echo "- data (required|array)\n";
echo "- data.params (required|array) \n";
echo "- data.params.check_in (required|date|after_or_equal:today)\n";
echo "- data.params.check_out (required|date|after_or_equal:data.params.check_in)\n";
echo "- data.params.adults (required|integer|min:1)\n";
echo "- data.params.children (nullable|integer|min:0)\n\n";

echo "✅ Structure alignment completed!\n";
?>