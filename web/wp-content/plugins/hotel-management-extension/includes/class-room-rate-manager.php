<?php

/**
 * Class HME_Room_Rate_Manager
 * Quản lý các chức năng liên quan đến Room Rates và Availability
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class HME_Room_Rate_Manager
{

    public function __construct()
    {
        // Không cần thêm hooks ở đây vì đã được handle trong main plugin
    }

    /**
     * AJAX Handler: Lấy danh sách room rates
     */
    public static function ajax_get_room_rates()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Lấy parameters
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        $room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : '';

        // Tạo params cho API
        $params = array();

        if (!empty($date_from)) {
            $params['date_from'] = $date_from;
        }

        if (!empty($date_to)) {
            $params['date_to'] = $date_to;
        }

        if (!empty($room_type_id)) {
            $params['room_type_id'] = $room_type_id;
        }

        // Gọi API
        $response = callApi('room-rates', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Cập nhật room rate cho một ngày cụ thể
     */
    public static function ajax_update_room_rate()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Validate required fields
        $required_fields = ['room_type_id', 'date', 'rate'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || $_POST[$field] === '') {
                wp_send_json_error("Field {$field} is required");
                return;
            }
        }

        // Sanitize data
        $rate_data = array(
            'room_type_id' => intval($_POST['room_type_id']),
            'date' => sanitize_text_field($_POST['date']),
            'rate' => floatval($_POST['rate']),
            'min_stay' => isset($_POST['min_stay']) ? intval($_POST['min_stay']) : 1,
            'max_stay' => isset($_POST['max_stay']) ? intval($_POST['max_stay']) : 30,
            'available_rooms' => isset($_POST['available_rooms']) ? intval($_POST['available_rooms']) : null,
            'is_closed' => isset($_POST['is_closed']) ? (bool)$_POST['is_closed'] : false,
            'close_to_arrival' => isset($_POST['close_to_arrival']) ? (bool)$_POST['close_to_arrival'] : false,
            'close_to_departure' => isset($_POST['close_to_departure']) ? (bool)$_POST['close_to_departure'] : false
        );

        // Validate rate
        if ($rate_data['rate'] < 0) {
            wp_send_json_error('Rate must be greater than or equal to 0');
            return;
        }

        // Validate date
        if (strtotime($rate_data['date']) === false) {
            wp_send_json_error('Invalid date format');
            return;
        }

        // Validate min/max stay
        if ($rate_data['min_stay'] < 1 || $rate_data['max_stay'] < $rate_data['min_stay']) {
            wp_send_json_error('Invalid stay restrictions');
            return;
        }

        // Gọi API
        $response = callApi('room-rates', 'PUT', $rate_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Bulk update room rates cho nhiều ngày
     */
    public static function ajax_bulk_update_rates()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Validate required fields
        $required_fields = ['room_type_id', 'date_from', 'date_to'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field {$field} is required");
                return;
            }
        }

        $room_type_id = intval($_POST['room_type_id']);
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);

        // Validate dates
        if (strtotime($date_from) === false || strtotime($date_to) === false) {
            wp_send_json_error('Invalid date format');
            return;
        }

        if (strtotime($date_to) < strtotime($date_from)) {
            wp_send_json_error('End date must be after start date');
            return;
        }

        // Prepare bulk update data
        $bulk_data = array(
            'room_type_id' => $room_type_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'apply_to_weekdays' => isset($_POST['apply_to_weekdays']) ? array_map('sanitize_text_field', $_POST['apply_to_weekdays']) : null
        );

        // Add optional fields if provided
        if (isset($_POST['rate']) && $_POST['rate'] !== '') {
            $bulk_data['rate'] = floatval($_POST['rate']);
        }

        if (isset($_POST['rate_adjustment_type']) && isset($_POST['rate_adjustment_value'])) {
            $bulk_data['rate_adjustment'] = array(
                'type' => sanitize_text_field($_POST['rate_adjustment_type']), // 'percentage' or 'fixed'
                'value' => floatval($_POST['rate_adjustment_value'])
            );
        }

        if (isset($_POST['min_stay']) && $_POST['min_stay'] !== '') {
            $bulk_data['min_stay'] = intval($_POST['min_stay']);
        }

        if (isset($_POST['max_stay']) && $_POST['max_stay'] !== '') {
            $bulk_data['max_stay'] = intval($_POST['max_stay']);
        }

        if (isset($_POST['available_rooms']) && $_POST['available_rooms'] !== '') {
            $bulk_data['available_rooms'] = intval($_POST['available_rooms']);
        }

        if (isset($_POST['is_closed'])) {
            $bulk_data['is_closed'] = (bool)$_POST['is_closed'];
        }

        if (isset($_POST['close_to_arrival'])) {
            $bulk_data['close_to_arrival'] = (bool)$_POST['close_to_arrival'];
        }

        if (isset($_POST['close_to_departure'])) {
            $bulk_data['close_to_departure'] = (bool)$_POST['close_to_departure'];
        }

        // Gọi API
        $response = callApi('room-rates/bulk-update', 'PUT', $bulk_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Toggle room availability (đóng/mở phòng)
     */
    public static function ajax_toggle_room_availability()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $room_type_id = intval($_POST['room_type_id']);
        $date = sanitize_text_field($_POST['date']);
        $is_closed = (bool)$_POST['is_closed'];

        if (empty($room_type_id) || empty($date)) {
            wp_send_json_error('Room type ID and date are required');
            return;
        }

        $data = array(
            'room_type_id' => $room_type_id,
            'date' => $date,
            'is_closed' => $is_closed
        );

        // Gọi API
        $response = callApi('room-rates/toggle-availability', 'PUT', $data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy inventory (số phòng có sẵn) cho calendar view
     */
    public static function ajax_get_inventory_calendar()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $date_from = sanitize_text_field($_POST['date_from'] ?? date('Y-m-01'));
        $date_to = sanitize_text_field($_POST['date_to'] ?? date('Y-m-t'));
        $room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : null;

        $params = array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'calendar_view' => true
        );

        if ($room_type_id) {
            $params['room_type_id'] = $room_type_id;
        }

        // Gọi API
        $response = callApi('room-rates/calendar', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Copy rates from one period to another
     */
    public static function ajax_copy_rates()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Validate required fields
        $required_fields = ['room_type_id', 'source_date_from', 'source_date_to', 'target_date_from', 'target_date_to'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field {$field} is required");
                return;
            }
        }

        $copy_data = array(
            'room_type_id' => intval($_POST['room_type_id']),
            'source_date_from' => sanitize_text_field($_POST['source_date_from']),
            'source_date_to' => sanitize_text_field($_POST['source_date_to']),
            'target_date_from' => sanitize_text_field($_POST['target_date_from']),
            'target_date_to' => sanitize_text_field($_POST['target_date_to']),
            'copy_rates' => isset($_POST['copy_rates']) ? (bool)$_POST['copy_rates'] : true,
            'copy_availability' => isset($_POST['copy_availability']) ? (bool)$_POST['copy_availability'] : true,
            'copy_restrictions' => isset($_POST['copy_restrictions']) ? (bool)$_POST['copy_restrictions'] : true,
            'overwrite_existing' => isset($_POST['overwrite_existing']) ? (bool)$_POST['overwrite_existing'] : false
        );

        // Gọi API
        $response = callApi('room-rates/copy', 'POST', $copy_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy rate templates (mẫu giá phòng)
     */
    public static function ajax_get_rate_templates()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Gọi API
        $response = callApi('room-rates/templates', 'GET');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Tạo hoặc cập nhật rate template
     */
    public static function ajax_save_rate_template()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $template_data = array(
            'id' => isset($_POST['template_id']) ? intval($_POST['template_id']) : null,
            'name' => sanitize_text_field($_POST['template_name']),
            'description' => sanitize_textarea_field($_POST['template_description'] ?? ''),
            'room_type_id' => intval($_POST['room_type_id']),
            'rates' => array(
                'monday' => floatval($_POST['rate_monday'] ?? 0),
                'tuesday' => floatval($_POST['rate_tuesday'] ?? 0),
                'wednesday' => floatval($_POST['rate_wednesday'] ?? 0),
                'thursday' => floatval($_POST['rate_thursday'] ?? 0),
                'friday' => floatval($_POST['rate_friday'] ?? 0),
                'saturday' => floatval($_POST['rate_saturday'] ?? 0),
                'sunday' => floatval($_POST['rate_sunday'] ?? 0)
            ),
            'min_stay' => intval($_POST['min_stay'] ?? 1),
            'max_stay' => intval($_POST['max_stay'] ?? 30),
            'is_active' => isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true
        );

        if (empty($template_data['name'])) {
            wp_send_json_error('Template name is required');
            return;
        }

        $endpoint = $template_data['id'] ? "room-rates/templates/{$template_data['id']}" : 'room-rates/templates';
        $method = $template_data['id'] ? 'PUT' : 'POST';

        // Gọi API
        $response = callApi($endpoint, $method, $template_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Apply rate template to date range
     */
    public static function ajax_apply_rate_template()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $apply_data = array(
            'template_id' => intval($_POST['template_id']),
            'room_type_id' => intval($_POST['room_type_id']),
            'date_from' => sanitize_text_field($_POST['date_from']),
            'date_to' => sanitize_text_field($_POST['date_to']),
            'overwrite_existing' => isset($_POST['overwrite_existing']) ? (bool)$_POST['overwrite_existing'] : false
        );

        if (empty($apply_data['template_id']) || empty($apply_data['date_from']) || empty($apply_data['date_to'])) {
            wp_send_json_error('Template ID and date range are required');
            return;
        }

        // Gọi API
        $response = callApi('room-rates/apply-template', 'POST', $apply_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy thống kê revenue by room type
     */
    public static function ajax_get_revenue_stats()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $date_from = sanitize_text_field($_POST['date_from'] ?? date('Y-m-01'));
        $date_to = sanitize_text_field($_POST['date_to'] ?? date('Y-m-t'));

        $params = array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'group_by' => sanitize_text_field($_POST['group_by'] ?? 'room_type') // 'room_type', 'date', 'month'
        );

        // Gọi API
        $response = callApi('room-rates/revenue-stats', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Helper function: Get room types for dropdown
     */
    public static function get_room_types()
    {
        // Sử dụng cache để tránh gọi API nhiều lần
        $cache_key = 'room_types_' . get_current_blog_id();
        $cached = hme_get_cached_api_response($cache_key, 3600); // Cache 1 hour

        if ($cached !== false) {
            return $cached;
        }

        $response = callApi('room-types', 'GET');
        $result = handle_api_response($response);
        if ($result['success']) {
            $room_types = $result['data'];
            hme_set_cached_api_response($cache_key, $room_types, 3600);
            return $room_types;
        }

        return array();
    }

    /**
     * Helper function: Get weekday names
     */
    public static function get_weekdays()
    {
        return array(
            'monday' => 'Thứ 2',
            'tuesday' => 'Thứ 3',
            'wednesday' => 'Thứ 4',
            'thursday' => 'Thứ 5',
            'friday' => 'Thứ 6',
            'saturday' => 'Thứ 7',
            'sunday' => 'Chủ nhật'
        );
    }

    /**
     * Helper function: Format rate data for calendar display
     */
    public static function format_calendar_data($data)
    {
        $formatted = array();

        foreach ($data as $date => $rate_info) {
            $formatted[$date] = array(
                'date' => $date,
                'rate' => floatval($rate_info['rate']),
                'rate_formatted' => hme_format_currency($rate_info['rate']),
                'available_rooms' => intval($rate_info['available_rooms']),
                'min_stay' => intval($rate_info['min_stay']),
                'max_stay' => intval($rate_info['max_stay']),
                'is_closed' => (bool)$rate_info['is_closed'],
                'close_to_arrival' => (bool)$rate_info['close_to_arrival'],
                'close_to_departure' => (bool)$rate_info['close_to_departure'],
                'css_classes' => self::get_calendar_css_classes($rate_info),
                'tooltip' => self::generate_calendar_tooltip($rate_info)
            );
        }

        return $formatted;
    }

    /**
     * Helper function: Get CSS classes for calendar cells
     */
    private static function get_calendar_css_classes($rate_info)
    {
        $classes = array('hme-calendar-cell');

        if ($rate_info['is_closed']) {
            $classes[] = 'hme-closed';
        }

        if ($rate_info['close_to_arrival']) {
            $classes[] = 'hme-cta';
        }

        if ($rate_info['close_to_departure']) {
            $classes[] = 'hme-ctd';
        }

        if ($rate_info['available_rooms'] == 0) {
            $classes[] = 'hme-no-availability';
        } elseif ($rate_info['available_rooms'] <= 3) {
            $classes[] = 'hme-low-availability';
        }

        return implode(' ', $classes);
    }

    /**
     * Helper function: Generate tooltip text for calendar cells
     */
    private static function generate_calendar_tooltip($rate_info)
    {
        $tooltip = array();

        $tooltip[] = 'Rate: ' . hme_format_currency($rate_info['rate']);
        $tooltip[] = 'Available: ' . $rate_info['available_rooms'] . ' rooms';

        if ($rate_info['min_stay'] > 1) {
            $tooltip[] = 'Min stay: ' . $rate_info['min_stay'] . ' nights';
        }

        if ($rate_info['max_stay'] < 30) {
            $tooltip[] = 'Max stay: ' . $rate_info['max_stay'] . ' nights';
        }

        if ($rate_info['is_closed']) {
            $tooltip[] = 'Status: CLOSED';
        }

        if ($rate_info['close_to_arrival']) {
            $tooltip[] = 'Closed to arrival';
        }

        if ($rate_info['close_to_departure']) {
            $tooltip[] = 'Closed to departure';
        }

        return implode('\n', $tooltip);
    }

    /**
     * Export room rates to CSV
     */
    public static function export_rates_csv($filters = array())
    {
        // Gọi API để lấy room rates với filters
        $params = array_merge($filters, ['export' => true]);
        $response = callApi('room-rates/export', 'GET', $params);
        $result = handle_api_response($response);

        if (!$result['success']) {
            return false;
        }

        $rates = $result['data'];

        // Tạo CSV content
        $csv_content = "Date,Room Type,Rate,Available Rooms,Min Stay,Max Stay,Status,Restrictions\n";

        foreach ($rates as $rate) {
            $status = $rate['is_closed'] ? 'CLOSED' : 'OPEN';
            $restrictions = array();

            if ($rate['close_to_arrival']) {
                $restrictions[] = 'CTA';
            }

            if ($rate['close_to_departure']) {
                $restrictions[] = 'CTD';
            }

            $csv_content .= sprintf(
                "\"%s\",\"%s\",%.2f,%d,%d,%d,\"%s\",\"%s\"\n",
                $rate['date'],
                $rate['room_type_name'],
                $rate['rate'],
                $rate['available_rooms'],
                $rate['min_stay'],
                $rate['max_stay'],
                $status,
                implode(', ', $restrictions)
            );
        }

        return $csv_content;
    }

    /**
     * Validate rate data
     */
    public static function validate_rate_data($data)
    {
        $errors = array();

        if (empty($data['room_type_id'])) {
            $errors[] = 'Room type is required';
        }

        if (empty($data['date'])) {
            $errors[] = 'Date is required';
        }

        if (!isset($data['rate']) || $data['rate'] < 0) {
            $errors[] = 'Valid rate is required';
        }

        if (isset($data['available_rooms']) && $data['available_rooms'] < 0) {
            $errors[] = 'Available rooms cannot be negative';
        }

        if (isset($data['min_stay']) && $data['min_stay'] < 1) {
            $errors[] = 'Minimum stay must be at least 1 night';
        }

        if (isset($data['max_stay']) && isset($data['min_stay']) && $data['max_stay'] < $data['min_stay']) {
            $errors[] = 'Maximum stay cannot be less than minimum stay';
        }

        return $errors;
    }
}
