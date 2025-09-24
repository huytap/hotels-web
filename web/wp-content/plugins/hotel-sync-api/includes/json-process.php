
<?php
/**
 * Universal UTF-8 Cleaner for Dynamic Content
 * Fix UTF-8 encoding issues cho text động từ WordPress/database
 */

/**
 * Fix UTF-8 encoding cho một string
 * 
 * @param string $str
 * @return string
 */
function fix_utf8_string($str)
{
    if (!is_string($str)) {
        return $str;
    }

    // Nếu string đã là UTF-8 hợp lệ thì return luôn
    if (mb_check_encoding($str, 'UTF-8')) {
        return $str;
    }

    // Thử convert từ các encoding phổ biến
    $encodings = ['ISO-8859-1', 'Windows-1252', 'CP1252', 'ISO-8859-15'];

    foreach ($encodings as $encoding) {
        $converted = @mb_convert_encoding($str, 'UTF-8', $encoding);
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
    }

    // Nếu vẫn không được, dùng cách aggressive hơn
    // Remove invalid UTF-8 sequences
    $str = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');

    // Remove/replace invalid characters với placeholder
    $str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);

    return $str ?: ''; // Return empty nếu null
}

/**
 * Fix UTF-8 encoding cho array recursively
 * 
 * @param array $data
 * @return array
 */
function fix_utf8_array($data)
{
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $clean_key = fix_utf8_string($key);
            $result[$clean_key] = fix_utf8_array($value);
        }
        return $result;
    } else {
        return fix_utf8_string($data);
    }
}

/**
 * Safe JSON encode with UTF-8 cleaning
 * 
 * @param mixed $data
 * @param int $flags JSON encode flags
 * @return string|false
 */
function safe_json_encode($data, $flags = JSON_UNESCAPED_UNICODE)
{
    // Clean UTF-8 first
    $cleaned_data = fix_utf8_array($data);

    // Try normal encode
    $json = json_encode($cleaned_data, $flags);

    if ($json === false) {
        // If fails, try with more aggressive flags
        $json = json_encode($cleaned_data, $flags | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            // Last resort - ignore invalid UTF-8
            $json = json_encode($cleaned_data, $flags | JSON_INVALID_UTF8_IGNORE);
        }
    }

    return $json;
}

/**
 * Validate if string has UTF-8 issues
 * 
 * @param string $str
 * @return bool True nếu có vấn đề
 */
function has_utf8_issues($str)
{
    if (!is_string($str)) {
        return false;
    }

    // Check for invalid UTF-8
    if (!mb_check_encoding($str, 'UTF-8')) {
        return true;
    }

    // Check for replacement characters (�)
    if (strpos($str, '�') !== false) {
        return true;
    }

    // Check for null bytes and control characters
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $str)) {
        return true;
    }

    return false;
}

/**
 * Clean database text output (for WordPress)
 * 
 * @param string $text
 * @return string
 */
function clean_db_text($text)
{
    if (!is_string($text)) {
        return $text;
    }

    // Fix UTF-8
    $text = fix_utf8_string($text);

    // Remove null bytes (common in DB corruption)
    $text = str_replace("\0", '', $text);

    // Fix common WordPress issues
    $text = str_replace(['â€™', 'â€œ', 'â€�'], ["'", '"', '"'], $text);

    // Normalize line breaks
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    return trim($text);
}

/**
 * MAIN FUNCTION - Dùng cái này cho case của bạn
 * 
 * @param array $data Your WordPress array
 * @return string JSON string
 */
function wordpress_array_to_json($data)
{
    // Clean UTF-8 issues
    $cleaned = fix_utf8_array($data);

    // Safe encode to JSON
    $json = safe_json_encode($cleaned, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    if ($json === false) {
        // Log error for debugging
        error_log('JSON encode failed: ' . json_last_error_msg());
        return false;
    }

    return $json;
}

/**
 * Debug function - check UTF-8 issues trong array
 * 
 * @param array $data
 * @return array Issues found
 */
function debug_utf8_issues($data, $path = '')
{
    $issues = [];

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $current_path = $path ? $path . '.' . $key : $key;
            if (has_utf8_issues($key)) {
                $issues[] = "Key has UTF-8 issues: $current_path";
            }
            $issues = array_merge($issues, debug_utf8_issues($value, $current_path));
        }
    } elseif (is_string($data)) {
        if (has_utf8_issues($data)) {
            $issues[] = "Value has UTF-8 issues at: $path";
        }
    }

    return $issues;
}
