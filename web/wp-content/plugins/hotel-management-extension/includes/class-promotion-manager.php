<?php

/**
 * Class HME_Promotion_Manager
 * Quản lý các chức năng liên quan đến Promotions và Discount Codes
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

class HME_Promotion_Manager
{

    public function __construct()
    {
        // Không cần thêm hooks ở đây vì đã được handle trong main plugin
    }

    /**
     * AJAX Handler: Duplicate promotion
     */
    public static function ajax_duplicate_promotion()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_id = intval($_POST['promotion_id']);
        $new_code = strtoupper(sanitize_text_field($_POST['new_code']));

        if (empty($promotion_id) || empty($new_code)) {
            wp_send_json_error('Promotion ID and new code are required');
            return;
        }

        $duplicate_data = array(
            'source_promotion_id' => $promotion_id,
            'new_code' => $new_code
        );

        // Gọi API
        $response = callApi('promotions/duplicate', 'POST', $duplicate_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Bulk actions cho promotions
     */
    public static function ajax_bulk_promotion_actions()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $promotion_ids = array_map('intval', $_POST['promotion_ids']);

        if (empty($action) || empty($promotion_ids)) {
            wp_send_json_error('Action and promotion IDs are required');
            return;
        }

        $results = array();
        $errors = array();

        foreach ($promotion_ids as $promotion_id) {
            switch ($action) {
                case 'activate':
                    $response = callApi("promotions/update-status", 'PUT', ['is_active' => 1]);
                    break;

                case 'deactivate':
                    $response = callApi("promotions/update-status", 'PUT', ['is_active' => 0, 'promotion_ids' => $promotion_ids]);
                    break;

                case 'delete':
                    $response = callApi("promotions/{$promotion_id}", 'DELETE');
                    break;

                default:
                    $errors[] = "Unknown action: {$action}";
                    continue 2;
            }

            $result = handle_api_response($response);
            if ($result['success']) {
                $results[] = $promotion_id;
            } else {
                $errors[] = "Promotion {$promotion_id}: " . $result['message'];
            }
        }

        wp_send_json_success(array(
            'processed' => count($results),
            'errors' => $errors,
            'action' => $action
        ));
    }

    /**
     * AJAX Handler: Generate promotion code
     */
    public static function ajax_generate_promotion_code()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        $response = callApi('promotions/check-code', 'GET');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy promotion usage history
     */
    public static function ajax_get_promotion_usage()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_id = intval($_POST['promotion_id']);
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);

        if (empty($promotion_id)) {
            wp_send_json_error('Promotion ID is required');
            return;
        }

        $params = array(
            'page' => $page,
            'per_page' => $per_page
        );

        // Gọi API
        $response = callApi("promotions/{$promotion_id}/usage", 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Helper function: Validate promotion data
     */
    private static function validate_promotion_data($data)
    {
        $errors = array();

        // Validate code format
        if (!preg_match('/^[A-Z0-9_-]+$/', $data['code'])) {
            $errors[] = 'Promotion code must contain only uppercase letters, numbers, hyphens, and underscores';
        }

        if (strlen($data['code']) < 3 || strlen($data['code']) > 20) {
            $errors[] = 'Promotion code must be between 3 and 20 characters';
        }

        // Validate discount type and value
        $allowed_discount_types = ['percentage', 'fixed', 'free_nights'];
        if (!in_array($data['discount_type'], $allowed_discount_types)) {
            $errors[] = 'Invalid discount type';
        }

        if ($data['discount_value'] <= 0) {
            $errors[] = 'Discount value must be greater than 0';
        }

        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
            $errors[] = 'Percentage discount cannot exceed 100%';
        }

        // Validate dates
        if (strtotime($data['start_date']) === false) {
            $errors[] = 'Invalid start date format';
        }

        if (strtotime($data['end_date']) === false) {
            $errors[] = 'Invalid end date format';
        }

        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            $errors[] = 'End date must be after start date';
        }

        // Validate usage limits
        if ($data['usage_limit'] < 0) {
            $errors[] = 'Usage limit cannot be negative';
        }

        if ($data['usage_per_customer'] < 1) {
            $errors[] = 'Usage per customer must be at least 1';
        }

        // Validate minimum values
        if ($data['min_nights'] < 1) {
            $errors[] = 'Minimum nights must be at least 1';
        }

        if ($data['min_amount'] < 0) {
            $errors[] = 'Minimum amount cannot be negative';
        }

        return $errors;
    }

    /**
     * Helper function: Generate unique promotion code
     */
    private static function generate_unique_code($prefix = '', $length = 8, $type = 'alphanumeric')
    {
        $attempts = 0;
        $max_attempts = 10;

        do {
            $code = $prefix . self::random_string($length - strlen($prefix), $type);
            $attempts++;
            // Kiểm tra tính duy nhất qua API
            $response = callApi('/sync/promotions/check-code', 'GET');
            $result = handle_api_response($response);

            if ($result['success'] && !$result['data']['exists']) {
                return $code;
            }
        } while ($attempts < $max_attempts);

        // Fallback: thêm timestamp nếu không generate được unique code
        return $prefix . self::random_string($length - strlen($prefix) - 4, $type) . date('His');
    }

    /**
     * Helper function: Generate random string
     */
    private static function random_string($length, $type = 'alphanumeric')
    {
        $characters = '';

        switch ($type) {
            case 'alphabetic':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alphanumeric':
            default:
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }

        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Helper function: Get promotion types with labels
     */
    public static function get_promotion_types()
    {
        return array(
            'percentage' => 'Percentage Discount',
            'fixed' => 'Fixed Amount Discount',
            'free_nights' => 'Free Nights'
        );
    }

    /**
     * Helper function: Get promotion statuses
     */
    public static function get_promotion_statuses()
    {
        return array(
            'active' => 'Active',
            'inactive' => 'Inactive',
            'expired' => 'Expired',
            'upcoming' => 'Upcoming',
            'used_up' => 'Used Up'
        );
    }

    /**
     * Helper function: Calculate promotion status
     */
    public static function calculate_promotion_status($promotion)
    {
        $now = time();
        $start_time = strtotime($promotion['start_date']);
        $end_time = strtotime($promotion['end_date']);

        if (!$promotion['is_active']) {
            return 'inactive';
        }

        if ($now < $start_time) {
            return 'upcoming';
        }

        if ($now > $end_time) {
            return 'expired';
        }

        if ($promotion['usage_limit'] > 0 && $promotion['used_count'] >= $promotion['usage_limit']) {
            return 'used_up';
        }

        return 'active';
    }

    /**
     * Helper function: Get status CSS class
     */
    public static function get_promotion_status_class($status)
    {
        $classes = array(
            'active' => 'hme-status-active',
            'inactive' => 'hme-status-inactive',
            'expired' => 'hme-status-expired',
            'upcoming' => 'hme-status-upcoming',
            'used_up' => 'hme-status-used-up'
        );

        return $classes[$status] ?? 'hme-status-unknown';
    }

    /**
     * Helper function: Format promotion for display
     */
    public static function format_promotion_for_display($promotion)
    {
        $status = self::calculate_promotion_status($promotion);

        return array(
            'id' => $promotion['id'],
            'code' => $promotion['code'],
            'title' => $promotion['title'],
            'description' => $promotion['description'],
            'discount_type' => $promotion['discount_type'],
            'discount_value' => $promotion['discount_value'],
            'discount_formatted' => self::format_discount_value($promotion['discount_type'], $promotion['discount_value']),
            'start_date_formatted' => hme_format_date($promotion['start_date']),
            'end_date_formatted' => hme_format_date($promotion['end_date']),
            'usage_count' => $promotion['used_count'] ?? 0,
            'usage_limit' => $promotion['usage_limit'],
            'usage_remaining' => $promotion['usage_limit'] > 0 ? max(0, $promotion['usage_limit'] - ($promotion['used_count'] ?? 0)) : '∞',
            'status' => $status,
            'status_label' => self::get_promotion_statuses()[$status],
            'status_class' => self::get_promotion_status_class($status),
            'is_active' => $promotion['is_active'],
            'created_at_formatted' => hme_format_datetime($promotion['created_at'])
        );
    }

    /**
     * Helper function: Format discount value for display
     */
    private static function format_discount_value($type, $value)
    {
        switch ($type) {
            case 'percentage':
                return $value . '%';
            case 'fixed':
                return hme_format_currency($value);
            case 'free_nights':
                return $value . ' free night' . ($value > 1 ? 's' : '');
            default:
                return $value;
        }
    }

    /**
     * Helper function: Get weekdays for promotion restrictions
     */
    public static function get_weekdays()
    {
        return array(
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        );
    }

    /**
     * Export promotions to CSV
     */
    public static function export_promotions_csv($filters = array())
    {
        // Gọi API để lấy tất cả promotions với filters
        $params = array_merge($filters, ['per_page' => 1000, 'export' => true]);
        $response = callApi('promotions', 'GET', $params);
        $result = handle_api_response($response);

        if (!$result['success']) {
            return false;
        }

        $promotions = $result['data']['data'] ?? array();

        // Tạo CSV content
        $csv_content = "ID,Code,Title,Type,Discount Value,Start Date,End Date,Usage Count,Usage Limit,Status,Created At\n";

        foreach ($promotions as $promotion) {
            $status = self::calculate_promotion_status($promotion);

            $csv_content .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\"\n",
                $promotion['id'],
                $promotion['code'],
                $promotion['title'],
                $promotion['discount_type'],
                self::format_discount_value($promotion['discount_type'], $promotion['discount_value']),
                $promotion['start_date'],
                $promotion['end_date'],
                $promotion['used_count'] ?? 0,
                $promotion['usage_limit'],
                $status,
                $promotion['created_at']
            );
        }

        return $csv_content;
    }

    /**
     * Helper function: Get room types for promotion restrictions
     */
    public static function get_room_types_for_promotion()
    {
        return HME_Room_Rate_Manager::get_room_types();
    }

    /**
     * Helper function: Validate promotion code format
     */
    public static function is_valid_promotion_code($code)
    {
        return preg_match('/^[A-Z0-9_-]{3,20}$/', $code);
    }

    /**
     * Helper function: Calculate discount amount
     */
    public static function calculate_discount_amount($promotion, $booking_total, $nights = 1)
    {
        switch ($promotion['discount_type']) {
            case 'percentage':
                $discount = $booking_total * ($promotion['discount_value'] / 100);
                if ($promotion['max_discount'] > 0) {
                    $discount = min($discount, $promotion['max_discount']);
                }
                return $discount;

            case 'fixed':
                return min($promotion['discount_value'], $booking_total);

            case 'free_nights':
                // Assume this is handled differently in the Laravel backend
                return 0;

            default:
                return 0;
        }
    }

    public static function ajax_get_promotions()
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
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

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

        if (!empty($type)) {
            $params['type'] = $type;
        }

        // Gọi API
        $response = callApi('promotions', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Tạo promotion mới
     */
    public static function ajax_create_promotion()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        // Gọi API để tạo promotion
        $response = callApi('promotions', 'POST', $_POST);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Cập nhật promotion
     */
    public static function ajax_update_promotion()
    {
        $promotionId = 0;
        if (isset($_POST['id']) && $_POST['id'] > 0) {
            $promotionId = $_POST['id'];
        }
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }


        // Gọi API
        $response = callApi("promotions/{$promotionId}", 'PUT', $_POST);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Xóa promotion
     */
    public static function ajax_delete_promotion()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_id = intval($_POST['promotion_id']);
        if (empty($promotion_id)) {
            wp_send_json_error('Promotion ID is required');
            return;
        }

        // Gọi API để xóa promotion
        $response = callApi("promotions/{$promotion_id}", 'DELETE');
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success('Promotion deleted successfully');
        } else {
            wp_send_json_error($result['message']);
        }
    }
    /**
     * AJAX Handler: Chi tiết promotion
     */
    public static function ajax_get_promotion()
    {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_id = intval($_GET['promotion_id']);
        if (empty($promotion_id)) {
            wp_send_json_error('Promotion ID is required');
            return;
        }

        // Call API to get promotion details
        $response = callApi("promotions/{$promotion_id}", 'GET');
        $result = handle_api_response($response);

        if ($result['success']) {
            // Correct the success message and include the data
            wp_send_json_success([
                'message' => 'Promotion details retrieved successfully.',
                'data' => $result['data']
            ]);
        } else {
            // Handle API errors
            wp_send_json_error($result['message']);
        }
    }
    /**
     * AJAX Handler: Validate promotion code
     */
    public static function ajax_validate_promotion()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_code = strtoupper(sanitize_text_field($_POST['promotion_code']));
        $booking_data = array(
            'room_type_id' => intval($_POST['room_type_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'guests' => intval($_POST['guests']),
            'customer_email' => sanitize_email($_POST['customer_email'] ?? '')
        );

        if (empty($promotion_code)) {
            wp_send_json_error('Promotion code is required');
            return;
        }

        // Gọi API để validate promotion
        $validation_data = array_merge($booking_data, ['promotion_code' => $promotion_code]);
        $response = callApi('promotions/validate', 'POST', $validation_data);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * AJAX Handler: Lấy thống kê sử dụng promotion
     */
    public static function ajax_get_promotion_stats()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $promotion_id = intval($_POST['promotion_id']);
        $date_from = sanitize_text_field($_POST['date_from'] ?? '');
        $date_to = sanitize_text_field($_POST['date_to'] ?? '');

        $params = array();

        if ($promotion_id) {
            $params['promotion_id'] = $promotion_id;
        }

        if ($date_from) {
            $params['date_from'] = $date_from;
        }

        if ($date_to) {
            $params['date_to'] = $date_to;
        }

        // Gọi API
        $response = callApi('promotions/stats', 'GET', $params);
        $result = handle_api_response($response);

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}
