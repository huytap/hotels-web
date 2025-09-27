<?php
// Test room data transformation logic directly

// Mock API response data (from our working API)
$mock_api_response = [
    [
        "room_type" => [
            "id" => 1,
            "name" => "Deluxe Double Room",
            "description" => "Spacious room with city view, perfect for couples",
            "max_occupancy" => 2,
            "adult_capacity" => 2,
            "child_capacity" => 1
        ],
        "available_count" => 5,
        "base_price" => 1200000,
        "total_price" => 2400000,
        "currency" => "VND",
        "nights" => 2,
        "applicable_promotions" => []
    ],
    [
        "room_type" => [
            "id" => 2,
            "name" => "Superior Twin Room",
            "description" => "Comfortable room with twin beds, great for friends or business travelers",
            "max_occupancy" => 3,
            "adult_capacity" => 3,
            "child_capacity" => 1
        ],
        "available_count" => 3,
        "base_price" => 1500000,
        "total_price" => 3000000,
        "currency" => "VND",
        "nights" => 2,
        "applicable_promotions" => []
    ],
    [
        "room_type" => [
            "id" => 3,
            "name" => "Family Suite",
            "description" => "Large suite with separate living area, perfect for families",
            "max_occupancy" => 4,
            "adult_capacity" => 4,
            "child_capacity" => 2
        ],
        "available_count" => 2,
        "base_price" => 2200000,
        "total_price" => 4400000,
        "currency" => "VND",
        "nights" => 2,
        "applicable_promotions" => []
    ]
];

echo "Testing room data transformation...\n";
echo "Mock API Response:\n" . json_encode($mock_api_response, JSON_PRETTY_PRINT) . "\n\n";

// Apply the same transformation logic from the AJAX handler
$room_types = array();
$rooms_data = $mock_api_response; // Direct array

foreach ($rooms_data as $room) {
    // Handle both nested room_type structure and flat structure
    $room_info = $room['room_type'] ?? $room;

    $room_types[] = array(
        'id' => $room_info['id'] ?? $room['room_type_id'] ?? 0,
        'name' => $room_info['name'] ?? $room['room_type_name'] ?? $room['title'] ?? 'Unknown Room',
        'rate' => $room['base_price'] ?? $room['rate'] ?? $room['price'] ?? $room_info['base_rate'] ?? 0,
        'max_guests' => $room_info['adult_capacity'] ?? $room_info['max_guests'] ?? $room['max_guests'] ?? $room['capacity'] ?? 2,
        'description' => $room_info['description'] ?? $room['description'] ?? '',
        'available_rooms' => $room['available_count'] ?? $room['available'] ?? 1
    );
}

echo "Transformed room types for frontend:\n";
echo json_encode($room_types, JSON_PRETTY_PRINT) . "\n";

echo "\nThis is the data structure the frontend dropdown will receive.\n";
?>