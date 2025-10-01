<?php

/**
 * Class HME_Room_Rate_Manager
 * Manages functions related to Room Rates and Availability
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class HME_Room_Rate_Manager
{

    public function __construct()
    {
        // No need to add hooks here as they are handled in main plugin
    }

    /**
     * Helper function: Get room types from API
     */
    public static function get_room_types()
    {
        $current_lang = get_locale();
        $response = callApi('roomtypes', 'GET', ['lang' => $current_lang]);
        $result = handle_api_response($response);

        if ($result['success'] && isset($result['data'])) {
            return $result['data'];
        }

        return array();
    }

    /**
     * Helper function: Get weekday names
     */
    public static function get_weekdays()
    {
        return array(
            'monday' => __('Thứ 2', 'hotel'),
            'tuesday' => __('Thứ 3', 'hotel'),
            'wednesday' => __('Thứ 4', 'hotel'),
            'thursday' => __('Thứ 5', 'hotel'),
            'friday' => __('Thứ 6', 'hotel'),
            'saturday' => __('Thứ 7', 'hotel'),
            'sunday' => __('Chủ nhật', 'hotel')
        );
    }

    /**
     * Helper function: Format rate data for calendar display
     */
    public static function format_calendar_data($data)
    {
        if (!is_array($data)) {
            return array();
        }

        $formatted_data = array();

        foreach ($data as $item) {
            $date_key = isset($item['date']) ? $item['date'] : '';

            if (empty($date_key)) {
                continue;
            }

            $formatted_data[$date_key] = array(
                'date' => $date_key,
                'price' => isset($item['price']) ? floatval($item['price']) : 0,
                'total_for_sale' => isset($item['total_for_sale']) ? intval($item['total_for_sale']) : 0,
                'booked_rooms' => isset($item['booked_rooms']) ? intval($item['booked_rooms']) : 0,
                'available_rooms' => isset($item['available_rooms']) ? intval($item['available_rooms']) : 0,
                'is_available' => isset($item['is_available']) ? (bool)$item['is_available'] : false,
                'has_rate' => isset($item['price']) && $item['price'] > 0,
                'has_inventory' => isset($item['total_for_sale']) && $item['total_for_sale'] > 0,
                'can_sell' => self::can_sell_room($item),
                'restrictions' => isset($item['restrictions']) ? $item['restrictions'] : array(),
                'min_stay' => isset($item['min_stay']) ? intval($item['min_stay']) : 1,
                'max_stay' => isset($item['max_stay']) ? intval($item['max_stay']) : 30,
            );
        }

        return $formatted_data;
    }

    /**
     * Helper function: Check if room can be sold
     */
    private static function can_sell_room($room_data)
    {
        if (!is_array($room_data)) {
            return false;
        }

        $has_rate = isset($room_data['price']) && $room_data['price'] > 0;
        $is_available = isset($room_data['is_available']) && $room_data['is_available'];
        $has_inventory = isset($room_data['total_for_sale']) && $room_data['total_for_sale'] > 0;
        $has_available_rooms = isset($room_data['available_rooms']) && $room_data['available_rooms'] > 0;

        return $has_rate && $is_available && $has_inventory && $has_available_rooms;
    }

    /**
     * Helper function: Format currency for display
     */
    public static function format_currency($amount)
    {
        if (!is_numeric($amount)) {
            return '0 VNĐ';
        }

        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Export rates to CSV format
     */
    public static function export_rates_csv($filters = array())
    {
        // Build API params from filters
        $params = array();

        if (!empty($filters['room_type_id'])) {
            $params['roomtype_id'] = intval($filters['room_type_id']);
        }

        if (!empty($filters['date_from'])) {
            $params['start_date'] = sanitize_text_field($filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $params['end_date'] = sanitize_text_field($filters['date_to']);
        }

        // Call API to get rates
        $response = callApi('room-management/calendar', 'GET', $params);
        $result = handle_api_response($response);

        if (!$result['success']) {
            return false;
        }

        $rates = $result['data'];

        // Create CSV content
        $csv_content = __('Ngày', 'hotel') . ',' . __('Loại Phòng', 'hotel') . ',' . __('Giá', 'hotel') . ',' . __('Số Phòng Có Sẵn', 'hotel') . ',' . __('Lưu Trú Tối Thiểu', 'hotel') . ',' . __('Lưu Trú Tối Đa', 'hotel') . ',' . __('Trạng Thái', 'hotel') . ',' . __('Hạn Chế', 'hotel') . "\n";

        foreach ($rates as $rate) {
            $status = $rate['is_closed'] ? __('ĐÓNG', 'hotel') : __('MỞ', 'hotel');
            $restrictions = array();

            if ($rate['close_to_arrival']) {
                $restrictions[] = 'CTA';
            }
            if ($rate['close_to_departure']) {
                $restrictions[] = 'CTD';
            }

            $restrictions_str = implode('|', $restrictions);

            $csv_content .= sprintf(
                "%s,%s,%s,%d,%d,%d,%s,%s\n",
                $rate['date'],
                $rate['room_type_name'] ?? '',
                $rate['price'],
                $rate['available_rooms'] ?? 0,
                $rate['min_stay'] ?? 1,
                $rate['max_stay'] ?? 30,
                $status,
                $restrictions_str
            );
        }

        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="room-rates-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV
        echo chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM
        echo $csv_content;

        return true;
    }

    /**
     * Validate rate data
     */
    public static function validate_rate_data($data)
    {
        $errors = array();

        if (empty($data['room_type_id'])) {
            $errors[] = __('Loại phòng là bắt buộc', 'hotel');
        }

        if (empty($data['date'])) {
            $errors[] = __('Ngày là bắt buộc', 'hotel');
        }

        if (!isset($data['rate']) || $data['rate'] < 0) {
            $errors[] = __('Giá hợp lệ là bắt buộc', 'hotel');
        }

        if (isset($data['available_rooms']) && $data['available_rooms'] < 0) {
            $errors[] = __('Số phòng có sẵn không thể âm', 'hotel');
        }

        if (isset($data['min_stay']) && $data['min_stay'] < 1) {
            $errors[] = __('Lưu trú tối thiểu phải ít nhất 1 đêm', 'hotel');
        }

        if (isset($data['max_stay']) && isset($data['min_stay']) && $data['max_stay'] < $data['min_stay']) {
            $errors[] = __('Lưu trú tối đa không thể nhỏ hơn lưu trú tối thiểu', 'hotel');
        }

        return $errors;
    }

    /**
     * AJAX Handler: Export rates to CSV
     */
    public static function ajax_export_rates()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hme_admin_nonce')) {
            wp_send_json_error(__('Mã bảo mật không hợp lệ', 'hotel'));
            return;
        }

        // Get filters
        $filters = array(
            'room_type_id' => isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : '',
            'date_from' => isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '',
            'date_to' => isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : ''
        );

        // Export CSV
        if (self::export_rates_csv($filters)) {
            exit; // Important: stop execution after CSV output
        } else {
            wp_send_json_error(__('Không thể xuất dữ liệu', 'hotel'));
        }
    }
}
