<?php
/**
 * Test new domain-config endpoint
 * Test URL: http://2govietnam.local/wp-json/hotel-info/v1/domain-config?domain=localhost:5177&language=vi
 */

// Test the new endpoint
$test_domains = [
    'localhost:5177',
    'localhost:5173',
    'paradise.hotel.com'
];

$test_languages = ['vi', 'en'];

echo "<h1>🧪 Test Domain Config Endpoint</h1>";

foreach ($test_domains as $domain) {
    foreach ($test_languages as $language) {
        echo "<h2>Testing: {$domain} ({$language})</h2>";

        $url = "http://2govietnam.local/wp-json/hotel-info/v1/domain-config?domain={$domain}&language={$language}";
        echo "<p><strong>URL:</strong> {$url}</p>";

        $response = file_get_contents($url);
        if ($response === false) {
            echo "<p style='color: red;'>❌ Failed to fetch data</p>";
        } else {
            $data = json_decode($response, true);
            echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "</pre>";
        }

        echo "<hr>";
    }
}

echo "<h2>📋 Expected Structure:</h2>";
echo "<pre style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
echo '{
  "success": true,
  "wp_id": "wp_001",
  "api_token": "dev_token_wp_001",
  "config": {
    // THEO ngôn ngữ
    "hotel_name": "Khách Sạn Paradise (vi) / Paradise Hotel (en)",
    "address": "123 Đường Biển, Đà Nẵng (vi) / 123 Beach Road, Da Nang (en)",

    // KHÔNG theo ngôn ngữ
    "phone": "+84 28 1234 5678",  // KHÔNG đổi
    "email": "info@hotel.com",    // KHÔNG đổi
    "domain": "localhost:5177"    // KHÔNG đổi
  }
}';
echo "</pre>";

echo "<h2>🎯 Test Results Summary:</h2>";
echo "<ul>";
echo "<li>✅ <strong>Phone & Email:</strong> Should be same for all languages</li>";
echo "<li>✅ <strong>Hotel Name & Address:</strong> Should change by language</li>";
echo "<li>✅ <strong>Domain:</strong> Should be consistent</li>";
echo "</ul>";
?>