<?php
// Simulate WordPress AJAX call for room types
define('WP_USE_THEMES', false);
require_once('web/wp-config.php');

// Simulate $_POST data that would come from the booking form
$_POST = array(
    'action' => 'hme_get_available_room_types',
    'check_in' => '2025-09-28',
    'check_out' => '2025-09-30',
    'guests' => '2',
    'nonce' => wp_create_nonce('hme_admin_nonce')
);

echo "Testing AJAX handler for room types...\n";
echo "POST data: " . print_r($_POST, true) . "\n";

// Load the booking manager class
require_once('web/wp-content/plugins/hotel-management-extension/includes/class-booking-manager.php');

// Call the AJAX handler
ob_start();
HME_Booking_Manager::ajax_get_available_room_types();
$output = ob_get_clean();

echo "AJAX Response: " . $output . "\n";