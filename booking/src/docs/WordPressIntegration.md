# WordPress Integration - Simplified Domain + Auto Token

## 🎯 Concept mới: Đơn giản hóa quy trình

### **Before (Phức tạp):**
```
1. Admin WP tạo hotel → tạo token manually
2. Dev phải config token trong frontend
3. Mỗi hotel mới = phải update frontend config
```

### **After (Đơn giản):**
```
1. Admin WP setup subdomain cho hotel
2. Frontend tự động call API get token theo domain
3. Không cần config frontend manual nữa!
```

## 🏗️ WordPress Admin Setup

### **1. Hotel Management Interface trong WP Admin:**

```php
// wp-admin/admin.php?page=hotel-management

Hotel List:
┌─────────────────────────────────────────────────────────────────┐
│ Hotel Name         │ Domain                │ Status    │ Actions │
├─────────────────────────────────────────────────────────────────┤
│ Paradise Hotel     │ paradise.hotel.com    │ Active    │ Edit    │
│ Ocean View Resort  │ oceanview.hotel.com   │ Active    │ Edit    │
│ Mountain Lodge     │ mountain.hotel.com    │ Inactive  │ Edit    │
└─────────────────────────────────────────────────────────────────┘

[+ Add New Hotel]
```

### **2. Add/Edit Hotel Form:**

```php
Hotel Configuration:
┌─────────────────────────────────────────────────────────────────┐
│ Hotel Name: [Paradise Hotel                            ]        │
│ Domain:     [paradise.hotel.com                        ]        │
│ ✓ Auto-generate API token                                       │
│                                                                 │
│ Settings:                                                       │
│ Currency:   [VND ▼]                                            │
│ Timezone:   [Asia/Ho_Chi_Minh ▼]                               │
│ Language:   [Vietnamese ▼]                                     │
│                                                                 │
│ Theme:                                                          │
│ Primary Color: [#e11d48] [🎨]                                  │
│ Logo URL:      [https://hotel.com/logo.png           ]         │
│                                                                 │
│ Contact:                                                        │
│ Phone:    [+84 28 1234 5678                         ]          │
│ Email:    [info@paradise.hotel.com                  ]          │
│ Address:  [123 Beach Road, Da Nang                  ]          │
│                                                                 │
│ Policies:                                                       │
│ Check-in:     [14:00]  Check-out: [12:00]                      │
│ Cancellation: [Free cancellation before 24h       ]           │
│                                                                 │
│ API Configuration:                                              │
│ Backend API URL: [https://api.hotel.com/api        ]          │
│ Auto Token: hotel_001_abc123xyz (🔄 Regenerate)                │
│                                                                 │
│                                    [Save Hotel]                │
└─────────────────────────────────────────────────────────────────┘
```

## 📡 WordPress REST API Endpoints

### **1. Domain Config API với Đa Ngôn Ngữ:**

```php
GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=vi

Response (Vietnamese):
{
  "success": true,
  "wp_id": "hotel_001",
  "api_token": "hotel_001_abc123xyz",
  "config": {
    // THEO ngôn ngữ
    "hotel_name": "Khách Sạn Paradise",
    "address": "123 Đường Biển, Đà Nẵng, Việt Nam",
    "description": "Khách sạn sang trọng bên bờ biển với view tuyệt đẹp",
    "cancellation_policy": "Hủy miễn phí trước 24 giờ",

    // KHÔNG theo ngôn ngữ
    "domain": "paradise.hotel.com",
    "currency": "VND",
    "timezone": "Asia/Ho_Chi_Minh",
    "language": "vi",
    "theme": {
      "primary_color": "#e11d48",
      "logo": "https://paradise.hotel.com/logo.png"
    },
    "contact": {
      "phone": "+84 28 1234 5678",        // KHÔNG đổi
      "email": "info@paradise.hotel.com", // KHÔNG đổi
      "address": "123 Đường Biển, Đà Nẵng, Việt Nam" // THEO ngôn ngữ
    },
    "policies": {
      "check_in": "14:00",
      "check_out": "12:00",
      "cancellation": "Hủy miễn phí trước 24 giờ"
    },
    "api_base_url": "https://api.hotel.com/api"
  }
}

GET /wp-json/hotel-booking/v1/domain-config?domain=paradise.hotel.com&language=en

Response (English):
{
  "success": true,
  "wp_id": "hotel_001",
  "api_token": "hotel_001_abc123xyz",
  "config": {
    // THEO ngôn ngữ
    "hotel_name": "Paradise Hotel",
    "address": "123 Beach Road, Da Nang, Vietnam",
    "description": "Luxury beachfront hotel with stunning ocean views",
    "cancellation_policy": "Free cancellation before 24 hours",

    // KHÔNG theo ngôn ngữ
    "domain": "paradise.hotel.com",
    "currency": "VND",
    "timezone": "Asia/Ho_Chi_Minh",
    "language": "en",
    "theme": {
      "primary_color": "#e11d48",
      "logo": "https://paradise.hotel.com/logo.png"
    },
    "contact": {
      "phone": "+84 28 1234 5678",        // KHÔNG đổi
      "email": "info@paradise.hotel.com", // KHÔNG đổi
      "address": "123 Beach Road, Da Nang, Vietnam" // THEO ngôn ngữ
    },
    "policies": {
      "check_in": "14:00",
      "check_out": "12:00",
      "cancellation": "Free cancellation before 24 hours"
    },
    "api_base_url": "https://api.hotel.com/api"
  }
}
```

### **2. Hotel Config API (với token):**

```php
GET /wp-json/hotel-booking/v1/config?hotel_id=hotel_001
Authorization: Bearer hotel_001_abc123xyz

Response: (same format as above)
```

### **3. Alternative endpoints (fallback):**

```php
GET /wp-json/wp/v2/hotel-config?domain=paradise.hotel.com
GET /wp-json/hotel/v1/config?domain=paradise.hotel.com
GET /wp-admin/admin-ajax.php?action=get_hotel_config&domain=paradise.hotel.com
```

## 💾 WordPress Database Schema với Đa Ngôn Ngữ

### **Table: wp_hotels** (Chính)

```sql
CREATE TABLE wp_hotels (
  id INT PRIMARY KEY AUTO_INCREMENT,
  hotel_id VARCHAR(50) UNIQUE NOT NULL,      -- hotel_001
  domain VARCHAR(255) UNIQUE NOT NULL,       -- paradise.hotel.com (KHÔNG theo ngôn ngữ)
  api_token VARCHAR(255) UNIQUE NOT NULL,    -- hotel_001_abc123xyz
  api_base_url VARCHAR(255) NOT NULL,        -- https://api.hotel.com/api

  -- Settings không đổi theo ngôn ngữ
  currency VARCHAR(10) DEFAULT 'VND',
  timezone VARCHAR(50) DEFAULT 'Asia/Ho_Chi_Minh',
  primary_color VARCHAR(20) DEFAULT '#3b82f6',
  logo_url TEXT,

  -- Contact KHÔNG theo ngôn ngữ
  phone VARCHAR(50),                         -- +84 28 1234 5678 (không đổi)
  email VARCHAR(255),                        -- info@paradise.hotel.com (không đổi)

  -- Policies không đổi
  check_in_time VARCHAR(10) DEFAULT '14:00',
  check_out_time VARCHAR(10) DEFAULT '12:00',

  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

### **Table: wp_hotels_i18n** (Đa ngôn ngữ)

```sql
CREATE TABLE wp_hotels_i18n (
  id INT PRIMARY KEY AUTO_INCREMENT,
  hotel_id VARCHAR(50) NOT NULL,             -- hotel_001
  language VARCHAR(10) NOT NULL,             -- 'vi', 'en', 'ko', 'ja'

  -- Các field THEO ngôn ngữ
  hotel_name VARCHAR(255) NOT NULL,          -- "Paradise Hotel" (EN), "Khách Sạn Paradise" (VI)
  address TEXT,                              -- Địa chỉ theo ngôn ngữ
  description TEXT,                          -- Mô tả theo ngôn ngữ
  cancellation_policy TEXT,                  -- Chính sách hủy theo ngôn ngữ

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (hotel_id) REFERENCES wp_hotels(hotel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_hotel_lang (hotel_id, language)
);
```

### **Sample Data:**

```sql
-- Main hotel data (không theo ngôn ngữ)
INSERT INTO wp_hotels VALUES
(1, 'hotel_001', 'paradise.hotel.com', 'hotel_001_abc123xyz',
 'https://api.hotel.com/api', 'VND', 'Asia/Ho_Chi_Minh', '#e11d48',
 'https://paradise.hotel.com/logo.png', '+84 28 1234 5678', 'info@paradise.hotel.com',
 '14:00', '12:00', 'active'),

(2, 'hotel_002', 'oceanview.hotel.com', 'hotel_002_def456abc',
 'https://api.hotel.com/api', 'VND', 'Asia/Ho_Chi_Minh', '#0ea5e9',
 'https://oceanview.hotel.com/logo.png', '+84 236 3123 456', 'booking@oceanview.hotel.com',
 '15:00', '11:00', 'active');

-- Multilingual data (theo ngôn ngữ)
INSERT INTO wp_hotels_i18n VALUES
-- Paradise Hotel - Vietnamese
(1, 'hotel_001', 'vi', 'Khách Sạn Paradise',
 '123 Đường Biển, Đà Nẵng, Việt Nam',
 'Khách sạn sang trọng bên bờ biển với view tuyệt đẹp',
 'Hủy miễn phí trước 24 giờ'),

-- Paradise Hotel - English
(2, 'hotel_001', 'en', 'Paradise Hotel',
 '123 Beach Road, Da Nang, Vietnam',
 'Luxury beachfront hotel with stunning ocean views',
 'Free cancellation before 24 hours'),

-- Paradise Hotel - Korean
(3, 'hotel_001', 'ko', '파라다이스 호텔',
 '123 비치 로드, 다낭, 베트남',
 '멋진 바다 전망을 갖춘 럭셔리 해변 호텔',
 '24시간 전 무료 취소'),

-- Ocean View Resort - Vietnamese
(4, 'hotel_002', 'vi', 'Resort Tầm Nhìn Đại Dương',
 '456 Đại Lộ Đại Dương, Nha Trang, Việt Nam',
 'Resort cao cấp với tầm nhìn toàn cảnh biển',
 'Hủy miễn phí trước 48 giờ'),

-- Ocean View Resort - English
(5, 'hotel_002', 'en', 'Ocean View Resort',
 '456 Ocean Drive, Nha Trang, Vietnam',
 'Premium resort with panoramic ocean views',
 'Free cancellation before 48 hours');
```

## 🔧 WordPress Plugin Code

### **Main Plugin File: hotel-booking-manager.php**

```php
<?php
/*
Plugin Name: Hotel Booking Manager
Description: Multi-tenant hotel booking system for WordPress Multisite
Version: 1.0.0
*/

// Register REST API endpoints
add_action('rest_api_init', 'register_hotel_booking_endpoints');

function register_hotel_booking_endpoints() {
    // Domain config endpoint
    register_rest_route('hotel-booking/v1', '/domain-config', array(
        'methods' => 'GET',
        'callback' => 'get_hotel_config_by_domain',
        'permission_callback' => '__return_true'
    ));

    // Hotel config endpoint (with token)
    register_rest_route('hotel-booking/v1', '/config', array(
        'methods' => 'GET',
        'callback' => 'get_hotel_config_by_id',
        'permission_callback' => 'validate_hotel_token'
    ));
}

function get_hotel_config_by_domain($request) {
    global $wpdb;

    $domain = $request->get_param('domain');
    $language = $request->get_param('language') ?: 'vi'; // Default Vietnamese

    if (!$domain) {
        return new WP_Error('missing_domain', 'Domain parameter is required', array('status' => 400));
    }

    // Get main hotel data (không theo ngôn ngữ)
    $hotel = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hotels WHERE domain = %s AND status = 'active'",
        $domain
    ));

    if (!$hotel) {
        return new WP_Error('hotel_not_found', 'Hotel not found for domain', array('status' => 404));
    }

    // Get multilingual data (theo ngôn ngữ)
    $hotel_i18n = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hotels_i18n WHERE hotel_id = %s AND language = %s",
        $hotel->hotel_id,
        $language
    ));

    // Fallback to default language if not found
    if (!$hotel_i18n) {
        $hotel_i18n = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hotels_i18n WHERE hotel_id = %s AND language = 'vi'",
            $hotel->hotel_id
        ));
    }

    return array(
        'success' => true,
        'wp_id' => $hotel->hotel_id,
        'api_token' => $hotel->api_token,
        'config' => format_hotel_config_multilingual($hotel, $hotel_i18n, $language)
    );
}

function format_hotel_config_multilingual($hotel, $hotel_i18n, $language) {
    return array(
        // Thông tin theo ngôn ngữ
        'hotel_name' => $hotel_i18n ? $hotel_i18n->hotel_name : 'Hotel',
        'address' => $hotel_i18n ? $hotel_i18n->address : '',
        'description' => $hotel_i18n ? $hotel_i18n->description : '',
        'cancellation_policy' => $hotel_i18n ? $hotel_i18n->cancellation_policy : '',

        // Thông tin KHÔNG theo ngôn ngữ
        'domain' => $hotel->domain,
        'currency' => $hotel->currency,
        'timezone' => $hotel->timezone,
        'language' => $language,
        'theme' => array(
            'primary_color' => $hotel->primary_color,
            'logo' => $hotel->logo_url
        ),
        'contact' => array(
            'phone' => $hotel->phone,          // KHÔNG đổi theo ngôn ngữ
            'email' => $hotel->email,          // KHÔNG đổi theo ngôn ngữ
            'address' => $hotel_i18n ? $hotel_i18n->address : '' // THEO ngôn ngữ
        ),
        'policies' => array(
            'check_in' => $hotel->check_in_time,
            'check_out' => $hotel->check_out_time,
            'cancellation' => $hotel_i18n ? $hotel_i18n->cancellation_policy : ''
        ),
        'api_base_url' => $hotel->api_base_url
    );
}

// Auto-generate token when creating hotel
function generate_hotel_token($hotel_id) {
    return $hotel_id . '_' . wp_generate_password(12, false);
}
```

## 🚀 Frontend Integration Flow

### **1. User visits domain:**
```
paradise.hotel.com/booking → Frontend loads
```

### **2. Auto-detection:**
```javascript
// tenantDetection.js automatically:
1. getCurrentDomain() → 'paradise.hotel.com'
2. getHotelConfigFromDomain() → Call WP API
3. Cache token → localStorage.setItem('hotel_api_token', 'hotel_001_abc123xyz')
4. Return hotel_001
```

### **3. API calls with auto token:**
```javascript
// apiService.js automatically uses cached token:
headers: {
  'Authorization': 'Bearer hotel_001_abc123xyz',
  'X-Hotel-ID': 'hotel_001'
}
```

## 🎯 Benefits

### **For Developers:**
- ✅ No frontend config needed
- ✅ Auto-scaling for new hotels
- ✅ Single deployment for all hotels

### **For Hotel Admins:**
- ✅ Easy setup trong WP admin
- ✅ No technical knowledge required
- ✅ Real-time config changes

### **For End Users:**
- ✅ Seamless booking experience
- ✅ Hotel-specific branding
- ✅ Fast loading (cached tokens)

---

**This approach eliminates manual token management and makes the system truly scalable!** 🎉