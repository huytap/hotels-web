<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gửi yêu cầu đến Laravel API với phương thức HTTP và dữ liệu động.
 *
 * @param string $endpoint Điểm cuối API (ví dụ: 'hotels', 'rooms').
 * @param string $method Phương thức HTTP (ví dụ: 'GET', 'POST', 'PUT', 'DELETE').
 * @param array $data Dữ liệu cần gửi đi.
 * @return array|WP_Error|null Phản hồi từ API hoặc đối tượng lỗi WP_Error hoặc null nếu không thực hiện.
 */
function callApi($endpoint, $method, $data = [])
{
    $current_blog_id = get_current_blog_id();

    // Chỉ thực hiện nếu đây là một site con (ID > 1)
    if ($current_blog_id <= 1) {
        error_log('API Call skipped: Main site (ID: ' . $current_blog_id . ')');
        return null;
    }

    // Kiểm tra API_URL constant
    if (!defined('API_URL') || empty(API_URL)) {
        error_log('API Call failed: API_URL not defined');
        return new WP_Error('missing_api_url', 'API URL not configured');
    }

    // Nối URL với điểm cuối
    $api_url = rtrim(API_URL, '/') . '/' . ltrim($endpoint, '/');

    // Lấy token và trạng thái kích hoạt từ database của site chính
    switch_to_blog(get_main_site_id());
    $option_key = "hotel_sync_api_config";
    $config = get_option($option_key, ['subsites' => []]);
    $site_conf = $config['subsites'][$current_blog_id] ?? null;
    restore_current_blog();

    // Kiểm tra xem token có được tạo và bật hay không
    if (empty($site_conf) || empty($site_conf['enabled']) || empty($site_conf['token'])) {
        error_log('API Call failed: Token not configured or disabled for site ID: ' . $current_blog_id);
        return new WP_Error('api_not_configured', 'API not configured or disabled for this site');
    }

    $token = $site_conf['token'];
    $response = null;

    try {
        // Xử lý dữ liệu và cấu hình theo phương thức HTTP
        if ($method === 'GET' || $method === 'DELETE') {
            // Đối với GET/DELETE, dữ liệu được gửi qua tham số truy vấn
            $query_data = array_merge($data, ['wp_id' => $current_blog_id]);
            $api_url = add_query_arg($query_data, $api_url);
            $args = [
                'method' => strtoupper($method),
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'timeout' => 45,
            ];
            $response = wp_remote_request($api_url, $args);
        } elseif ($method === 'POST' || $method === 'PUT') {
            $bodyData = [
                'wp_id' => $current_blog_id,
                'last_updated' => get_lastpostmodified('gmt', 'post'),
                'data' => $data
            ];

            // Check if wordpress_array_to_json function exists
            if (function_exists('wordpress_array_to_json')) {
                $json_body = wordpress_array_to_json($bodyData);
            } else {
                // Fallback to regular json_encode with UTF-8 safe encoding
                $json_body = json_encode($bodyData, JSON_UNESCAPED_UNICODE);
            }
            if ($json_body === false) {
                error_log('API Call failed: JSON encoding failed - ' . json_last_error_msg());
                return new WP_Error('json_encode_failed', 'Failed to encode data to JSON: ' . json_last_error_msg());
            }
            // print_r($json_body);
            // die;
            $args = [
                'method' => strtoupper($method), // Use the actual method passed
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => $json_body,
                'timeout' => 45
            ];
            $response = wp_remote_request($api_url, $args);
        } else {
            // Unsupported method
            error_log('API Call failed: Unsupported HTTP method: ' . $method);
            return new WP_Error('unsupported_method', 'Unsupported HTTP method: ' . $method);
        }
    } catch (Exception $e) {
        error_log('API Call exception: ' . $e->getMessage());
        return new WP_Error('api_exception', 'API call failed: ' . $e->getMessage());
    }

    // Kiểm tra lỗi từ wp_remote_request
    if (is_wp_error($response)) {
        error_log('Error sending ' . $method . ' request to ' . $endpoint . ': ' . $response->get_error_message());
        return $response;
    }

    // Kiểm tra HTTP status code
    $http_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    // Log successful response for debugging
    error_log('API Call success: ' . $method . ' ' . $endpoint . ' - HTTP ' . $http_code);

    // Parse JSON response nếu có
    if (!empty($response_body)) {
        $parsed_response = json_decode($response_body, true);
        if ($parsed_response !== null) {
            $response['parsed_body'] = $parsed_response;
        }
    }

    // Kiểm tra HTTP errors (4xx, 5xx)
    if ($http_code >= 400) {
        $error_message = 'API returned error: HTTP ' . $http_code;
        if (isset($parsed_response['message'])) {
            $error_message .= ' - ' . $parsed_response['message'];
        }
        error_log($error_message);
        return new WP_Error('api_http_error', $error_message, ['http_code' => $http_code, 'response' => $parsed_response]);
    }
    return $response;
}

/**
 * Helper function to handle API responses
 * 
 * @param mixed $response Response from callApi function
 * @return array Result with success status and data
 */
function handle_api_response($response)
{
    if (is_null($response)) {
        return [
            'success' => false,
            'message' => 'API call was skipped (main site or not configured)',
            'data' => null
        ];
    }

    if (is_wp_error($response)) {
        return [
            'success' => false,
            'message' => $response->get_error_message(),
            'data' => null // Hoặc bạn có thể truyền thêm error_code và error_data vào đây
        ];
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $parsed_body = json_decode(wp_remote_retrieve_body($response), true); // Giải mã JSON từ body

    // Nếu body không phải là JSON hợp lệ hoặc không có
    if (is_null($parsed_body)) {
        return [
            'success' => ($http_code >= 200 && $http_code < 300),
            'message' => 'Request completed, but no valid JSON response.',
            'data' => null
        ];
    }

    // Trả về cấu trúc đã đồng bộ với backend Laravel
    return [
        'success' => $parsed_body['success'] ?? false,
        'message' => $parsed_body['message'] ?? 'An unknown error occurred',
        'data' => $parsed_body['data'] ?? null
    ];
}
