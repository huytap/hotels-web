<?php

/**
 * Class HME_Booking_Manager
 * Quản lý các chức năng liên quan đến Booking
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class HME_Booking_Manager
{

    public function __construct()
    {
        // Không cần thêm hooks ở đây vì đã được handle trong main plugin
    }

    /**
     * AJAX Handler: Lấy danh sách bookings
     */
    public static function ajax_get_bookings()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Lấy parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';

        // Tạo params cho API
        $params = array(
            'page' => $page,
            'per_page' => $per_page
        );

        if (!empty($status)) {
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $params['search'] = $search;
        }

        if (!empty($date_from)) {
            $params['date_from'] = $date_from;
        }

        if (!empty($date_to)) {
            $params['date_to'] = $date_to;
        }

        // Gọi API
        $response = callApi('bookings', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Tạo booking mới
     */
    public static function ajax_create_booking()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Validate required fields
        $required_fields = ['customer_name', 'customer_email', 'customer_phone', 'room_type_id', 'check_in', 'check_out', 'guests'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field {$field} is required");
                return;
            }
        }

        // Sanitize data
        $booking_data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'room_type_id' => intval($_POST['room_type_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'guests' => intval($_POST['guests']),
            'notes' => sanitize_textarea_field($_POST['notes'] ?? ''),
            'promotion_code' => sanitize_text_field($_POST['promotion_code'] ?? ''),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? '')
        );

        // Validate dates
        if (strtotime($booking_data['check_in']) <= time()) {
            wp_send_json_error('Check-in date must be in the future');
            return;
        }

        if (strtotime($booking_data['check_out']) <= strtotime($booking_data['check_in'])) {
            wp_send_json_error('Check-out date must be after check-in date');
            return;
        }

        // Gọi API để tạo booking
        $response = callApi('bookings', 'POST', $booking_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Cập nhật booking
     */
    public static function ajax_update_booking()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $booking_id = intval($_POST['booking_id']);
        if (empty($booking_id)) {
            wp_send_json_error('Booking ID is required');
            return;
        }

        // Determine update type
        $update_type = sanitize_text_field($_POST['update_type'] ?? 'full');

        switch ($update_type) {
            case 'status':
                self::update_booking_status($booking_id);
                break;

            case 'details':
                self::update_booking_details($booking_id);
                break;

            default:
                wp_send_json_error('Invalid update type');
        }
    }

    /**
     * Update booking status only
     */
    private static function update_booking_status($booking_id)
    {
        $status = sanitize_text_field($_POST['status']);
        $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];

        if (!in_array($status, $allowed_statuses)) {
            wp_send_json_error('Invalid status');
            return;
        }

        $data = array('status' => $status);

        // Thêm lý do hủy nếu có
        if ($status === 'cancelled' && !empty($_POST['cancellation_reason'])) {
            $data['cancellation_reason'] = sanitize_textarea_field($_POST['cancellation_reason']);
        }

        // Gọi API
        $response = callApi("bookings/{$booking_id}/status", 'PUT', $data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Update booking full details
     */
    private static function update_booking_details($booking_id)
    {
        // Sanitize data
        $booking_data = array();

        // Only update fields that are provided
        if (isset($_POST['customer_name'])) {
            $booking_data['customer_name'] = sanitize_text_field($_POST['customer_name']);
        }

        if (isset($_POST['customer_email'])) {
            $booking_data['customer_email'] = sanitize_email($_POST['customer_email']);
        }

        if (isset($_POST['customer_phone'])) {
            $booking_data['customer_phone'] = sanitize_text_field($_POST['customer_phone']);
        }

        if (isset($_POST['room_type_id'])) {
            $booking_data['room_type_id'] = intval($_POST['room_type_id']);
        }

        if (isset($_POST['check_in'])) {
            $booking_data['check_in'] = sanitize_text_field($_POST['check_in']);
        }

        if (isset($_POST['check_out'])) {
            $booking_data['check_out'] = sanitize_text_field($_POST['check_out']);
        }

        if (isset($_POST['guests'])) {
            $booking_data['guests'] = intval($_POST['guests']);
        }

        if (isset($_POST['notes'])) {
            $booking_data['notes'] = sanitize_textarea_field($_POST['notes']);
        }

        if (isset($_POST['special_requests'])) {
            $booking_data['special_requests'] = sanitize_textarea_field($_POST['special_requests']);
        }

        // Validate dates if provided
        if (isset($booking_data['check_in']) && isset($booking_data['check_out'])) {
            if (strtotime($booking_data['check_out']) <= strtotime($booking_data['check_in'])) {
                wp_send_json_error('Check-out date must be after check-in date');
                return;
            }
        }

        // Gọi API
        $response = callApi("bookings/{$booking_id}", 'PUT', $booking_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Xóa booking
     */
    public static function ajax_delete_booking()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $booking_id = intval($_POST['booking_id']);
        if (empty($booking_id)) {
            wp_send_json_error('Booking ID is required');
            return;
        }

        // Gọi API để xóa booking
        $response = callApi("bookings/{$booking_id}", 'DELETE');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success('Booking deleted successfully');
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy chi tiết booking
     */
    public static function ajax_get_booking_details()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $booking_id = intval($_POST['booking_id']);
        if (empty($booking_id)) {
            wp_send_json_error('Booking ID is required');
            return;
        }

        // Gọi API để lấy chi tiết booking
        $response = callApi("bookings/{$booking_id}", 'GET');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Bulk actions cho bookings
     */
    public static function ajax_bulk_booking_actions()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $booking_ids = array_map('intval', $_POST['booking_ids']);

        if (empty($action) || empty($booking_ids)) {
            wp_send_json_error('Action and booking IDs are required');
            return;
        }

        $results = array();
        $errors = array();

        foreach ($booking_ids as $booking_id) {
            switch ($action) {
                case 'confirm':
                    $response = callApi("bookings/{$booking_id}/status", 'PUT', ['status' => 'confirmed']);
                    break;

                case 'cancel':
                    $response = callApi("bookings/{$booking_id}/status", 'PUT', ['status' => 'cancelled']);
                    break;

                case 'delete':
                    $response = callApi("bookings/{$booking_id}", 'DELETE');
                    break;

                default:
                    $errors[] = "Unknown action: {$action}";
                    continue 2;
            }

            $result = handle_api_response($response);
            if ($result['success']) {
                $results[] = $booking_id;
            } else {
                $errors[] = "Booking {$booking_id}: " . $result['message'];
            }
        }

        wp_send_json_success(array(
            'processed' => count($results),
            'errors' => $errors,
            'action' => $action
        ));
    }

    /**
     * AJAX Handler: Validate promotion code for booking
     */
    public static function ajax_validate_promotion_for_booking()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_code = sanitize_text_field($_POST['promotion_code']);
        $booking_data = array(
            'room_type_id' => intval($_POST['room_type_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'guests' => intval($_POST['guests'])
        );

        if (empty($promotion_code)) {
            wp_send_json_error('Promotion code is required');
            return;
        }

        // Gọi API để validate promotion
        $params = array_merge($booking_data, ['promotion_code' => $promotion_code]);
        $response = callApi('promotions/validate', 'POST', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Calculate booking total
     */
    public static function ajax_calculate_booking_total()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $booking_data = array(
            'room_type_id' => intval($_POST['room_type_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'guests' => intval($_POST['guests']),
            'promotion_code' => sanitize_text_field($_POST['promotion_code'] ?? '')
        );

        // Gọi API để tính toán tổng tiền
        $response = callApi('bookings/calculate-total', 'POST', $booking_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Helper function: Get available room types for booking form
     */
    public static function get_available_room_types($check_in = null, $check_out = null)
    {
        $params = array();

        if ($check_in && $check_out) {
            $params['check_in'] = $check_in;
            $params['check_out'] = $check_out;
        }

        $response = callApi('room-types/available', 'GET', $params);
        $result = handle_api_response($response);

        return $result['success'] ? $result['data'] : array();
    }

    /**
     * Helper function: Get booking statuses with labels
     */
    public static function get_booking_statuses()
    {
        return array(
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'cancelled' => 'Đã hủy',
            'completed' => 'Hoàn thành',
            'no_show' => 'Không đến'
        );
    }

    /**
     * Helper function: Get status CSS class
     */
    public static function get_status_class($status)
    {
        $classes = array(
            'pending' => 'hme-status-pending',
            'confirmed' => 'hme-status-confirmed',
            'cancelled' => 'hme-status-cancelled',
            'completed' => 'hme-status-completed',
            'no_show' => 'hme-status-no-show'
        );

        return $classes[$status] ?? 'hme-status-unknown';
    }

    /**
     * Helper function: Format booking for display
     */
    public static function format_booking_for_display($booking)
    {
        return array(
            'id' => $booking['id'],
            'customer_name' => $booking['customer_name'],
            'customer_email' => $booking['customer_email'],
            'customer_phone' => $booking['customer_phone'],
            'room_type' => $booking['room_type']['name'] ?? 'N/A',
            'check_in_formatted' => hme_format_date($booking['check_in']),
            'check_out_formatted' => hme_format_date($booking['check_out']),
            'guests' => $booking['guests'],
            'total_amount_formatted' => hme_format_currency($booking['total_amount']),
            'status' => $booking['status'],
            'status_label' => self::get_booking_statuses()[$booking['status']] ?? $booking['status'],
            'status_class' => self::get_status_class($booking['status']),
            'created_at_formatted' => hme_format_datetime($booking['created_at'])
        );
    }

    /**
     * Export bookings to CSV
     */
    public static function export_bookings_csv($filters = array())
    {
        // Gọi API để lấy tất cả bookings với filters
        $params = array_merge($filters, ['per_page' => 1000, 'export' => true]);
        $response = callApi('bookings', 'GET', $params);
        $result = handle_api_response($response);

        if (!$result['success']) {
            return false;
        }

        $bookings = $result['data']['data'] ?? array();

        // Tạo CSV content
        $csv_content = "ID,Customer Name,Email,Phone,Room Type,Check-in,Check-out,Guests,Total Amount,Status,Created At\n";

        foreach ($bookings as $booking) {
            $csv_content .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%.2f,\"%s\",\"%s\"\n",
                $booking['id'],
                $booking['customer_name'],
                $booking['customer_email'],
                $booking['customer_phone'],
                $booking['room_type']['name'] ?? 'N/A',
                $booking['check_in'],
                $booking['check_out'],
                $booking['guests'],
                $booking['total_amount'],
                $booking['status'],
                $booking['created_at']
            );
        }

        return $csv_content;
    }

    /**
     * AJAX Handler: Lấy danh sách room types khả dụng cho booking
     */
    public static function ajax_get_available_room_types()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Sanitize input data
        $check_in = sanitize_text_field($_POST['check_in']);
        $check_out = sanitize_text_field($_POST['check_out']);
        $guests = intval($_POST['guests']);

        // Validate required fields
        if (empty($check_in) || empty($check_out) || $guests < 1) {
            wp_send_json_error('Missing required fields: check_in, check_out, guests');
            return;
        }

        // Validate date format (YYYY-MM-DD)
        if (!self::isValidDate($check_in) || !self::isValidDate($check_out)) {
            wp_send_json_error('Invalid date format. Expected YYYY-MM-DD');
            return;
        }

        // Check if check_out is after check_in
        if (strtotime($check_out) <= strtotime($check_in)) {
            wp_send_json_error('Check-out date must be after check-in date');
            return;
        }

        try {
            // Call API to get available rooms
            // wp_id is automatically added by callApi function
            $data = array(
                'params' => array(
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'adults' => $guests,
                    'children' => 0 // Default to 0, can be enhanced later
                )
            );

            // API path - 'sync' prefix is already configured in WordPress
            $response = callApi('hotel/find-rooms', 'POST', $data);
            $result = handle_api_response($response);

            if ($result['success']) {
                // Transform the data for frontend compatibility
                $room_types = array();

                // API response could have different structure, handle various formats
                $data = $result['data'];
                $rooms_data = array();

                // Check various possible response structures
                if (isset($data['available_rooms'])) {
                    $rooms_data = $data['available_rooms'];
                } elseif (isset($data['room_combinations'])) {
                    $rooms_data = $data['room_combinations'];
                } elseif (isset($data['rooms'])) {
                    $rooms_data = $data['rooms'];
                } elseif (is_array($data)) {
                    $rooms_data = $data;
                }

                foreach ($rooms_data as $room) {
                    // Handle both nested room_type structure and flat structure
                    $room_info = $room['room_type'] ?? $room;

                    $room_types[] = array(
                        'id' => $room_info['id'] ?? $room['room_type_id'] ?? 0,
                        'name' => $room_info['name'] ?? $room['room_type_name'] ?? $room['title'] ?? 'Unknown Room',
                        'rate' => $room['base_price'] ?? $room['rate'] ?? $room['price'] ?? $room_info['base_rate'] ?? 0,
                        'max_guests' => $room_info['adult_capacity'] ?? $room_info['max_guests'] ?? $room['max_guests'] ?? $room['capacity'] ?? $guests,
                        'description' => $room_info['description'] ?? $room['description'] ?? '',
                        'available_rooms' => $room['available_count'] ?? $room['available'] ?? 1
                    );
                }

                wp_send_json_success($room_types);
            } else {
                wp_send_json_error($result['message'] ?? 'Failed to get available rooms');
            }
        } catch (Exception $e) {
            error_log('Error in ajax_get_available_room_types: ' . $e->getMessage());

            // Fallback: Try to get all room types if API fails
            $fallback_rooms = self::get_fallback_room_types();
            if (!empty($fallback_rooms)) {
                wp_send_json_success($fallback_rooms);
            } else {
                wp_send_json_error('Failed to get available room types: ' . $e->getMessage());
            }
        }
    }

    /**
     * Fallback method to get basic room types when API fails
     */
    private static function get_fallback_room_types()
    {
        try {
            // Try to get room types from sync API or local cache
            $response = callApi('sync/roomtypes', 'GET', array());
            $result = handle_api_response($response);

            if ($result['success'] && !empty($result['data'])) {
                $room_types = array();
                foreach ($result['data'] as $room) {
                    $room_types[] = array(
                        'id' => $room['id'],
                        'name' => $room['title']['vi'] ?? $room['title']['en'] ?? $room['name'] ?? 'Unknown Room',
                        'rate' => $room['base_rate'] ?? 100000, // Default rate
                        'max_guests' => $room['max_guests'] ?? 2,
                        'description' => $room['description'] ?? '',
                        'available_rooms' => 1 // Assume 1 available
                    );
                }
                return $room_types;
            }
        } catch (Exception $e) {
            error_log('Fallback room types also failed: ' . $e->getMessage());
        }

        return array(); // Return empty array if everything fails
    }

    /**
     * Helper method to validate date format
     */
    private static function isValidDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
